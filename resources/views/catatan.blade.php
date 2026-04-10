<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan — Neuroom</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catatan.css') }}">
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

<div class="container page">

    <!-- =========================
         PELAJARAN UMUM
    ========================= -->
    <div class="section">
        <h2>Pelajaran Umum</h2>

        <div class="grid">

            {{-- 🔸 BACKEND: loop data umum --}}
            @foreach($umum ?? [] as $item)
            <div class="card">

                <div class="card-header">
                    <h3>{{ $item->title }}</h3>

                    {{-- 🔸 BACKEND: status --}}
                    @if($item->is_completed)
                        <span class="badge success">Selesai</span>
                    @endif

                    @if($item->is_opened)
                        <span class="badge info">Dibuka</span>
                    @endif
                </div>

                <p class="desc">
                    {{ Str::limit($item->summary, 100) }}
                </p>

                {{-- 🔸 BACKEND: route ke detail --}}
                <a href="{{ route('catatan.show', $item->id) }}" class="btn">
                    Lihat Catatan
                </a>

            </div>
            @endforeach

            @if(empty($umum))
                <p class="empty">Belum ada catatan umum</p>
            @endif

        </div>
    </div>

    <!-- =========================
         KEJURUAN
    ========================= -->
    <div class="section">
        <h2>Kejuruan</h2>

        <div class="grid">

            {{-- 🔸 BACKEND: loop data kejuruan --}}
            @foreach($kejuruan ?? [] as $item)
            <div class="card">

                <div class="card-header">
                    <h3>{{ $item->title }}</h3>

                    @if($item->is_completed)
                        <span class="badge success">Selesai</span>
                    @endif

                    @if($item->is_opened)
                        <span class="badge info">Dibuka</span>
                    @endif
                </div>

                <p class="desc">
                    {{ Str::limit($item->summary, 100) }}
                </p>

                <a href="{{ route('catatan.show', $item->id) }}" class="btn">
                    Lihat Catatan
                </a>

            </div>
            @endforeach

            @if(empty($kejuruan))
                <p class="empty">Belum ada catatan kejuruan</p>
            @endif

        </div>
    </div>

</div>

</body>
</html>