<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
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
        $base = Str::slug($natural_username, '_'); // optional: rapihin (spasi jadi _ dll)
        $unique_username = $base;

        $exists = User::where('username', $unique_username)->exists();

        if ($exists) {
            // ambil semua username yang diawali base (ejar, ejar2, ejar3, ...)
            $taken = User::where('username', 'like', $base.'%')
                ->pluck('username')
                ->all();

            // cari suffix angka terbesar
            $max = 1;
            foreach ($taken as $u) {
                if ($u === $base) {
                    $max = max($max, 1);
                    continue;
                }
                if (preg_match('/^'.preg_quote($base, '/').'(\d+)$/', $u, $m)) {
                    $max = max($max, (int)$m[1]);
                }
            }

            $unique_username = $base . ($max + 1);
        }












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
                'is_admin' => 0,
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
            'data' => ['id' => $auth->id, 'email' => $auth->email, 'username' => $auth->username, 'is_admin' => (int) $auth->is_admin],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
        ]);

        $email = $request->email;
        $password = $request->password;

        $row = Authentication::query()->where('email', $email)->first();
        if ($row == null) {
            abort(401, 'Invalid credentials');
        }

        $password_from_db = $row->password;
        if ($password_from_db == null) {
            abort(401, 'Invalid credentials');
        }

        // Verifikasi utama pakai password_verify agar tidak tergantung binary eksternal.
        $isPasswordValid = password_verify($password, $password_from_db);

        // Fallback ke binary lama jika hash tidak terbaca oleh password_verify.
        if (! $isPasswordValid) {
            $bin = base_path('go\bin\win\hashingbcry.exe');
            $process = $this->goProcess([$bin, '-v', $password, $password_from_db]);
            $process->setTimeout(4);
            $process->run();

            if ($process->isSuccessful()) {
                $validated = trim($process->getOutput());
                $isPasswordValid = $validated === 'Bcrypt matched';
            } else {
                logger()->warning('fallback hashingbcry gagal saat login', ['err' => $process->getErrorOutput()]);
            }
        }

        if (! $isPasswordValid) {
            abort(401, 'Invalid credentials');
        }

        $token = $row->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'data' => ['id' => $row->id, 'email' => $row->email, 'username' => $row->username, 'is_admin' => (int) $row->is_admin],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
