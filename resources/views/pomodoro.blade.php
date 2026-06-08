<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="user-id" content="{{ auth()->id() }}">
  <title>Fokus — Pomodoro</title>

  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <link rel="stylesheet" href="{{ asset('css/pomodoro.css') }}">
</head>

<body>

<!-- NAVBAR -->
<x-navbar />

<div class="pomodoro">

  <!-- LEFT: TIMER -->
  <div class="card center">
    <h2>Fokus Sekarang <span id="connectionBadge" class="badge-connecting">⟳</span></h2>

    <div class="time" id="timeText">00:25:00</div>

    <p class="sub">Mulai sesi pomodoro untuk fokus belajar</p>

    <div class="controls">
      <button class="btn primary" id="startBtn">Start</button>
      <button class="btn" id="pauseBtn">Pause</button>
      <button class="btn" id="resetBtn">Reset</button>
      <button class="btn danger" id="finishBtn">Selesai</button>
    </div>
  </div>

  <!-- RIGHT: HISTORY -->
  <div class="card">
    <div class="row">
      <h3>Riwayat Fokus</h3>

      <button class="btn ghost" id="refreshBtn">
        Refresh
      </button>
    </div>

    <div class="history" id="trackingList">
      <p class="empty">Belum ada sesi fokus</p>
    </div>
  </div>

</div>

<!-- Echo + Pusher for WebSocket -->
<script src="https://js.pusher.com/8.5.0/pusher.min.js"></script>
<script src="https://unpkg.com/laravel-echo@2.3.4/dist/echo.iife.js"></script>
<script>
  window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'neuroom-key',
    wsHost: '127.0.0.1',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
  });

  const badge = document.getElementById('connectionBadge');

  Echo.connector.pusher.connection.bind('connected', () => {
    badge.textContent = '●';
    badge.className = 'badge-connected';
  });

  Echo.connector.pusher.connection.bind('disconnected', () => {
    badge.textContent = '○';
    badge.className = 'badge-disconnected';
  });

  Echo.connector.pusher.connection.bind('error', () => {
    badge.textContent = '✗';
    badge.className = 'badge-error';
  });
</script>

<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/pomodoro.js') }}" defer></script>
<script src="{{ asset('js/pomodoro-echo.js') }}" defer></script>

</body>
</html>
