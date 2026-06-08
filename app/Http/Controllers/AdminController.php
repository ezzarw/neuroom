<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserFromAdminRequest;
use App\Models\PomodoroHistory;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    protected function basePomodoroSessionsQuery(): Builder
    {
        return PomodoroHistory::query()
            ->select([
                'id',
                'user_id',
                'pomodoro_uid',
                'status',
                'duration_seconds',
                'actual_seconds',
                'started_at',
                'finished_at',
                'stopped_at',
                'created_at',
                'updated_at',
            ])
            ->with([
                'user:id,username',
            ]);
    }

    protected function baseUsersQuery(): Builder
    {
        return User::query()
            ->select([
                'id',
                'username',
                'display_name',
                'email',
                'is_admin',
                'created_at',
                'updated_at',
            ]);
    }

    protected function transformPomodoroSession(PomodoroHistory $session): array
    {
        return [
            'id' => $session->id,
            'username' => $session->user?->username,
            'pomodoro_uid' => $session->pomodoro_uid,
            'status' => $session->status,
            'duration_seconds' => (int) ($session->duration_seconds ?? 0),
            'actual_seconds' => (int) ($session->actual_seconds ?? 0),
            'started_at' => $this->formatDateTime($session->started_at),
            'finished_at' => $this->formatDateTime($session->finished_at),
            'stopped_at' => $this->formatDateTime($session->stopped_at),
            'created_at' => $this->formatDateTime($session->created_at),
            'updated_at' => $this->formatDateTime($session->updated_at),
        ];
    }

    protected function transformUserRow(User $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'display_name' => $user->display_name,
            'email' => $user->email,
            'is_admin' => (int) $user->is_admin,
            'created_at' => $this->formatDateTime($user->created_at),
            'updated_at' => $this->formatDateTime($user->updated_at),
        ];
    }

    public function usersPage()
    {
        return $this->apiSuccess('Halaman admin users tersedia.', [
            'page' => 'admin.users',
        ]);
    }

    public function dashboard()
    {
        $latestSessions = $this->basePomodoroSessionsQuery()
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn (PomodoroHistory $session) => $this->transformPomodoroSession($session))
            ->values();

        return $this->apiSuccess('Dashboard admin berhasil diambil.', [
            'stats' => [
                'total_users' => User::count(),
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
        $sessions = $this->basePomodoroSessionsQuery()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn (PomodoroHistory $session) => $this->transformPomodoroSession($session))
            ->values();

        return $this->apiSuccess(
            'Data pomodoro admin berhasil diambil.',
            $sessions->all()
        );
    }

    public function monitoringLogs()
    {
        $logs = ActivityLog::with('user:id,username,display_name')
            ->latest()
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user_id' => $log->user_id,
                    'username' => $log->user ? $log->user->username : 'System/Guest',
                    'action' => $log->action,
                    'description' => $log->description,
                    'created_at' => $this->formatDateTime($log->created_at),
                ];
            });

        return $this->apiSuccess('Log monitoring berhasil diambil.', $logs->all());
    }

    public function index()
    {
        $users = $this->baseUsersQuery()
            ->orderBy('id')
            ->get()
            ->map(fn (User $user) => $this->transformUserRow($user))
            ->values();

        return $this->apiSuccess(
            'Daftar user berhasil diambil.',
            $users->all()
        );
    }

    public function store(StoreUserFromAdminRequest $request)
    {
        $validated = $request->validated();

        $displayName = trim($validated['username']);
        $uniqueUsername = $this->generateUniqueUsername($displayName);

        $created = DB::transaction(function () use ($uniqueUsername, $validated, $displayName) {
            return User::create([
                'username' => $uniqueUsername,
                'display_name' => $displayName,
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'is_admin' => 0,
                'profile_picture' => null,
            ]);
        });

        ActivityLogger::log(auth()->id(), 'admin_create_user', "Admin membuat user baru: {$uniqueUsername}");

        return $this->apiSuccess(
            'User berhasil ditambahkan.',
            $this->transformUserRow($created),
            201
        );
    }

    public function update(Request $request, User $user)
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
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'is_admin' => ['required', 'integer', 'in:0,1'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            $payload = [
                'email' => $validated['email'],
                'display_name' => $validated['display_name'],
                'is_admin' => $validated['is_admin'],
            ];

            if (! empty($validated['password'])) {
                $payload['password'] = Hash::make($validated['password']);
            }

            $user->update($payload);
        });

        $updated = $user->fresh();

        ActivityLogger::log(auth()->id(), 'admin_update_user', "Admin mengubah data user: {$updated->username}");

        return $this->apiSuccess(
            'User berhasil diupdate.',
            $this->transformUserRow($updated)
        );
    }

    public function destroy(User $user)
    {
        $username = $user->username;
        $user->delete();

        ActivityLogger::log(auth()->id(), 'admin_delete_user', "Admin menghapus user: {$username}");

        return $this->apiSuccess('User berhasil dihapus.');
    }
}
