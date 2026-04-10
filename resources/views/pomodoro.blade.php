<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>Fokus — Pomodoro</title>

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pomodoro.css') }}">
  <script src="{{ asset('js/vendor/axios.min.js') }}"></script>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar">
  <div class="container nav-content">
    <div class="logo">Neuroom</div>

        <ul class="nav-menu">
      <li><a href="/utama">Beranda</a></li>
      <li><a href="/belajar">Belajar</a></li>
      <li><a href="/pomodoro">Fokus</a></li>
      <li><a href="/catatan">Catatan</a></li>
    </ul>
  </div>
</nav>

<div class="pomodoro">

  <!-- LEFT: STOPWATCH -->
  <div class="card center">
    <h2>Fokus Sekarang</h2>

    <!-- FE HANDLE (JS update) -->
    <div class="time" id="timeText">00:00:00</div>

    <p class="sub">Mulai stopwatch untuk sesi fokus</p>

    <div class="controls">
      <!-- FE HANDLE -->
      <button class="btn primary" id="startBtn">Start</button>
      <button class="btn" id="pauseBtn">Pause</button>
      <button class="btn" id="resetBtn">Reset</button>

      <!-- FE → BE (trigger simpan) -->
      <button class="btn danger" id="finishBtn">Selesai</button>
    </div>
  </div>

  <!-- RIGHT: HISTORY -->
  <div class="card">
    <div class="row">
      <h3>Riwayat Fokus</h3>

      <!-- FE → BE (fetch ulang data) -->
      <button class="btn ghost" id="refreshBtn">Refresh</button>
    </div>

    <div class="history" id="trackingList">

      {{-- =========================
         BACKEND RENDER DATA
         ========================= --}}
      {{-- contoh blade --}}
      @foreach($histories ?? [] as $item)
        <div class="history-item">
          <strong>{{ $item->duration }}</strong><br>
          <span>{{ $item->created_at }}</span>
        </div>
      @endforeach

      {{-- kalau kosong --}}
      @if(empty($histories))
        <p class="empty">Belum ada sesi fokus</p>
      @endif

    </div>
  </div>

</div>

<script src="{{ asset('js/pomodoro.js') }}" defer></script>

</body>
</html>