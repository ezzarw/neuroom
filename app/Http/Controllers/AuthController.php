<?php

namespace App\Http\Controllers;

use App\Models\Authentication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

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
        $bin = base_path('go/bin/suffix_username'); // biar absolute anjaymanjay
        $process = new Process([$bin, $natural_username]); // arg dipisah agar aman
        $process->setTimeout(3);
        $process->run();
        // rajin rajin taruh log, biar gampang debugging
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada binary suffix_username', ['err' => $process->getErrorOutput()]);
            abort(500, 'Internal error');
        }
        $unique_username = trim($process->getOutput(), "\n");

        // password hashing
        $bin = base_path('go/bin/hashingbcry');
        $process = new Process([$bin, '-e', str($natural_password)]);
        $process->setTimeout(4);
        $process->run();
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
            abort(500, 'Internal Error');
        }
        $hashed_password = trim($process->getOutput(), "\n");

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

        // password hashing
        $bin = base_path('go/bin/hashingbcry');
        $process = new Process([$bin, '-v', $password, $password_from_db]);
        $process->setTimeout(4);
        $process->run();
        if ($process->isSuccessful() == false) {
            logger()->error('ada kesalahan pada hashingbcry', ['err' => $process->getErrorOutput()]);
            abort(500, 'Internal Error');
        }
        $validated = trim($process->getOutput(), "\n");

        if ($validated != 'Bcrypt matched') {
            abort(401, 'Invalid credentials');
        }

        $token = $row->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => true,
            'data' => ['email' => $row->email, 'username' => $row->username],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }
}
