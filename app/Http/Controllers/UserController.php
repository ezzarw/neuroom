<?php

namespace App\Http\Controllers;

use App\Models\Auth as Authentication;
use App\Models\User;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function updateMe(Request $request)
    {
        $request->validate([
            'display_name' => 'string|max:100|nullable',
            'email' => 'email|string|max:100|nullable',
            'profile_picture' => 'image|mimes:jpeg,png,jpg|max:10000|nullable',
        ]);

        $username = $request->user()->username;
        $display_name = $request->display_name;
        $email = $request->email;
        $profile_picture = $request->file('profile_picture');

        $row_authentications = Authentication::where('username', $username)->first();
        $row_users = $row_authentications
            ? User::where('auth_id', $row_authentications->id)->first()
            : null;

        if ($row_users == null || $row_authentications == null) {
            return $this->apiError('User tidak ditemukan.', 404);
        }

        $old_profile_picture = $row_users->profile_picture;

        if (is_null($profile_picture) == false) {
            $dt = new DateTime;
            $formatted_date = str_replace([' ', ':', '.', '-'], ['_', '', '', ''], $dt->format('Y-m-d H:i:s.u'));
            $sanitized_name = uniqid().'_'.$formatted_date.'.'.$profile_picture->getClientOriginalExtension();
        } else {
            $sanitized_name = null;
        }

        if (is_null($email) == false) {
            if ($row_authentications->email != $email) {
                if (Authentication::where('email', $email)->exists()) {
                    return $this->apiError(
                        'Email sudah digunakan.',
                        422,
                        [
                            'email' => ['Email sudah digunakan.'],
                        ]
                    );
                }
            }
        }

        $input_not_null = [];
        if (is_null($display_name) == false) {
            $input_not_null['display_name'] = $display_name;
        }
        if (is_null($profile_picture) == false) {
            $input_not_null['profile_picture'] = $sanitized_name;
        }

        try {
            if (is_null($profile_picture) == false) {
                $profile_picture->storeAs('profile_picture', $sanitized_name, 'public');
            }

            DB::transaction(function () use ($row_users, $input_not_null, $row_authentications, $email) {
                if (count($input_not_null) > 0) {
                    $row_users->update($input_not_null);
                }

                if (is_null($email) == false) {
                    if ($row_authentications->email != $email) {
                        $row_authentications->update(['email' => $email]);
                    }
                }
            });
        } catch (\Throwable $e) { //kalo misal gagal tapi filenya masih nyangkkut, otomatis kedelete (jika nyangkut)
            if (is_null($sanitized_name) == false) {
                if (Storage::disk('public')->exists('profile_picture/'.$sanitized_name)) {
                    Storage::disk('public')->delete('profile_picture/'.$sanitized_name);
                }
            }

            return $this->apiError('Gagal edit profil.', 500);
        }

        if (is_null($profile_picture) == false) {
            if (is_null($old_profile_picture) == false) {
                if (Storage::disk('public')->exists('profile_picture/'.$old_profile_picture)) {
                    Storage::disk('public')->delete('profile_picture/'.$old_profile_picture);
                }
            }
        }

        if (is_null($email) == false) {
            if ($row_authentications->email != $email) {
                $input_not_null['email'] = $email;
            }
        }

        $row_users->refresh();
        $row_authentications->refresh();

        return $this->apiSuccess('Profil berhasil diupdate.', [
            'user' => [
                'id' => $row_authentications->id,
                'username' => $row_authentications->username,
                'email' => $row_authentications->email,
                'is_admin' => (int) $row_authentications->is_admin,
                'display_name' => $row_users->display_name,
                'profile_picture' => $row_users->profile_picture,
                'profile_picture_url' => $row_users->profile_picture ? asset('storage/profile_picture/'.$row_users->profile_picture) : null,
                'created_at' => $this->formatDateTime($row_users->created_at),
                'updated_at' => $this->formatDateTime($row_users->updated_at),
                'auth_created_at' => $this->formatDateTime($row_authentications->created_at),
                'auth_updated_at' => $this->formatDateTime($row_authentications->updated_at),
            ],
        ]);
    }
}
