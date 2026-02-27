<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Users - Admin</title>

    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

<div class="layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <h2 class="logo">Neuroom Admin</h2>

        <nav class="nav">
            <a href="{{ route('admin.dashboard') }}">Dashboard</a>

            <a href="{{ route('admin.users') }}"
               class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
                Users
            </a>

            <a href="{{ route('admin.pomodoro') }}">Pomodoro</a>
        </nav>
    </aside>

    <!-- MAIN -->
    <main class="main">

        <div class="header">
            <h1>Users Management</h1>
            <button class="btn-cta">+ Tambah User</button>
        </div>

        <section class="table-card">
            <h3>Daftar User</h3>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width:25%">Name</th>
                            <th style="width:30%">Email</th>
                            <th style="width:20%">Password</th>
                            <th style="width:25%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                        {{-- Dummy sementara ya zar (nanti backend ganti) --}}
                        <tr>
                            <td>Anisa</td>
                            <td>anisa@email.com</td>
                            <td>********</td>
                            <td class="actions">
                                <button class="btn-edit">Edit</button>
                                <button class="btn-delete">Hapus</button>
                            </td>
                        </tr>

                        <tr>
                            <td>Rahmat</td>
                            <td>rahmat@email.com</td>
                            <td>********</td>
                            <td class="actions">
                                <button class="btn-edit">Edit</button>
                                <button class="btn-delete">Hapus</button>
                            </td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>