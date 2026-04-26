<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\Auth as Authentication;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    protected function redirectPathForAuthenticatedUser(Authentication $auth): string
    {
        return (int) $auth->is_admin === 1
            ? route('admin.dashboard')
            : route('utama');
    }

    protected function transformAuthenticatedUser(Authentication $auth): array
    {
        $profile = User::query()
            ->where('auth_id', $auth->id)
            ->first();

        $profilePicture = $profile?->profile_picture;

        return [
            'id' => $auth->id,
            'username' => $auth->username,
            'email' => $auth->email,
            'is_admin' => (int) $auth->is_admin,
            'display_name' => $profile?->display_name,
            'profile_picture' => $profilePicture,
            'profile_picture_url' => $profilePicture ? asset('storage/profile_picture/'.$profilePicture) : null,
        ];
    }

    public function register(RegisterUserRequest $request)
    {
        if ($request->user() !== null) {
            return $this->apiError('Session aktif sudah ada.', 409, [], [
                'redirect_to' => $this->redirectPathForAuthenticatedUser($request->user()),
            ]);
        }

        $display_name = $request->username;
        $email = $request->email;
        $hashed_password = Hash::make($request->password);
        $unique_username = $this->generateUniqueUsername($request->username);

        $user_data = DB::transaction(function () use ($unique_username, $email, $hashed_password, $display_name) {
            $auth = Authentication::create([
                'username' => $unique_username,
                'email' => $email,
                'is_admin' => 0,
                'password' => $hashed_password,
            ]);

            $users = User::create([
                'auth_id' => $auth->id,
                'display_name' => $display_name,
                'profile_picture' => null,
            ]);

            $result = (object) ['auth' => $auth, 'user' => $users];
            return $result;
        });

        Auth::guard('web')->login($user_data->auth);
        $request->session()->regenerate();

        return $this->apiSuccess(
            'Register berhasil. Selamat datang.',
            [
                'user' => $this->transformAuthenticatedUser($user_data->auth),
            ],
            201,
            [
                'redirect_to' => route('utama'),
            ]
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
        ]);

        if ($request->user() !== null) {
            return $this->apiError('Session aktif sudah ada.', 409, [], [
                'redirect_to' => $this->redirectPathForAuthenticatedUser($request->user()),
            ]);
        }

        $auth = Authentication::query()->where('email', $request->email)->first();
        $user = $auth ? User::query()->where('auth_id', $auth->id)->first() : null;

        if ($auth === null || $user === null || ! Hash::check($request->password, $auth->password)) {
            return $this->apiError(
                'Email atau password salah.',
                422,
                [
                    'email' => ['Email atau password salah.'],
                ]
            );
        }

        Auth::guard('web')->login($auth);
        $request->session()->regenerate();

        $redirectTo = $this->redirectPathForAuthenticatedUser($auth);

        return $this->apiSuccess(
            'Login berhasil.',
            [
                'user' => $this->transformAuthenticatedUser($auth),
            ],
            200,
            [
                'redirect_to' => $redirectTo,
            ]
        );
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();  
        $request->session()->regenerateToken();

        return $this->apiSuccess(
            'Logout berhasil.',
            [],
            200,
            [
                'redirect_to' => route('landing'),
            ]
        );
    }

    public function me(Request $request)
    {
        return $this->apiSuccess('Profil berhasil diambil.', [
            'user' => $this->transformAuthenticatedUser($request->user()),
        ]);
    }
}
