<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
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

    public function user_add(Request $request)
    {
        $request->validate([
            'username' => ['string', 'required', 'max:100'],
            'password' => ['string', 'required', 'min:8'],
            'email' => ['string', 'required', 'email', 'max:100', 'unique:authentications,email'],
        ]);

        $display_name = $request->username;
        $natural_username = $request->username;
        $natural_password = $request->password;
        $email = $request->email;

        // making username unique
        $bin = base_path('go\bin\win\suffix_username.exe');
        $process = $this->goProcess([$bin, $natural_username]); // arg dipisah agar aman
        $process->setTimeout(3);
        $process->run();
        // rajin rajin taruh log, biar gampang debugging
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada binary suffix_username', ['err' => $process->getErrorOutput()]);
            abort(500, 'Internal error');
        }
        $unique_username = trim($process->getOutput());

        // password hashing
        $bin = base_path('go\bin\win\hashingbcry.exe');
        $process = $this->goProcess([$bin, '-e', $natural_password]);
        $process->setTimeout(4);
        $process->run();
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
            abort(500, 'Internal Error');
        }
        $hashed_password = trim($process->getOutput());

        // Simpan auth + profile dalam satu transaksi agar tidak setengah jadi.
        $auth = DB::transaction(function () use ($unique_username, $email, $hashed_password, $display_name) {
            $auth = Authentication::create([
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

            return $auth;
        });

        $token = $auth->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'data' => ['email' => $auth->email, 'username' => $auth->username],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function user_view(Request $request)
    {
        // select('id', 'username','display_name', "email")->get()
        $users_table = User::leftJoin('authentications', 'users.id', '=', 'authentications.id')->select('users.id', 'authentications.username', 'users.display_name', 'authentications.email', 'authentications.is_admin', 'authentications.created_at as users_created_at', 'authentications.updated_at as auth_updated_at', 'users.updated_at as users_updated_at')->get();

        // a.is_admin a.username a.id
        return response()->json([
            'status' => true,
            'data' => $users_table,
            // 'i' => $i
        ]);
    }

    public function user_edit(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:authentications,id'],
            'displayName' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'], // nullable kalau edit tanpa ganti password
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('authentications', 'email')->ignore($request->id),
            ],
            'isAdmin' => ['required', 'integer', 'in:0,1'],
        ]);
        // email, password, is_admin, display_name, profile_picture, email

        // profile_picture kapan hari aja lah anjir males

        $id = $validated['id'];
        $display_name = $validated['displayName'];
        $natural_password = $validated['password'];
        $email = $validated['email'];
        $is_admin = $validated['isAdmin'];

        // password hashing
        if (! is_null($natural_password)) {
            $bin = base_path('go\bin\win\hashingbcry.exe');
            $process = $this->goProcess([$bin, '-e', $natural_password]);
            $process->setTimeout(4);
            $process->run();
            if ($process->isSuccessful() == false) {
                logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
                abort(500, 'Internal Error');
            }
            $password = trim($process->getOutput());
        } else {
            $password = null;
        }

        // masukin ke database
        $auth = DB::transaction(function () use ($id, $display_name, $email, $password, $is_admin) {
            $auth = Authentication::query()->findOrFail($id);
            if (is_null($password)) {
                $auth->update([
                    'email' => $email,
                    'is_admin' => $is_admin,
                ]);
            } else {
                $auth->update([
                    'email' => $email,
                    'is_admin' => $is_admin,
                    'password' => $password,
                ]);
            }

            User::where('username', $auth->username)->update([
                'display_name' => $display_name,
            ]);

            if (! is_null($password)) {
                $auth->tokens()->delete();
            }

            return $auth->fresh();
        });

        return response()->json([
            'status' => true,
            'data' => $auth,
        ], 200);
    }

    public function user_delete(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'integer', 'exists:authentications,id'],
        ]);
        $id = $validated['id'];

        $auth = Authentication::query()->findOrFail($id);
        $auth->delete();

        return response()->json([
            'status' => true,
            'message' => 'User berhasil dihapus.',
        ], 200);
    }
}
