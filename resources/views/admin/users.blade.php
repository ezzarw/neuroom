<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
            <button type="button" class="btn-cta" id="open-create-modal">+ Tambah User</button>
        </div>

        <div id="action-result" class="result-box"></div>

        <section class="table-card">
            <h3>Daftar User</h3>

            <div class="table-wrap">
                <table>
                    <thead>
                    <tr>
                        <th style="width:6%">ID</th>
                        <th style="width:12%">Username</th>
                        <th style="width:14%">Display Name</th>
                        <th style="width:16%">Email</th>
                        <th style="width:8%">Admin</th>
                        <th style="width:12%">Created At</th>
                        <th style="width:12%">Auth Updated</th>
                        <th style="width:12%">Updated At</th>
                        <th style="width:8%">Aksi</th>
                    </tr>
                    </thead>

                    <tbody id="users-table-body">
                        <tr>
                            <td colspan="9" style="text-align:center;">Memuat data user...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<!-- MODAL CREATE USER -->
<div id="create-user-modal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-user-title">
        <div class="modal-head">
            <h3 class="modal-title" id="create-user-title">Tambah User</h3>
            <button type="button" class="modal-close" id="close-create-modal" aria-label="Tutup">&times;</button>
        </div>
        <form id="create-user-form" class="edit-form">
            <div class="label-name">
                <label for="create-username">Username</label>
                <input type="text" id="create-username" name="username" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="create-email">Email</label>
                <input type="email" id="create-email" name="email" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="create-password">Password</label>
                <input type="password" id="create-password" name="password" minlength="8" required>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancel-create-btn">Batal</button>
                <button type="submit" class="btn-primary" id="save-create-btn">Tambah User</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT USER -->
<div id="edit-user-modal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-user-title">
        <div class="modal-head">
            <h3 class="modal-title" id="edit-user-title">Edit User</h3>
            <button type="button" class="modal-close" id="close-edit-modal" aria-label="Tutup">&times;</button>
        </div>
        <form id="edit-user-form" class="edit-form">
            <input type="hidden" id="edit-user-id" name="user_id">

            <div class="label-name">
                <label for="edit-display-name">Display Name</label>
                <input type="text" id="edit-display-name" name="display_name" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="edit-password">Password Baru (Opsional)</label>
                <input type="password" id="edit-password" name="password" minlength="8" placeholder="Kosongkan jika tidak ganti">
                <p class="modal-note">Kalau dikosongkan, password lama tetap dipakai.</p>
            </div>

            <div class="label-name">
                <label for="edit-is-admin">Role</label>
                <select id="edit-is-admin" name="is_admin" required>
                    <option value="0">User Biasa</option>
                    <option value="1">Admin</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancel-edit-btn">Batal</button>
                <button type="submit" class="btn-primary" id="save-edit-btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- SCRIPT -->
<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/admin-users.js') }}" defer></script>

</body>
</html>
