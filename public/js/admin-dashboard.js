const totalUsers = document.getElementById('total-users');
const totalSessions = document.getElementById('total-sessions');
const activeToday = document.getElementById('active-today');
const latestSessionsBody = document.getElementById('latest-sessions-body');
const terminalBody = document.getElementById('terminal-body');
const logoutButton = document.getElementById('logout-button');

// ===== LOAD DASHBOARD =====
async function loadDashboard() {
  try {
    const response = await window.NeuroomApi.request('/api/v1/admin/dashboard');
    const stats = response.data?.stats || {};
    totalUsers.textContent = stats.total_users ?? 0;
    totalSessions.textContent = stats.total_sessions ?? 0;
    activeToday.textContent = stats.active_today ?? 0;

    const logs = Array.isArray(response.data?.latest_logs) ? response.data.latest_logs : [];
    
    // 1. Update Table
    if(latestSessionsBody) {
        latestSessionsBody.innerHTML = '';
        if (!logs.length) {
            latestSessionsBody.innerHTML = '<tr><td colspan="5">Belum ada data aktivitas.</td></tr>';
        } else {
            logs.slice(0, 10).forEach((log) => {
                const row = document.createElement('tr');
                const actionClass = log.action ? log.action.toLowerCase() : '';
                row.innerHTML = `
                    <td>#${log.id}</td>
                    <td>${log.username}</td>
                    <td><span class="log-action ${actionClass}">${log.action}</span></td>
                    <td>${log.description || '-'}</td>
                    <td>${window.NeuroomApi.formatDate(log.created_at)}</td>
                `;
                latestSessionsBody.appendChild(row);
            });
        }
    }

    // 2. Update Terminal
    if(terminalBody) {
        terminalBody.innerHTML = '';
        if (!logs.length) {
            terminalBody.innerHTML = '<div class="term-line"><span class="term-desc" style="color: #64748b;">> Menunggu aktivitas log terbaru...</span></div>';
        } else {
            // Render logs ke terminal (sekarang panel aktivitas terang)
            logs.forEach((log) => {
            const line = document.createElement('div');
            line.className = 'term-line';
            const actionClass = log.action ? log.action.toLowerCase() : '';
            line.innerHTML = `
                <span class="term-time">${window.NeuroomApi.formatDate(log.created_at)}</span>
                <span class="term-user">@${log.username}</span>
                <span class="term-action"><span class="log-action ${actionClass}">${log.action}</span></span>
                <span class="term-desc">${log.description || '-'}</span>
            `;
            terminalBody.appendChild(line);
            });
        }
    }

  } catch (error) {
    if(latestSessionsBody) latestSessionsBody.innerHTML = '<tr><td colspan="5">Gagal memuat dashboard.</td></tr>';
    if(terminalBody) terminalBody.innerHTML = '<div class="term-line"><span class="term-desc" style="color: #ef4444;">> ERROR FETCHING LOGS...</span></div>';
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

// Auto refresh logs setiap 8 detik agar serasa live monitoring
setInterval(loadDashboard, 8000);