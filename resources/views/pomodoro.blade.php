<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Pomodoro Timer</title>

  <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/pomodoro.css') }}">
  <script src="{{ asset('js/vendor/axios.min.js') }}"></script>
</head>
<body>
  
   <!-- NAVBAR -->
<nav class="navbar">
  <div class="container nav-content">
    <div class="logo">Neuroom</div>

   <ul class="nav-menu">
     <li><a href="/">Beranda</a></li>
    <li><a href="/belajar">Belajar</a></li>
  <li><a href="/pomodoro">Fokus</a></li>
  <li><a href="/catatan">Catatan</a></li>
</ul>
  </div>
</nav>

  <div class="app">
    <!-- LEFT: TIMER -->
    <div class="card">
      <div class="row">
        <div>
          <div class="title">Timer</div>
          <div class="muted" id="hintText">Stopwatch siap.</div>
        </div>
        <button class="btn ghost" id="switchBtn" title="Tidak bisa switch saat running">Switch mode</button>
      </div>

      <div class="timerWrap">
        <div class="circle">
          <!-- Ring -->
          <svg width="100%" height="100%" viewBox="0 0 120 120">
            <!-- background -->
            <circle cx="60" cy="60" r="52"
              fill="none" stroke="var(--ringBg)" stroke-width="10"
              stroke-linecap="round"></circle>

            <!-- progress -->
            <circle id="progressCircle" cx="60" cy="60" r="52"
              fill="none" stroke="var(--ring)" stroke-width="10"
              stroke-linecap="round"
              stroke-dasharray="326.7256"
              stroke-dashoffset="326.7256"></circle>
          </svg>

          <div class="inner">
            <span class="modeBadge" id="modeBadge">stopwatch</span>
            <div class="time" id="timeText">00:00:00</div>
            <div class="muted small" id="subText">Klik Start</div>
          </div>
        </div>

        <div class="btns">
          <button class="btn primary" id="startBtn">Start</button>
          <button class="btn" id="pauseBtn" disabled>Pause</button>
          <button class="btn" id="resetBtn">Reset</button>
          <button class="btn danger" id="finishBtn" disabled>Finish</button>
        </div>
      </div>
    </div>

    <!-- RIGHT: PRESET + TRACKING -->
    <div class="card">
      <div class="title">Pilih jam belajar</div>
      <div class="muted">Memilih preset akan mengubah timer jadi Pomodoro (countdown).</div>

      <div class="pill" style="margin-top:12px;">
        <button class="chip" data-preset="25">25 menit</button>
        <button class="chip" data-preset="50">50 menit</button>
        <button class="chip" data-preset="90">90 menit</button>
        <button class="chip" data-preset="120">120 menit</button>
      </div>

      <hr style="border:none;border-top:1px solid rgba(148,163,184,.12); margin:16px 0;">

      <div class="row">
        <div class="title">Tracking</div>
        <button class="btn ghost" id="refreshBtn">Refresh</button>
      </div>
      <div class="muted">Klik item untuk lihat detail (popup).</div>

      <div class="list" id="trackingList"></div>
    </div>

  </div>

  <!-- MODAL -->
  <div class="modal" id="modal">
    <div class="backdrop" id="modalClose"></div>
    <div class="panel">
      <div class="row">
        <div class="title">Detail Tracking</div>
        <button class="btn ghost" id="modalClose2">Tutup</button>
      </div>
      <div class="muted" id="detailHeader" style="margin: 6px 0 10px;"></div>
      <pre class="mono" id="detailBody"></pre>
    </div>
  </div>

<script src="{{ asset('js/pomodoro.js') }}" defer></script>

</body>
</html>
