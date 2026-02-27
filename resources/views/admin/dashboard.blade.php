<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

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