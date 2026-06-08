const usersTableBody = document.getElementById('users-table-body');
const actionResult = document.getElementById('action-result');
const createModal = document.getElementById('create-user-modal');
const editModal = document.getElementById('edit-user-modal');
const createForm = document.getElementById('create-user-form');
const editForm = document.getElementById('edit-user-form');
const editUserIdInput = document.getElementById('edit-user-id');
const editDisplayNameInput = document.getElementById('edit-display-name');
const editEmailInput = document.getElementById('edit-email');
const editPasswordInput = document.getElementById('edit-password');
const editIsAdminInput = document.getElementById('edit-is-admin');
const editModalTitle = document.getElementById('edit-user-title');

let usersCache = [];

// ===== RESULT BOX =====
function setResult(message, isError = false) {
  actionResult.textContent = message || '';
  actionResult.style.color = isError ? '#b91c1c' : '#166534';
}

// ===== MODAL =====
function openModal(modal) {
  modal.classList.add('show');
  modal.setAttribute('aria-hidden', 'false');
}

function closeModal(modal) {
  modal.classList.remove('show');
  modal.setAttribute('aria-hidden', 'true');
}

// ===== RENDER TABLE =====
function renderUsers(users) {
  usersTableBody.innerHTML = '';

  if (!users.length) {
    usersTableBody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Belum ada data user.</td></tr>';
    return;
  }

  users.forEach((user) => {
    const row = document.createElement('tr');
    row.innerHTML = `
      <td>${user.id}</td>
      <td>${user.username}</td>
      <td>${user.display_name || '-'}</td>
      <td>${user.email}</td>
      <td>
        <span class="role-badge ${Number(user.is_admin) === 1 ? 'role-admin' : 'role-user'}">
          ${Number(user.is_admin) === 1 ? 'Admin' : 'User'}
        </span>
      </td>
      <td>${window.NeuroomApi.formatDate(user.created_at)}</td>
      <td>${window.NeuroomApi.formatDate(user.auth_updated_at)}</td>
      <td>${window.NeuroomApi.formatDate(user.updated_at)}</td>
      <td class="actions">
        <button type="button" class="btn-edit" data-action="edit" data-id="${user.id}">Edit</button>
        <button type="button" class="btn-delete" data-action="delete" data-id="${user.id}">Hapus</button>
      </td>
    `;
    usersTableBody.appendChild(row);
  });
}

// ===== LOAD DATA =====
async function loadUsers() {
  try {
    const response = await window.NeuroomApi.request('/api/v1/admin/users');
    usersCache = Array.isArray(response.data) ? response.data : [];
    renderUsers(usersCache);
  } catch (error) {
    setResult(error.message || 'Gagal memuat user.', true);
    usersTableBody.innerHTML = '<tr><td colspan="9" style="text-align:center;">Gagal memuat data.</td></tr>';
  }
}

// ===== OPEN CREATE MODAL =====
document.getElementById('open-create-modal')?.addEventListener('click', () => {
  createForm.reset();
  openModal(createModal);
});

// ===== CLOSE MODAL =====
document.getElementById('close-create-modal')?.addEventListener('click', () => closeModal(createModal));
document.getElementById('cancel-create-btn')?.addEventListener('click', () => closeModal(createModal));
document.getElementById('close-edit-modal')?.addEventListener('click', () => closeModal(editModal));
document.getElementById('cancel-edit-btn')?.addEventListener('click', () => closeModal(editModal));

createModal?.addEventListener('click', (event) => {
  if (event.target === createModal) {
    closeModal(createModal);
  }
});

editModal?.addEventListener('click', (event) => {
  if (event.target === editModal) {
    closeModal(editModal);
  }
});

// ===== SUBMIT CREATE =====
createForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  setResult('');

  try {
    const payload = Object.fromEntries(new FormData(createForm).entries());
    const response = await window.NeuroomApi.request('/api/v1/admin/users', {
      method: 'POST',
      data: payload,
    });

    setResult(response.reason || 'User berhasil ditambahkan.');
    closeModal(createModal);
    createForm.reset();
    await loadUsers();
  } catch (error) {
    const message = error.payload?.errors
      ? Object.values(error.payload.errors).flat()[0]
      : (error.payload?.reason || error.message);
    setResult(message || 'Gagal menambah user.', true);
  }
});

// ===== SUBMIT EDIT =====
editForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  setResult('');

  try {
    const userId = editUserIdInput.value;
    const payload = Object.fromEntries(new FormData(editForm).entries());
    if (!payload.password) {
      delete payload.password;
    }

    const response = await window.NeuroomApi.request(`/api/v1/admin/users/${userId}`, {
      method: 'PUT',
      data: payload,
    });

    setResult(response.reason || 'User berhasil diupdate.');
    closeModal(editModal);
    editForm.reset();
    await loadUsers();
  } catch (error) {
    const message = error.payload?.errors
      ? Object.values(error.payload.errors).flat()[0]
      : (error.payload?.reason || error.message);
    setResult(message || 'Gagal mengubah user.', true);
  }
});

// ===== ACTION BUTTONS =====
usersTableBody?.addEventListener('click', async (event) => {
  const button = event.target.closest('button[data-action]');
  if (!button) return;

  const userId = Number(button.dataset.id);
  const user = usersCache.find((item) => Number(item.id) === userId);
  if (!user) return;

  if (button.dataset.action === 'edit') {
    editUserIdInput.value = user.id;
    editDisplayNameInput.value = user.display_name || '';
    editEmailInput.value = user.email || '';
    editPasswordInput.value = '';
    editIsAdminInput.value = String(Number(user.is_admin || 0));
    editModalTitle.textContent = `Edit User: ${user.username}`;
    openModal(editModal);
    return;
  }

  if (button.dataset.action === 'delete') {
    const approved = window.confirm(`Yakin hapus user ${user.username}?`);
    if (!approved) return;

    try {
      const response = await window.NeuroomApi.request(`/api/v1/admin/users/${user.id}`, {
        method: 'DELETE',
      });
      setResult(response.reason || 'User berhasil dihapus.');
      await loadUsers();
    } catch (error) {
      setResult(error.payload?.reason || error.message || 'Gagal menghapus user.', true);
    }
  }
});

// ===== INIT =====
loadUsers();
