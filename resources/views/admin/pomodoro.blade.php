<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <button class="btn-cta" type="button" id="refresh-pomodoro-btn">Refresh</button>
        </div>

        <!-- TABLE -->
        <section class="table-card">
            <h3>Data Timer Pomodoro</h3>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Sesi</th>
                        <th>Durasi</th>
                        <th>Tanggal</th>
                        <th>Dibuat</th>
                    </tr>
                </thead>

                <tbody id="admin-pomodoro-body">
                    <tr>
                        <td colspan="6">Memuat data...</td>
                    </tr>
                </tbody>
            </table>
        </section>

    </main>
</div>

<!-- SCRIPT -->
<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/admin-pomodoro.js') }}" defer></script>

</body>
</html>
