<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Pelajaran</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/belajar.css') }}">
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

    <section class="choose-section">
        <h1 class="headline">Pilih yang ingin kamu pelajari</h1>
        <p class="subtitle">Sesuaikan pembelajaran dengan kebutuhan dan tujuanmu</p>

        <div class="card-wrapper">
            <!-- Card Pelajaran Umum -->
            <a href="/utama" class="card">
            <!-- GAMBAR -->
            <img src="{{ asset('img/umum.jpg') }}" class="card-img" alt="Pelajaran Umum">
                <h2>Pelajaran Umum</h2>
                <p>Matematika, Bahasa Indonesia dan Bahasa Inggris .</p>
                <span class="cta">Mulai Belajar →</span>
            </a>

            <!-- Card Pelajaran Kejuruan -->
            <a href="#" class="card">
             <!-- GAMBAR -->
            <img src="{{ asset('img/jurusan.jpg') }}" class="card-img" alt="Pelajaran Umum">
                <h2>Pelajaran Kejuruan</h2>
                <p>Materi sesuai jurusan seperti SIJA, RPL dan TKJ.</p>
                <span class="cta">Mulai Belajar →</span>
            </a>
        </div>
    </section>

</body>
</html>
