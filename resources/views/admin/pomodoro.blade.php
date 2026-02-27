<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pomodoro - Admin</title>

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
            <h1>Pomodoro Sessions</h1>
            <button class="btn-cta">Export</button>
        </div>

        <!-- TABLE -->
        <section class="table-card">
            <h3>Data Timer Pomodoro</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Mode</th>
                        <th>Duration</th>
                        <th>Started At</th>
                        <th>Ended At</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    {{-- NANTI BACKEND NGISI: $sessions --}}
                    @forelse($sessions ?? [] as $s)
                        <tr>
                            <td>#{{ $s->id }}</td>
                            <td>{{ $s->user->name ?? '-' }}</td>
                            <td>{{ $s->mode ?? '-' }}</td>
                            <td>{{ $s->duration ?? '-' }}</td>
                            <td>{{ $s->started_at ?? '-' }}</td>
                            <td>{{ $s->ended_at ?? '-' }}</td>
                            <td>{{ $s->status ?? '-' }}</td>
                        </tr>
                    @empty
                        {{-- Dummy rows biar UI kebaca walau backend belum siap --}}
                        <tr>
                            <td>#102</td>
                            <td>Anisa</td>
                            <td>pomodoro</td>
                            <td>00:25:00</td>
                            <td>2026-02-27 09:10</td>
                            <td>2026-02-27 09:35</td>
                            <td>done</td>
                        </tr>
                        <tr>
                            <td>#103</td>
                            <td>Rahmat</td>
                            <td>stopwatch</td>
                            <td>01:12:34</td>
                            <td>2026-02-27 10:02</td>
                            <td>-</td>
                            <td>running</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

    </main>
</div>

</body>
</html>