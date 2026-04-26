const totalUsers = document.getElementById('total-users');
const totalSessions = document.getElementById('total-sessions');
const activeToday = document.getElementById('active-today');
const latestSessionsBody = document.getElementById('latest-sessions-body');
const logoutButton = document.getElementById('logout-button');

// ===== LOAD DASHBOARD =====
async function loadDashboard() {
  try {
    const response = await window.NeuroomApi.request('/api/v1/admin/dashboard');
    const stats = response.data?.stats || {};
    totalUsers.textContent = stats.total_users ?? 0;
    totalSessions.textContent = stats.total_sessions ?? 0;
    activeToday.textContent = stats.active_today ?? 0;

    const sessions = response.data?.latest_sessions || [];
    latestSessionsBody.innerHTML = '';

    if (!sessions.length) {
      latestSessionsBody.innerHTML = '<tr><td colspan="5">Belum ada data.</td></tr>';
      return;
    }

    sessions.forEach((session) => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>#${session.id}</td>
        <td>${session.username}</td>
        <td>${session.session}</td>
        <td>${session.duration}</td>
        <td>${window.NeuroomApi.formatDate(session.created_at || session.date)}</td>
      `;
      latestSessionsBody.appendChild(row);
    });
  } catch (error) {
    latestSessionsBody.innerHTML = '<tr><td colspan="5">Gagal memuat dashboard.</td></tr>';
  }
}

// ===== LOGOUT =====
logoutButton?.addEventListener('click', async () => {
  try {
    const response = await window.NeuroomApi.request('/api/v1/auth/logout', {
      method: 'POST',
    });
    window.location.href = response.meta?.redirect_to || '/';
  } catch (error) {
    window.location.href = '/';
  }
});

// ===== INIT =====
loadDashboard();
