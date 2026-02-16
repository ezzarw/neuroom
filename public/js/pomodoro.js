// =============================
//  FRONT-END TIMER (Stopwatch + Pomodoro)
//  - Timer jalan full di browser
//  - Backend (optional) hanya untuk simpan tracking
// =============================

const STORAGE_STATE = "timer_state_v1";
const STORAGE_TRACK = "tracking_local_v1"; // fallback kalau backend belum siap

// ===== DOM =====
const timeText = document.getElementById("timeText");
const modeBadge = document.getElementById("modeBadge");
const subText = document.getElementById("subText");
const hintText = document.getElementById("hintText");

const startBtn = document.getElementById("startBtn");
const pauseBtn = document.getElementById("pauseBtn");
const resetBtn = document.getElementById("resetBtn");
const finishBtn = document.getElementById("finishBtn");
const switchBtn = document.getElementById("switchBtn");
const refreshBtn = document.getElementById("refreshBtn");

const progressCircle = document.getElementById("progressCircle");
const trackingList = document.getElementById("trackingList");

const modal = document.getElementById("modal");
const modalClose = document.getElementById("modalClose");
const modalClose2 = document.getElementById("modalClose2");
const detailHeader = document.getElementById("detailHeader");
const detailBody = document.getElementById("detailBody");

// ===== circle math =====
const R = 52;
const CIRC = 2 * Math.PI * R; // ~326.7256
progressCircle.style.strokeDasharray = String(CIRC);

// ===== STATE =====
let state = {
  mode: "stopwatch",          // stopwatch | pomodoro
  running: false,

  // stopwatch
  startAt: null,              // ms
  elapsedBefore: 0,           // ms

  // pomodoro
  initialPomodoroMs: null,    // ms
  targetEndAt: null,          // ms
  selectedPresetMin: null,

  // session link (backend)
  hasSession: false,
  currentSessionId: null
};

// ===== UTIL =====
function pad(n){ return String(n).padStart(2,"0"); }

function formatSec(totalSec){
  const h = Math.floor(totalSec / 3600);
  const m = Math.floor((totalSec % 3600) / 60);
  const s = totalSec % 60;
  return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

function calcMsNow(){
  if (state.mode === "stopwatch") {
    let ms = state.elapsedBefore;
    if (state.running && state.startAt) ms += (Date.now() - state.startAt);
    return Math.max(0, ms);
  } else {
    if (!state.targetEndAt) return 0;
    return Math.max(0, state.targetEndAt - Date.now());
  }
}

function calcDurationSec(){
  // durasi yang sudah dilakukan (buat disimpan ke tracking)
  if (state.mode === "stopwatch") {
    const ms = state.elapsedBefore + (state.running && state.startAt ? (Date.now() - state.startAt) : 0);
    return Math.floor(Math.max(0, ms) / 1000);
  } else {
    const initial = state.initialPomodoroMs ?? 0;
    const remaining = state.targetEndAt ? Math.max(0, state.targetEndAt - Date.now()) : 0;
    const done = Math.max(0, initial - remaining);
    return Math.floor(done / 1000);
  }
}

function setRingProgress(progress01){
  // progress01 = 0..1
  const clamped = Math.min(1, Math.max(0, progress01));
  const offset = CIRC * (1 - clamped);
  progressCircle.style.strokeDashoffset = String(offset);
}

function updateUI(){
  modeBadge.textContent = state.mode;

  const ms = calcMsNow();
  const sec = Math.floor(ms / 1000);
  timeText.textContent = formatSec(sec);

  // ring behavior:
  // - stopwatch: ring "naik" per menit (loop 60 detik)
  // - pomodoro: ring menunjukkan progress dari total preset
  if (state.mode === "stopwatch") {
    const withinMinute = sec % 60;
    setRingProgress(withinMinute / 60);
    hintText.textContent = "Stopwatch: hitung naik.";
    subText.textContent = state.running ? "Berjalan..." : "Klik Start";
  } else {
    const initial = state.initialPomodoroMs ?? (25 * 60 * 1000);
    const remaining = ms;
    const done = Math.max(0, initial - remaining);
    const progress = initial ? (done / initial) : 0;
    setRingProgress(progress);
    hintText.textContent = "Pomodoro: countdown sampai 0.";
    subText.textContent = state.running ? "Fokus..." : "Pilih preset / Start";
  }

  startBtn.disabled = state.running;
  pauseBtn.disabled = !state.running;
  finishBtn.disabled = !state.hasSession;

  // switch mode disabled saat running biar gak bikin state campur
  switchBtn.disabled = state.running;
  switchBtn.title = state.running ? "Pause dulu baru switch mode" : "";
}

function saveState(){
  localStorage.setItem(STORAGE_STATE, JSON.stringify(state));
}

function loadState(){
  const raw = localStorage.getItem(STORAGE_STATE);
  if (!raw) return;
  try {
    const s = JSON.parse(raw);
    state = { ...state, ...s };
  } catch {}
}

// ===== TRACKING (fallback local) =====
function loadLocalTracking(){
  try { return JSON.parse(localStorage.getItem(STORAGE_TRACK) || "[]"); }
  catch { return []; }
}
function saveLocalTracking(list){
  localStorage.setItem(STORAGE_TRACK, JSON.stringify(list));
}

function renderTracking(list){
  trackingList.innerHTML = "";
  if (!list.length) {
    const div = document.createElement("div");
    div.className = "muted";
    div.textContent = "Belum ada tracking.";
    trackingList.appendChild(div);
    return;
  }

  list.slice().reverse().forEach(item => {
    const el = document.createElement("div");
    el.className = "item";
    el.innerHTML = `
      <div class="meta">
        <div>
          <div style="font-weight:700; text-transform:capitalize">${item.mode}</div>
          <div class="muted small">${item.started_at}</div>
        </div>
        <div class="mono">${formatSec(item.duration_sec || 0)}</div>
      </div>
    `;
    el.addEventListener("click", () => openModal(item));
    trackingList.appendChild(el);
  });
}

function openModal(item){
  detailHeader.textContent = `#${item.id || "-"} • ${item.started_at} → ${item.ended_at || "-"}`;
  detailBody.textContent = JSON.stringify(item, null, 2);
  modal.classList.add("show");
}
function closeModal(){ modal.classList.remove("show"); }

modalClose.addEventListener("click", closeModal);
modalClose2.addEventListener("click", closeModal);

// ===== BACKEND HOOK (optional) =====
// Kalau backend temenmu siap, ganti USE_BACKEND = true
// lalu sesuaikan endpointnya.
const USE_BACKEND = false;

async function apiList(){
  if (!USE_BACKEND) return loadLocalTracking();

  const res = await fetch("/api/study-sessions");
  return await res.json();
}

async function apiStart(){
  if (!USE_BACKEND) {
    // local mode: bikin "session id" dummy
    state.currentSessionId = String(Date.now());
    state.hasSession = true;
    saveState();
    return;
  }

  const res = await fetch("/api/study-sessions/start", {
    method:"POST",
    headers: jsonHeaders(),
    body: JSON.stringify({
      mode: state.mode,
      meta: state.mode === "pomodoro"
        ? { preset_min: state.selectedPresetMin, target_end_at: state.targetEndAt }
        : {}
    })
  });
  const data = await res.json();
  state.currentSessionId = data.id;
  state.hasSession = true;
  saveState();
}

async function apiStop(durationSec){
  if (!USE_BACKEND) {
    const list = loadLocalTracking();
    const now = new Date();
    list.push({
      id: state.currentSessionId,
      mode: state.mode,
      started_at: state._startedAtISO || new Date().toISOString(),
      ended_at: now.toISOString(),
      duration_sec: durationSec,
      meta: {
        preset_min: state.selectedPresetMin,
      }
    });
    saveLocalTracking(list);
    return;
  }

  await fetch("/api/study-sessions/stop", {
    method:"POST",
    headers: jsonHeaders(),
    body: JSON.stringify({
      id: state.currentSessionId,
      duration_sec: durationSec,
      meta: state.mode === "pomodoro" ? { preset_min: state.selectedPresetMin } : {}
    })
  });
}

function jsonHeaders(){
  return {
    "Content-Type":"application/json",
    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content ?? ""
  }
}

// ===== ACTIONS =====
function start(){
  if (state.running) return;
  state.running = true;

  // stopwatch
  if (state.mode === "stopwatch") {
    state.startAt = Date.now();
  } else {
    // pomodoro default kalau belum pilih preset
    if (!state.targetEndAt) setPreset(25);
  }

  // catat start ISO (untuk local tracking)
  state._startedAtISO = new Date().toISOString();

  if (!state.hasSession) apiStart();
  saveState();
  updateUI();
}

function pause(){
  if (!state.running) return;
  state.running = false;

  if (state.mode === "stopwatch" && state.startAt) {
    state.elapsedBefore += (Date.now() - state.startAt);
    state.startAt = null;
  }
  saveState();
  updateUI();
}

function reset(){
  // reset timer display (bukan menyimpan tracking)
  state.running = false;
  state.startAt = null;
  state.elapsedBefore = 0;

  state.initialPomodoroMs = null;
  state.targetEndAt = null;
  state.selectedPresetMin = null;

  saveState();
  updateUI();
}

async function finish(){
  if (!state.hasSession) return;

  const durationSec = calcDurationSec();
  await apiStop(durationSec);

  // clear session link
  state.hasSession = false;
  state.currentSessionId = null;

  reset();

  const list = await apiList();
  renderTracking(list);
}

function switchMode(){
  if (state.running) return;
  state.mode = (state.mode === "stopwatch") ? "pomodoro" : "stopwatch";
  reset();
}

function setPreset(min){
  if (state.running) return;
  state.mode = "pomodoro";
  state.selectedPresetMin = min;
  state.initialPomodoroMs = min * 60 * 1000;
  state.targetEndAt = Date.now() + state.initialPomodoroMs;
  saveState();
  updateUI();
  // highlight chips
  document.querySelectorAll("[data-preset]").forEach(btn=>{
    btn.classList.toggle("active", Number(btn.dataset.preset) === min);
  });
}

// ===== EVENT WIRING =====
startBtn.addEventListener("click", start);
pauseBtn.addEventListener("click", pause);
resetBtn.addEventListener("click", reset);
finishBtn.addEventListener("click", finish);
switchBtn.addEventListener("click", switchMode);
refreshBtn.addEventListener("click", async () => {
  const list = await apiList();
  renderTracking(list);
});

document.querySelectorAll("[data-preset]").forEach(btn=>{
  btn.addEventListener("click", () => setPreset(Number(btn.dataset.preset)));
});

// ===== LOOP =====
loadState();

// render tracking first load
apiList().then(renderTracking);

// tick loop
setInterval(() => {
  // auto finish pomodoro when 0
  if (state.mode === "pomodoro" && state.running && calcMsNow() === 0) {
    state.running = false;
    finish();
  }
  updateUI();
  saveState();
}, 250);

updateUI();
