<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redis;

class AuthController extends Controller
{
    protected function statefulSessionGuard(): string
    {
        $guards = config('sanctum.guard', []);

        if (is_array($guards) && isset($guards[0]) && is_string($guards[0])) {
            return $guards[0];
        }

        return (string) config('auth.defaults.guard');
    }

    protected function transformAuthenticatedUser(User $auth): array
    {
        $profilePicture = $auth->profile_picture;

        return [
            'id' => $auth->id,
            'username' => $auth->username,
            'email' => $auth->email,
            'is_admin' => (int) $auth->is_admin,
            'display_name' => $auth->display_name,
            'profile_picture' => $profilePicture,
            'profile_picture_url' => $profilePicture ? asset('storage/profile_picture/'.$profilePicture) : null,
        ];
    }

    public function register(RegisterUserRequest $request, UserService $userService)
    {
        if ($request->user() !== null) {
            return $this->apiError('Session aktif sudah ada.', 409);
        }

        $display_name = $request->username;
        $email = $request->email;
        $hashed_password = Hash::make($request->password);
        $unique_username = $userService->generateUniqueUsername($request->username);

        $auth = User::create([
                'username' => $unique_username,
                'email' => $email,
                'is_admin' => 0,
                'password' => $hashed_password,
                'display_name' => $display_name,
                'profile_picture' => null,
            ]);

        Auth::guard($this->statefulSessionGuard())->login($auth);
        $request->session()->regenerate();

        ActivityLogger::log($auth->id, 'register', "User mendaftar dengan username {$auth->username}");

        return $this->apiSuccess(
            'Register berhasil. Selamat datang.',
            $this->transformAuthenticatedUser($auth),
            201
        );
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email|max:100',
            'password' => 'required|string',
        ]);

        if ($request->user() !== null) {
            return $this->apiError('Session aktif sudah ada.', 409);
        }

        $auth = User::query()
            ->where('email', $request->email)
            ->first();

        if ($auth === null || ! Hash::check($request->password, $auth->password)) {
            return $this->apiError(
                'Email atau password salah.',
                422,
                [
                    'email' => ['Email atau password salah.'],
                ]
            );
        }

        Auth::guard($this->statefulSessionGuard())->login($auth);
        $request->session()->regenerate();

        ActivityLogger::log($auth->id, 'login', "User login");

        return $this->apiSuccess(
            'Login berhasil.',
            $this->transformAuthenticatedUser($auth),
            200,
            ['redirect_to' => $auth->is_admin ? '/admin' : '/utama']
        );
    }

    public function logout(Request $request, AuthService $authService)
    {
        $user_id = Auth::id();
        if ($user_id) {
            $authService->clearUserRedisKeys($user_id);
            ActivityLogger::log($user_id, 'logout', "User logout");
        }
        
        Auth::guard($this->statefulSessionGuard())->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->apiSuccess(
            'Logout berhasil.',
            []
        );
    }

    public function me(Request $request)
    {
        return $this->apiSuccess(
            'Profil berhasil diambil.',
            $this->transformAuthenticatedUser($request->user())
        );
    }
}
