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

// ===== AXIOS (Laravel CSRF) =====
axios.defaults.headers.common['X-CSRF-TOKEN'] =
  document.querySelector('meta[name="csrf-token"]').getAttribute('content');

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
// POST /pomodoro/store
async function saveSession(duration) {
  try {
    await axios.post("/pomodoro/store", {
      duration: duration
    });
  } catch (err) {
    console.error("Gagal simpan:", err);
  }
}

// 🔸 BACKEND WAJIB:
// GET /pomodoro/history
async function loadHistory() {
  try {
    const res = await axios.get("/pomodoro/history");

    renderHistory(res.data);
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
      <span>${item.created_at}</span>
    `;

    trackingList.appendChild(div);
  });
}

// ===== FINISH (SIMPAN KE BACKEND) =====
async function finish() {
  const duration = formatTime(getCurrentTime());

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