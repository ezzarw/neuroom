const adminPomodoroBody = document.getElementById('admin-pomodoro-body');
const refreshPomodoroBtn = document.getElementById('refresh-pomodoro-btn');

// ===== LOAD DATA =====
async function loadAdminPomodoro() {
  try {
    const response = await window.NeuroomApi.request('/api/v1/admin/pomodoro');
    const sessions = response.data?.sessions || [];
    adminPomodoroBody.innerHTML = '';

    if (!sessions.length) {
      adminPomodoroBody.innerHTML = '<tr><td colspan="6">Belum ada data pomodoro.</td></tr>';
      return;
    }

    sessions.forEach((session) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>#${session.id}</td>
        <td>${session.username}</td>
        <td>${session.session}</td>
        <td>${session.duration}</td>
        <td>${session.date}</td>
        <td>${window.NeuroomApi.formatDate(session.created_at)}</td>
      `;
      adminPomodoroBody.appendChild(row);
    });
  } catch (error) {
    adminPomodoroBody.innerHTML = '<tr><td colspan="6">Gagal memuat data pomodoro.</td></tr>';
  }
}

// ===== REFRESH =====
refreshPomodoroBtn?.addEventListener('click', loadAdminPomodoro);

// ===== INIT =====
loadAdminPomodoro();
