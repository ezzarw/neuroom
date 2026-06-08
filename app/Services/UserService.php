<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use DateTime;

class UserService
{
    public function generateUniqueUsername(string $naturalUsername): string
    {
        $base = Str::slug($naturalUsername, '');
        $base = $base === '' ? Str::lower(Str::random(8)) : $base;
        $uniqueUsername = $base;
 
        if (! User::where('username', $uniqueUsername)->exists()) {
            return $uniqueUsername;
        }

        $taken = User::where('username', 'like', $base.'%')
            ->pluck('username')
            ->all();

        $max = 1;

        foreach ($taken as $username) {
            if ($username === $base) {
                continue;
            }

            if (preg_match('/^'.preg_quote($base, '/').'(\d+)$/', $username, $matches)) {
                $max = max($max, (int) $matches[1]);
            }
        }

        return $base.($max + 1);
    }

    public function updateProfile(User $user, array $data, $profilePictureFile = null)
    {
        $oldProfilePicture = $user->profile_picture;
        $newProfilePictureName = null;

        if ($profilePictureFile) {
            $dt = new DateTime;
            $formattedDate = str_replace([' ', ':', '.', '-'], ['_', '', '', ''], $dt->format('Y-m-d H:i:s.u'));
            $newProfilePictureName = uniqid().'_'.$formattedDate.'.'.$profilePictureFile->getClientOriginalExtension();
        }

        $updateData = [];
        if (isset($data['display_name'])) {
            $updateData['display_name'] = $data['display_name'];
        }
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        if ($newProfilePictureName) {
            $updateData['profile_picture'] = $newProfilePictureName;
        }

        try {
            if ($profilePictureFile) {
                $profilePictureFile->storeAs('profile_picture', $newProfilePictureName, 'public');
            }

            DB::transaction(function () use ($user, $updateData) {
                if (count($updateData) > 0) {
                    $user->update($updateData);
                }
            });
        } catch (\Throwable $e) {
            if ($newProfilePictureName) {
                if (Storage::disk('public')->exists('profile_picture/'.$newProfilePictureName)) {
                    Storage::disk('public')->delete('profile_picture/'.$newProfilePictureName);
                }
            }
            throw $e;
        }

        if ($profilePictureFile && $oldProfilePicture) {
            if (Storage::disk('public')->exists('profile_picture/'.$oldProfilePicture)) {
                Storage::disk('public')->delete('profile_picture/'.$oldProfilePicture);
            }
        }

        return $user->fresh();
    }
}
