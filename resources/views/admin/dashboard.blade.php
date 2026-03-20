<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .admin-session {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .admin-name {
            font-size: 14px;
            color: #334155;
            font-weight: 600;
        }
        .logout-btn {
            border: none;
            background: #ef4444;
            color: #fff;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        .logout-btn:hover {
            background: #dc2626;
        }
    </style>
</head>
<body>
@php
    $admin = auth()->user();
    $adminName = $admin?->username ?? $admin?->name ?? 'Admin';
@endphp

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Neuroom Admin</h2>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}"
               class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.users') }}"
               class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                Users
            </a>

            <a href="{{ route('admin.pomodoro') }}"
               class="{{ request()->routeIs('admin.pomodoro') ? 'active' : '' }}">
                Pomodoro
            </a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <!-- HEADER -->
        <div class="header">
            <h1>Welcome Dashboard!</h1>
            <div class="admin-session">
                <span class="admin-name">
                    Login sebagai: <strong>{{ $adminName }}</strong>
                </span>
                <form action="{{ url('/auth/logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="logout-btn">Logout</button>
                </form>
            </div>
        </div>

        <!-- STAT CARDS -->
        <section class="stats">
            <div class="card">
                <h4>Total Users</h4>
                <h2>{{ $totalUsers ?? 1284 }}</h2>
            </div>

            <div class="card">
                <h4>Total Sessions</h4>
                <h2>{{ $totalSessions ?? 324 }}</h2>
            </div>

            <div class="card">
                <h4>Active Today</h4>
                <h2>{{ $activeToday ?? 98 }}</h2>
            </div>
        </section>

        <!-- TABLE -->
        <section class="table-card">
            <h3>Latest Sessions</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Mode</th>
                        <th>Duration</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>

                    @forelse($sessions ?? [] as $session)
                        <tr>
                            <td>#{{ $session->id }}</td>
                            <td>{{ $session->user->name ?? '-' }}</td>
                            <td>{{ $session->mode }}</td>
                            <td>{{ $session->duration }}</td>
                            <td>{{ $session->status }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Belum ada data.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </section>

    </main>
</div>

</body>
</html>
