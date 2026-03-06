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

            {{-- Route create belum ada, sementara button biasa --}}
            <button type="button" class="btn-cta">+ Tambah User</button>
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

                    <tbody>
                    @php
                        /**
                         * Helper aman untuk ambil value dari array/object.
                         * data_get bisa ambil dari array maupun object.
                         */
                        $fmt = function ($v) {
                            if (empty($v)) return '-';
                            try {
                                return \Carbon\Carbon::parse($v)->format('Y-m-d H:i');
                            } catch (\Throwable $e) {
                                return (string) $v;
                            }
                        };
                    @endphp

                    @forelse (($users ?? []) as $user)
                        @php
                            // Field utama sesuai pedoman + fallback snake_case
                            $id = data_get($user, 'id');

                            $username = data_get($user, 'username');
                            // fallback kalau ada typo lama / field lain
                            if (empty($username)) $username = data_get($user, 'uusername');

                            $displayName = data_get($user, 'displayName');
                            if (empty($displayName)) $displayName = data_get($user, 'display_name');

                            $email = data_get($user, 'email');

                            $isAdmin = data_get($user, 'isAdmin');
                            if ($isAdmin === null) $isAdmin = data_get($user, 'is_admin', 0);

                            // Tanggal: standar Laravel + fallback kolom custom lama
                            $createdAt = data_get($user, 'created_at');
                            if (empty($createdAt)) $createdAt = data_get($user, 'users_created_at');

                            // kalau backend kamu punya field khusus "auth updated", taruh di sini.
                            // fallback ke updated_at supaya kolom tidak kosong
                            $authUpdated = data_get($user, 'auth_updated_at');
                            if (empty($authUpdated)) $authUpdated = data_get($user, 'auth_update_at');
                            if (empty($authUpdated)) $authUpdated = data_get($user, 'updated_at');

                            $updatedAt = data_get($user, 'updated_at');
                            if (empty($updatedAt)) $updatedAt = data_get($user, 'users_update_at');
                        @endphp

                        <tr>
                            <td>{{ $id ?? '-' }}</td>
                            <td>{{ $username ?: '-' }}</td>
                            <td>{{ $displayName ?: '-' }}</td>
                            <td>{{ $email ?: '-' }}</td>
                            <td>
                                <span class="role-badge {{ (int)$isAdmin === 1 ? 'role-admin' : 'role-user' }}">
                                    {{ (int)$isAdmin === 1 ? 'Admin' : 'User' }}
                                </span>
                            </td>

                            <td>{{ $fmt($createdAt) }}</td>
                            <td>{{ $fmt($authUpdated) }}</td>
                            <td>{{ $fmt($updatedAt) }}</td>

                            <td class="actions">
                                <button
                                    type="button"
                                    class="btn-edit"
                                    data-id="{{ $id }}"
                                    data-username="{{ $username }}"
                                    data-display-name="{{ $displayName }}"
                                    data-email="{{ $email }}"
                                    data-is-admin="{{ (int)$isAdmin }}"
                                >
                                    Edit
                                </button>
                                <button type="button" class="btn-delete" data-id="{{ $id }}">Hapus</button>
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

<div id="edit-user-modal" class="modal-backdrop" aria-hidden="true">
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="edit-user-title">
        <div class="modal-head">
            <h3 class="modal-title" id="edit-user-title">Edit User</h3>
            <button type="button" class="modal-close" id="close-edit-modal" aria-label="Tutup">&times;</button>
        </div>
        <form id="edit-user-form" class="edit-form">
            <input type="hidden" id="edit-id" name="id">

            <div class="label-name">
                <label for="edit-display-name">Display Name</label>
                <input type="text" id="edit-display-name" name="displayName" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="edit-email">Email</label>
                <input type="email" id="edit-email" name="email" maxlength="100" required>
            </div>

            <div class="label-name">
                <label for="edit-password">Password Baru (Opsional)</label>
                <input type="password" id="edit-password" name="password" minlength="8" placeholder="Kosongkan jika tidak ganti">
                <p class="modal-note">Kalau diisi, minimal 8 karakter. Jika kosong akan dikirim `null`.</p>
            </div>

            <div class="label-name">
                <label for="edit-is-admin">Role</label>
                <select id="edit-is-admin" name="isAdmin" required>
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

<script>
    const actionResult = document.getElementById('action-result');
    const editModal = document.getElementById('edit-user-modal');
    const editForm = document.getElementById('edit-user-form');
    const closeEditModalBtn = document.getElementById('close-edit-modal');
    const cancelEditBtn = document.getElementById('cancel-edit-btn');
    const saveEditBtn = document.getElementById('save-edit-btn');
    const editModalTitle = document.getElementById('edit-user-title');
    const editIdInput = document.getElementById('edit-id');
    const editDisplayNameInput = document.getElementById('edit-display-name');
    const editEmailInput = document.getElementById('edit-email');
    const editPasswordInput = document.getElementById('edit-password');
    const editIsAdminInput = document.getElementById('edit-is-admin');

    function setResult(message, isError = false) {
        actionResult.textContent = message;
        actionResult.style.color = isError ? '#b91c1c' : '#166534';
    }

    function getBearerToken() {
        const token =
            localStorage.getItem('auth_token') ||
            localStorage.getItem('token') ||
            sessionStorage.getItem('auth_token') ||
            sessionStorage.getItem('token');

        if (!token) return null;
        return token.trim();
    }

    function handleApiError(status, data) {
        if (status === 401) return data?.message || '401: Token tidak valid / kredensial salah.';
        if (status === 403) return data?.message || '403: Akses ditolak (bukan admin).';
        if (status === 422) {
            const first = data?.errors ? Object.values(data.errors).flat()[0] : null;
            return first || data?.message || '422: Validasi gagal.';
        }
        if (status === 429) return data?.message || '429: Terlalu banyak request.';
        if (status === 500) return data?.message || '500: Error internal server.';
        return data?.message || `Request gagal (HTTP ${status}).`;
    }

    async function callAdminApi(url, method, payload) {
        const token = getBearerToken();
        const headers = {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        };

        if (token) {
            headers.Authorization = `Bearer ${token}`;
        }

        const response = await fetch(url, {
            method,
            credentials: 'include',
            headers,
            body: JSON.stringify(payload)
        });

        let data = null;
        try {
            data = await response.json();
        } catch (e) {}

        return { ok: response.ok, status: response.status, data };
    }

    function openEditModal(userData) {
        editIdInput.value = userData.id;
        editDisplayNameInput.value = (userData.displayName || '').trim();
        editEmailInput.value = (userData.email || '').trim();
        editPasswordInput.value = '';
        editIsAdminInput.value = String(Number(userData.isAdmin || 0));
        editModalTitle.textContent = `Edit User: ${userData.username || '-'}`;
        editModal.classList.add('show');
        editModal.setAttribute('aria-hidden', 'false');
    }

    function closeEditModal() {
        editModal.classList.remove('show');
        editModal.setAttribute('aria-hidden', 'true');
        editForm.reset();
    }

    function isEmailValid(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    document.querySelectorAll('.btn-edit').forEach((button) => {
        button.addEventListener('click', () => {
            openEditModal({
                id: Number(button.dataset.id),
                username: button.dataset.username || '',
                displayName: button.dataset.displayName || '',
                email: button.dataset.email || '',
                isAdmin: Number(button.dataset.isAdmin || 0),
            });
        });
    });

    closeEditModalBtn.addEventListener('click', closeEditModal);
    cancelEditBtn.addEventListener('click', closeEditModal);
    editModal.addEventListener('click', (event) => {
        if (event.target === editModal) closeEditModal();
    });

    editForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        saveEditBtn.disabled = true;

        const id = Number(editIdInput.value);
        const displayName = editDisplayNameInput.value.trim();
        const email = editEmailInput.value.trim();
        const password = editPasswordInput.value.trim();
        const isAdminRaw = String(editIsAdminInput.value).trim();

        if (!displayName || !email) {
            setResult('Display Name dan Email wajib diisi.', true);
            saveEditBtn.disabled = false;
            return;
        }

        if (!isEmailValid(email)) {
            setResult('Format email tidak valid.', true);
            saveEditBtn.disabled = false;
            return;
        }

        if (isAdminRaw !== '0' && isAdminRaw !== '1') {
            setResult('Role admin hanya boleh 0 atau 1.', true);
            saveEditBtn.disabled = false;
            return;
        }

        if (password.length > 0 && password.length < 8) {
            setResult('Jika diisi, password minimal 8 karakter.', true);
            saveEditBtn.disabled = false;
            return;
        }

        const payload = {
            id,
            displayName,
            email,
            isAdmin: Number(isAdminRaw),
            password: password.length > 0 ? password : null
        };

        setResult('Memproses edit user...');
        const { ok, status, data } = await callAdminApi('/api/admin/user-edit', 'PUT', payload);

        if (!ok) {
            setResult(handleApiError(status, data), true);
            saveEditBtn.disabled = false;
            return;
        }

        setResult('User berhasil diupdate.');
        closeEditModal();
        window.location.reload();
    });

    document.querySelectorAll('.btn-delete').forEach((button) => {
        button.addEventListener('click', async () => {
            const id = Number(button.dataset.id);
            if (!confirm(`Yakin hapus user dengan ID ${id}?`)) return;

            setResult('Memproses hapus user...');
            const { ok, status, data } = await callAdminApi('/api/admin/user-delete', 'DELETE', { id });

            if (!ok) {
                setResult(handleApiError(status, data), true);
                return;
            }

            setResult('User berhasil dihapus.');
            window.location.reload();
        });
    });
</script>

</body>
</html>
