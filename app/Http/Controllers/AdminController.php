<?php

namespace App\Http\Controllers;

use App\Models\Auth as Authentication;
use App\Models\PomodoroHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected function transformPomodoroSession(PomodoroHistory $session): array
    {
        $session->loadMissing('user.auth');

        return [
            'id' => $session->id,
            'username' => $session->user?->auth?->username,
            'session' => $session->session,
            'date' => $session->created_at?->toDateString(),
            'duration_seconds' => (int) ($session->duration_seconds ?? 0),
            'duration' => $this->formatDuration((int) ($session->duration_seconds ?? 0)),
            'created_at' => $this->formatDateTime($session->created_at),
            'updated_at' => $this->formatDateTime($session->updated_at),
        ];
    }

    protected function transformUserRow(object $row): array
    {
        return [
            'id' => $row->id,
            'username' => $row->username,
            'display_name' => $row->display_name,
            'email' => $row->email,
            'is_admin' => (int) $row->is_admin,
            'created_at' => $row->created_at ? $this->formatDateTime(Carbon::parse($row->created_at)) : null,
            'auth_updated_at' => $row->auth_updated_at ? $this->formatDateTime(Carbon::parse($row->auth_updated_at)) : null,
            'updated_at' => $row->updated_at ? $this->formatDateTime(Carbon::parse($row->updated_at)) : null,
        ];
    }

    protected function baseUsersQuery()
    {
        return Authentication::query()
            ->leftJoin('users', 'auths.id', '=', 'users.auth_id')
            ->select(
                'auths.id',
                'auths.username',
                'users.display_name',
                'auths.email',
                'auths.is_admin',
                'users.created_at',
                'auths.updated_at as auth_updated_at',
                'users.updated_at'
            );
    }

    public function usersPage()
    {
        return view('admin.users');
    }

    public function dashboard()
    {
        $latestSessions = PomodoroHistory::query()
            ->with('user.auth')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (PomodoroHistory $session) => $this->transformPomodoroSession($session))
            ->values();

        return $this->apiSuccess('Dashboard admin berhasil diambil.', [
            'stats' => [
                'total_users' => Authentication::count(),
                'total_sessions' => PomodoroHistory::count(),
                'active_today' => PomodoroHistory::query()
                    ->whereDate('created_at', now()->toDateString())
                    ->distinct()
                    ->count('user_id'),
            ],
            'latest_sessions' => $latestSessions,
        ]);
    }

    public function pomodoroSessions()
    {
        $sessions = PomodoroHistory::query()
            ->with('user.auth')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (PomodoroHistory $session) => $this->transformPomodoroSession($session))
            ->values();

        return $this->apiSuccess('Data pomodoro admin berhasil diambil.', [
            'sessions' => $sessions,
        ]);
    }

    public function index()
    {
        $users = $this->baseUsersQuery()
            ->orderBy('auths.id')
            ->get()
            ->map(fn (object $row) => $this->transformUserRow($row))
            ->values();

        return $this->apiSuccess('Daftar user berhasil diambil.', [
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => ['string', 'required', 'max:100'],
            'password' => ['string', 'required', 'min:8'],
            'email' => ['string', 'required', 'email', 'max:100', 'unique:auths,email'],
        ]);

        $displayName = $validated['username'];
        $uniqueUsername = $this->generateUniqueUsername($validated['username']);
        $hashedPassword = Hash::make($validated['password']);

        DB::transaction(function () use ($uniqueUsername, $validated, $hashedPassword, $displayName) {
            $auth = Authentication::create([
                'username' => $uniqueUsername,
                'email' => $validated['email'],
                'password' => $hashedPassword,
            ]);

            User::create([
                'auth_id' => $auth->id,
                'display_name' => $displayName,
                'profile_picture' => null,
            ]);
        });

        $created = $this->baseUsersQuery()
            ->where('auths.username', $uniqueUsername)
            ->firstOrFail();

        return $this->apiSuccess('User berhasil ditambahkan.', [
            'user' => $this->transformUserRow($created),
        ], 201);
    }

    public function update(Request $request, Authentication $user)
    {
        $request->merge([
            'display_name' => $request->input('display_name', $request->input('displayName')),
            'is_admin' => $request->input('is_admin', $request->input('isAdmin')),
        ]);

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:100'],
            'password' => ['nullable', 'string', 'min:8'],
            'email' => [
                'required', 'string', 'email', 'max:100',
                Rule::unique('auths', 'email')->ignore($user->id),
            ],
            'is_admin' => ['required', 'integer', 'in:0,1'],
        ]);

        $password = null;
        if (array_key_exists('password', $validated) && ! is_null($validated['password'])) {
            $password = Hash::make($validated['password']);
        }

        DB::transaction(function () use ($user, $validated, $password) {
            $payload = [
                'email' => $validated['email'],
                'is_admin' => $validated['is_admin'],
            ];

            if (! is_null($password)) {
                $payload['password'] = $password;
            }

            $user->update($payload);

            User::where('auth_id', $user->id)->update([
                'display_name' => $validated['display_name'],
            ]);
        });

        $updated = $this->baseUsersQuery()
            ->where('auths.id', $user->id)
            ->firstOrFail();

        return $this->apiSuccess('User berhasil diupdate.', [
            'user' => $this->transformUserRow($updated),
        ]);
    }

    public function destroy(Authentication $user)
    {
        $user->delete();

        return $this->apiSuccess('User berhasil dihapus.');
    }
}
