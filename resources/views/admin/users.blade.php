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

            {{-- Route create belum ada di web.php, jadi sementara button biasa --}}
            <button type="button" class="btn-cta">+ Tambah User</button>
        </div>

        <section class="table-card">
            <h3>Daftar User</h3>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th style="width:6%">ID</th>
                            <th style="width:12%">Username</th>
                            <th style="width:14%">Display Name</th>
                            <th style="width:14%">Email</th>
                            <th style="width:10%">Admin</th>
                            <th style="width:12%">Created At</th>
                            <th style="width:12%">Auth Updated</th>
                            <th style="width:12%">Updated At</th>
                            <th style="width:10%">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($users ?? [] as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>{{ $user->uusername }}</td>
                                <td>{{ $user->display_name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->is_admin ? 'Ya' : 'Tidak' }}</td>
                                <td>{{ $user->users_created_at }}</td>
                                <td>{{ $user->auth_update_at }}</td>
                                <td>{{ $user->users_update_at }}</td>

                                <td class="actions">
                                    {{-- Route edit/delete belum ada di web.php, jadi sementara button biasa --}}
                                    <button type="button" class="btn-edit">Edit</button>
                                    <button type="button" class="btn-delete">Hapus</button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="text-align:center;">
                                    Belum ada data user.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

</body>
</html>