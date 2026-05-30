// =============================
// POMODORO FRONTEND
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
let currentPomodoro = null;

// ===== UTIL =====
function pad(n) {
  return String(n).padStart(2, "0");
}

function formatTime(seconds) {
  const h = Math.floor(seconds / 3600);
  const m = Math.floor((seconds % 3600) / 60);
  const s = seconds % 60;

  return `${pad(h)}:${pad(m)}:${pad(s)}`;
}

// ===== TIMER =====
function getRemainingSeconds() {
  if (!currentPomodoro) return 0;

  if (currentPomodoro.status === "paused") {
    return currentPomodoro.remaining || 0;
  }

  if (currentPomodoro.status !== "running") {
    return 0;
  }

  const now = new Date();
  const endsAt = new Date(currentPomodoro.ends_at);

  return Math.max(
    0,
    Math.floor((endsAt.getTime() - now.getTime()) / 1000)
  );
}

function updateUI() {
  const remaining = getRemainingSeconds();

  timeText.innerText = formatTime(remaining);

  if (
    currentPomodoro &&
    currentPomodoro.status === "running" &&
    remaining <= 0
  ) {
    finish();
  }
}

// ===== API =====

async function loadCurrent() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/current"
    );

    currentPomodoro = res.data;

    if (currentPomodoro.status === "idle") {
      currentPomodoro = null;
    }

    updateUI();
  } catch (err) {
    console.error("Load current gagal:", err);
  }
}

async function startPomodoro() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/start",
      {
        method: "POST"
      }
    );

    currentPomodoro = res.data;

    updateUI();
  } catch (err) {
    console.error("Start gagal:", err);
  }
}

async function pausePomodoro() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/pause",
      {
        method: "POST"
      }
    );

    currentPomodoro = res.data;

    updateUI();
  } catch (err) {
    console.error("Pause gagal:", err);
  }
}

async function resumePomodoro() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/resume",
      {
        method: "POST"
      }
    );

    currentPomodoro = res.data;

    updateUI();
  } catch (err) {
    console.error("Resume gagal:", err);
  }
}

async function stopPomodoro() {
  try {
    await window.NeuroomApi.request(
      "/api/v1/pomodoro/stop",
      {
        method: "POST"
      }
    );

    currentPomodoro = null;

    updateUI();

    await loadHistory();
  } catch (err) {
    console.error("Stop gagal:", err);
  }
}

async function finish() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/finish",
      {
        method: "POST"
      }
    );

    currentPomodoro = res.data;

    if (
      currentPomodoro.status === "finished" ||
      currentPomodoro.status === "idle"
    ) {
      currentPomodoro = null;
    }

    updateUI();

    await loadHistory();
  } catch (err) {
    console.error("Finish gagal:", err);
  }
}

// ===== HISTORY =====

async function loadHistory() {
  try {
    const res = await window.NeuroomApi.request(
      "/api/v1/pomodoro/history"
    );

    renderHistory(res.data?.records || []);
  } catch (err) {
    console.error("History gagal:", err);
  }
}

function renderHistory(records) {
  trackingList.innerHTML = "";

  if (!records.length) {
    trackingList.innerHTML =
      '<p class="empty">Belum ada sesi fokus</p>';
    return;
  }

  records.forEach(item => {
    const div = document.createElement("div");

    div.className = "history-item";

    div.innerHTML = `
      <strong>
        ${window.NeuroomApi.formatDuration(
          item.actual_seconds || 0
        )}
      </strong>
      <br>

      <span>
        ${item.status || "-"}
      </span>
      <br>

      <small>
        ${window.NeuroomApi.formatDate(
          item.finished_at ||
          item.stopped_at ||
          item.started_at
        )}
      </small>
    `;

    trackingList.appendChild(div);
  });
}

// ===== EVENTS =====

startBtn.addEventListener("click", async () => {
  if (
    currentPomodoro &&
    currentPomodoro.status === "paused"
  ) {
    await resumePomodoro();
  } else {
    await startPomodoro();
  }
});

pauseBtn.addEventListener("click", pausePomodoro);

resetBtn.addEventListener("click", stopPomodoro);

// tombol selesai = stop manual
finishBtn.addEventListener("click", stopPomodoro);

refreshBtn.addEventListener("click", async () => {
  await loadCurrent();
  await loadHistory();
});

// ===== LOOP =====

setInterval(updateUI, 1000);

// ===== INIT =====

(async function () {
  await loadCurrent();
  await loadHistory();
})();