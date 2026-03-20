<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected function redirectCreateUserFormError(string $message)
    {
        return redirect()
            ->route('admin.users')
            ->withInput()
            ->with('open_create_modal', true)
            ->with('error', $message);
    }

    protected function redirectUserFormError(string $message)
    {
        return redirect()
            ->route('admin.users')
            ->withInput()
            ->with('open_edit_modal', true)
            ->with('error', $message);
    }

    public function usersPage()
    {
        $users = Authentication::query()
            ->leftJoin('users', 'authentications.username', '=', 'users.username')
            ->select(
                'authentications.id',
                'authentications.username',
                'users.display_name',
                'authentications.email',
                'authentications.is_admin',
                'users.created_at',
                'authentications.updated_at as auth_updated_at',
                'users.updated_at'
            )
            ->orderBy('authentications.id')
            ->get();

        return view('admin.users', ['users' => $users]);
    }

    public function createUserWeb(Request $request)
    {
        $validated = $request->validate([
            'username' => ['string', 'required', 'max:100'],
            'password' => ['string', 'required', 'min:8'],
            'email' => ['string', 'required', 'email', 'max:100', 'unique:authentications,email'],
        ]);

        $display_name = $validated['username'];
        $natural_username = $validated['username'];
        $natural_password = $validated['password'];
        $email = $validated['email'];

        if (PHP_OS_FAMILY == 'Windows') {
            $bin = base_path('go\bin\win\suffix_username.exe');
        } elseif (PHP_OS_FAMILY == 'Linux') {
            $bin = base_path('go/bin/suffix_username');
        }

        $process = $this->goProcess([$bin, $natural_username]);
        $process->setTimeout(3);
        $process->run();

        if (! $process->isSuccessful()) {
            logger()->error('ada kesalahan pada binary suffix_username', ['err' => $process->getErrorOutput()]);
            return $this->redirectCreateUserFormError('Gagal membuat username unik.');
        }

        $unique_username = trim($process->getOutput());

        if (PHP_OS_FAMILY == 'Windows') {
            $bin = base_path('go\bin\win\hashingbcry.exe');
        } elseif (PHP_OS_FAMILY == 'Linux') {
            $bin = base_path('go/bin/hashingbcry');
        }

        $process = $this->goProcess([$bin, '-e', $natural_password]);
        $process->setTimeout(4);
        $process->run();

        if (! $process->isSuccessful()) {
            logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
            return $this->redirectCreateUserFormError('Gagal menyimpan password user.');
        }

        $hashed_password = trim($process->getOutput());

        DB::transaction(function () use ($unique_username, $email, $hashed_password, $display_name) {
            Authentication::create([
                'username' => $unique_username,
                'email' => $email,
                'password' => $hashed_password,
            ]);

            User::create([
                'username' => $unique_username,
                'display_name' => $display_name,
                'email' => $email,
                'profile_picture' => null,
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function updateUserWeb(Request $request, Authentication $user)
    {
        $validated = $request->validate([
            'displayName' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('authentications', 'email')->ignore($user->id),
            ],
            'isAdmin' => ['required', 'integer', 'in:0,1'],
        ]);

        $password = null;
        if (! is_null($validated['password'])) {
            if (PHP_OS_FAMILY == 'Windows') {
                $bin = base_path('go\bin\win\hashingbcry.exe');
            } elseif (PHP_OS_FAMILY == 'Linux') {
                $bin = base_path('go/bin/hashingbcry');
            }

            $process = $this->goProcess([$bin, '-e', $validated['password']]);
            $process->setTimeout(4);
            $process->run();

            if (! $process->isSuccessful()) {
                logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
                return $this->redirectUserFormError('Gagal mengubah password user.');
            }

            $password = trim($process->getOutput());
        }

        DB::transaction(function () use ($user, $validated, $password) {
            $payload = [
                'email' => $validated['email'],
                'is_admin' => $validated['isAdmin'],
            ];

            if (! is_null($password)) {
                $payload['password'] = $password;
            }

            $user->update($payload);

            User::where('username', $user->username)->update([
                'display_name' => $validated['displayName'],
            ]);
        });

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil diupdate.');
    }

    public function deleteUserWeb(Authentication $user)
    {
        $user->delete();

        return redirect()
            ->route('admin.users')
            ->with('success', 'User berhasil dihapus.');
    }
}
