<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
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

    public function register(RegisterUserRequest $request)
    {
        if ($request->user() !== null) {
            return $this->apiError('Session aktif sudah ada.', 409);
        }

        $display_name = $request->username;
        $email = $request->email;
        $hashed_password = Hash::make($request->password);
        $unique_username = $this->generateUniqueUsername($request->username);

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

        return $this->apiSuccess(
            'Login berhasil.',
            $this->transformAuthenticatedUser($auth)
        );
    }

    public function logout(Request $request)
    {
        $user_id = Auth::id();
        $redis_keys = Redis::keys("user:$user_id:*");
        
        
        
        if (!empty($redis_keys)) {
            $redis_keys_parsed = [];
            foreach ($redis_keys as $perkey) {
                $arr_temporary = explode(':', $perkey); //pokok e koyok ngene ngkok ['laravel-database-user', '1', 'summary']
                $arr_temporary[0] = 'user';
                $arr_to_str = implode(':', $arr_temporary);
                // dd($arr_to_str);
                
                $redis_keys_parsed[] = $arr_to_str;
            }


            Redis::del($redis_keys_parsed);
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
