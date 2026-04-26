// =============================
//  SIMPLE STOPWATCH (FINAL CLEAN)
//  Fokus: Stopwatch + History
// =============================

// ===== DOM =====
const timeText = document.getElementById("timeText");
const startBtn = document.getElementById("startBtn");
const pauseBtn = document.getElementById("pauseBtn");
const resetBtn = document.getElementById("resetBtn");
const finishBtn = document.getElementById("finishBtn");
const refreshBtn = document.getElementById("refreshBtn");
const trackingList = document.getElementById("trackingList");

// ===== STATE =====
let state = {
  running: false,
  startAt: null,
  elapsed: 0, // ms
};

// ===== UTIL =====
function pad(n) {
  return String(n).padStart(2, "0");
}

function formatTime(ms) {
  const total = Math.floor(ms / 1000);
  const h = Math.floor(total / 3600);
  const m = Math.floor((total % 3600) / 60);
  const s = total % 60;
  return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

function getCurrentTime() {
  if (!state.running) return state.elapsed;
  return state.elapsed + (Date.now() - state.startAt);
}

// ===== UI =====
function updateUI() {
  timeText.innerText = formatTime(getCurrentTime());
}

// ===== ACTIONS =====
function start() {
  if (state.running) return;

  state.running = true;
  state.startAt = Date.now();
}

function pause() {
  if (!state.running) return;

  state.elapsed += Date.now() - state.startAt;
  state.running = false;
}

function reset() {
  state.running = false;
  state.startAt = null;
  state.elapsed = 0;
  updateUI();
}

// ===== BACKEND =====

// 🔸 BACKEND WAJIB:
// POST /api/pomodoro/history
async function saveSession(duration) {
  return window.NeuroomApi.request("/api/v1/pomodoro/history", {
    method: "POST",
    data: {
      duration_seconds: duration,
    },
  });
}

// 🔸 BACKEND WAJIB:
// GET /api/pomodoro/history
async function loadHistory() {
  try {
    const res = await window.NeuroomApi.request("/api/v1/pomodoro/history");
    renderHistory(res.data?.sessions || []);
  } catch (err) {
    console.error("Gagal ambil history:", err);
  }
}

// ===== RENDER HISTORY =====
function renderHistory(list) {
  trackingList.innerHTML = "";

  if (!list.length) {
    trackingList.innerHTML = `<p class="empty">Belum ada sesi</p>`;
    return;
  }

  list.forEach(item => {
    const div = document.createElement("div");
    div.className = "history-item";

    div.innerHTML = `
      <strong>${item.duration}</strong><br>
      <span>${window.NeuroomApi.formatDate(item.created_at)}</span>
    `;

    trackingList.appendChild(div);
  });
}

// ===== FINISH (SIMPAN KE BACKEND) =====
async function finish() {
  const duration = Math.floor(getCurrentTime() / 1000);

  if (duration <= 0) {
    return;
  }

  await saveSession(duration);

  reset();
  loadHistory();
}

// ===== EVENTS =====
startBtn.addEventListener("click", start);
pauseBtn.addEventListener("click", pause);
resetBtn.addEventListener("click", reset);
finishBtn.addEventListener("click", finish);
refreshBtn.addEventListener("click", loadHistory);

// ===== LOOP =====
setInterval(updateUI, 500);

// ===== INIT =====
updateUI();
loadHistory();