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

            <button type="button" class="btn-cta" id="open-create-modal">+ Tambah User</button>
        </div>
        <div id="action-result" class="result-box" style="color: {{ session('error') || $errors->any() ? '#b91c1c' : '#166534' }};">
            @if (session('success'))
                {{ session('success') }}
            @elseif (session('error'))
                {{ session('error') }}
            @elseif ($errors->any())
                {{ $errors->first() }}
            @endif
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
                        <th style="width:16%">Email</th>
                        <th style="width:8%">Admin</th>
                        <th style="width:12%">Created At</th>
                        <th style="width:12%">Auth Updated</th>
                        <th style="width:12%">Updated At</th>
                        <th style="width:8%">Aksi</th>
                    </tr>
                    </thead>

                    <tbody id="users-table-body">
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->display_name ?? '-' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                <span class="role-badge {{ (int) $user->is_admin === 1 ? 'role-admin' : 'role-user' }}">
                                    {{ (int) $user->is_admin === 1 ? 'Admin' : 'User' }}
                                </span>
                            </td>
                            <td>
                                {{ $user->created_at ? \Illuminate\Support\Carbon::parse($user->created_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>
                                {{ $user->auth_updated_at ? \Illuminate\Support\Carbon::parse($user->auth_updated_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td>
                                {{ $user->updated_at ? \Illuminate\Support\Carbon::parse($user->updated_at)->format('d/m/Y H:i') : '-' }}
                            </td>
                            <td class="actions">
                                <button
                                    type="button"
                                    class="btn-edit"
                                    data-action="edit"
                                    data-id="{{ $user->id }}"
                                    data-username="{{ $user->username }}"
                                    data-display-name="{{ $user->display_name ?? '' }}"
                                    data-email="{{ $user->email }}"
                                    data-is-admin="{{ (int) $user->is_admin }}"
                                >
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('admin.users.delete', ['user' => $user->id]) }}" onsubmit="return confirm('Yakin hapus user dengan ID {{ $user->id }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete">Hapus</button>
                                </form>
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

<div id="create-user-modal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="create-user-title">
        <div class="modal-head">
            <h3 class="modal-title" id="create-user-title">Tambah User</h3>
            <button type="button" class="modal-close" id="close-create-modal" aria-label="Tutup">&times;</button>
        </div>
        <form id="create-user-form" class="edit-form" method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <input type="hidden" name="form_type" value="create">

            <div class="label-name">
                <label for="create-username">Username</label>
                <input
                    type="text"
                    id="create-username"
                    name="username"
                    maxlength="100"
                    value="{{ old('form_type') === 'create' ? old('username') : '' }}"
                    required
                >
            </div>

            <div class="label-name">
                <label for="create-email">Email</label>
                <input
                    type="email"
                    id="create-email"
                    name="email"
                    maxlength="100"
                    value="{{ old('form_type') === 'create' ? old('email') : '' }}"
                    required
                >
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

<div id="edit-user-modal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-user-title">
        <div class="modal-head">
            <h3 class="modal-title" id="edit-user-title">Edit User</h3>
            <button type="button" class="modal-close" id="close-edit-modal" aria-label="Tutup">&times;</button>
        </div>
        <form
            id="edit-user-form"
            class="edit-form"
            method="POST"
            action="{{ old('form_type') === 'edit' && old('user_id') ? route('admin.users.update', ['user' => old('user_id')]) : '' }}"
            data-action-base="{{ url('/admin/users') }}"
        >
            @csrf
            @method('PUT')
            <input type="hidden" id="edit-user-id" name="user_id" value="{{ old('form_type') === 'edit' ? old('user_id') : '' }}">
            <input type="hidden" name="form_type" value="edit">
            <input type="hidden" id="edit-username" name="username" value="{{ old('form_type') === 'edit' ? old('username') : '' }}">

            <div class="label-name">
                <label for="edit-display-name">Display Name</label>
                <input type="text" id="edit-display-name" name="displayName" maxlength="100" value="{{ old('displayName') }}" required>
            </div>

            <div class="label-name">
                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" maxlength="100" value="{{ old('email') }}" required>
            </div>

            <div class="label-name">
                <label for="edit-password">Password Baru (Opsional)</label>
                <input type="password" id="edit-password" name="password" minlength="8" placeholder="Kosongkan jika tidak ganti">
                <p class="modal-note">Kalau dikosongkan, password lama tetap dipakai.</p>
            </div>

            <div class="label-name">
                <label for="edit-is-admin">Role</label>
                <select id="edit-is-admin" name="isAdmin" required>
                    <option value="0" @selected((string) old('isAdmin', '0') === '0')>User Biasa</option>
                    <option value="1" @selected((string) old('isAdmin') === '1')>Admin</option>
                </select>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn-secondary" id="cancel-edit-btn">Batal</button>
                <button type="submit" class="btn-primary" id="save-edit-btn">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
    const openCreateModalBtn = document.getElementById('open-create-modal');
    const createModal = document.getElementById('create-user-modal');
    const createForm = document.getElementById('create-user-form');
    const closeCreateModalBtn = document.getElementById('close-create-modal');
    const cancelCreateBtn = document.getElementById('cancel-create-btn');
    const createUsernameInput = document.getElementById('create-username');

    const editModal = document.getElementById('edit-user-modal');
    const editForm = document.getElementById('edit-user-form');
    const closeEditModalBtn = document.getElementById('close-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const editModalTitle = document.getElementById('edit-user-title');
    const editUserIdInput = document.getElementById('edit-user-id');
    const editUsernameInput = document.getElementById('edit-username');
    const editDisplayNameInput = document.getElementById('edit-display-name');
    const editEmailInput = document.getElementById('edit-email');
    const editPasswordInput = document.getElementById('edit-password');
    const editIsAdminInput = document.getElementById('edit-is-admin');

    function openCreateModal() {
        createModal.classList.add('show');
        createModal.setAttribute('aria-hidden', 'false');
        createUsernameInput.focus();
    }

    function closeCreateModal() {
        createModal.classList.remove('show');
        createModal.setAttribute('aria-hidden', 'true');

        if (!{{ (bool) session('open_create_modal') || old('form_type') === 'create' ? 'true' : 'false' }}) {
            createForm.reset();
        }
    }

    function setEditFormAction(id) {
        editForm.action = `${editForm.dataset.actionBase}/${id}`;
    }

    function openEditModal(userData) {
        editUserIdInput.value = userData.id;
        editUsernameInput.value = userData.username || '';
        editDisplayNameInput.value = (userData.displayName || '').trim();
        editEmailInput.value = (userData.email || '').trim();
        editPasswordInput.value = '';
        editIsAdminInput.value = String(Number(userData.isAdmin || 0));
        editModalTitle.textContent = `Edit User: ${userData.username || '-'}`;
        setEditFormAction(userData.id);
        editModal.classList.add('show');
        editModal.setAttribute('aria-hidden', 'false');
        editDisplayNameInput.focus();
    }

    function closeEditModal() {
        editModal.classList.remove('show');
        editModal.setAttribute('aria-hidden', 'true');
    }

    openCreateModalBtn.addEventListener('click', openCreateModal);
    closeCreateModalBtn.addEventListener('click', closeCreateModal);
    cancelCreateBtn.addEventListener('click', closeCreateModal);
    closeEditModalBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);

    createModal.addEventListener('click', (event) => {
        if (event.target === createModal) {
            closeCreateModal();
        }
    });

    editModal.addEventListener('click', (event) => {
        if (event.target === editModal) {
            closeEditModal();
        }
    });

    document.querySelectorAll('button[data-action="edit"]').forEach((button) => {
        button.addEventListener('click', () => {
            openEditModal({
                id: button.dataset.id,
                username: button.dataset.username,
                displayName: button.dataset.displayName,
                email: button.dataset.email,
                isAdmin: button.dataset.isAdmin,
            });
        });
    });

    const shouldOpenCreateModal = {{ (bool) session('open_create_modal') || old('form_type') === 'create' ? 'true' : 'false' }};
    const shouldOpenEditModal = {{ (bool) session('open_edit_modal') || old('form_type') === 'edit' ? 'true' : 'false' }};

    if (shouldOpenCreateModal) {
        openCreateModal();
    }

    if (shouldOpenEditModal && editUserIdInput.value) {
        editModalTitle.textContent = `Edit User: ${editUsernameInput.value || '-'}`;
        setEditFormAction(editUserIdInput.value);
        editModal.classList.add('show');
        editModal.setAttribute('aria-hidden', 'false');
        editDisplayNameInput.focus();
    }
</script>

</body>
</html>
