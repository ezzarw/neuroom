<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\UserService;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateMe(Request $request, UserService $userService)
    {
        $request->validate([
            'display_name' => 'string|max:100|nullable',
            'email' => 'email|string|max:100|nullable',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:10000|nullable',
        ]);

        $user = $request->user();

        if ($user === null) {
            return $this->apiError('User tidak ditemukan.', 404);
        }

        if ($request->has('email') && $user->email != $request->email) {
            if (User::where('email', $request->email)->exists()) {
                return $this->apiError(
                    'Email sudah digunakan.',
                    422,
                    [
                        'email' => ['Email sudah digunakan.'],
                    ]
                );
            }
        }

        try {
            $user = $userService->updateProfile(
                $user,
                $request->only(['display_name', 'email']),
                $request->file('profile_picture')
            );
        } catch (\Throwable $e) {
            return $this->apiError('Gagal edit profil.', 500);
        }

        return $this->apiSuccess(
            'Profil berhasil diupdate.',
            [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'is_admin' => (int) $user->is_admin,
                'display_name' => $user->display_name,
                'profile_picture' => $user->profile_picture,
                'profile_picture_url' => $user->profile_picture ? asset('storage/profile_picture/'.$user->profile_picture) : null,
                'created_at' => $this->formatDateTime($user->created_at),
                'updated_at' => $this->formatDateTime($user->updated_at),
            ]
        );
    }
}
