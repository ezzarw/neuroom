<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Pelajaran</title>

    <link rel="stylesheet" href="{{ asset('css/belajar.css') }}">
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

<section class="choose-section">

    <h1 class="headline">Summary dan Quiz yang bisa kamu pelajari</h1>
    <!-- 🔥 SUMMARY -->
    <div class="summary-box">

        <h2>Ringkas Materi AI</h2>
        <p class="summary-subtitle">
            Upload file dan dapatkan ringkasan otomatis
        </p>

        @if ($errors->any())
            <div class="summary-alert error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/summary" enctype="multipart/form-data" class="summary-form" id="summaryForm">
            @csrf

            <!-- DRAG AREA -->
            <label class="drop-area" id="dropArea">
                <input type="file" name="document" id="fileInput" hidden required>

                <p id="fileText">Klik atau drag file ke sini</p>
            </label>

            <div class="summary-row">
                <select name="bahasa">
                    <option value="indonesia">Indonesia</option>
                    <option value="english">English</option>
                </select>

                <button type="submit" id="submitBtn">Ringkas</button>
            </div>
        </form>

        <!-- LOADING -->
        <div id="loadingBox" class="loading-box">
            AI sedang merangkum...
        </div>

        <!-- HASIL -->
        @if(session('summary_result'))
            <div class="summary-result">
                <p><strong>Status:</strong> {{ session('summary_result.status') }}</p>
                <p>{{ session('summary_result.message') }}</p>

                <div class="summary-output">
    @php
        $output = session('summary_result.output');
    @endphp

    @if(is_array($output))
        <ul>
            @foreach($output as $item)
                <li>{{ $item }}</li>
            @endforeach
        </ul>
    @else
        {{ $output }}
    @endif
</div>
            </div>
        @endif

    </div>
    <h1 class="headline">Pilih Quiz sesuai kebutuhanmu</h1>
    <!-- CARD -->
    <div class="card-wrapper">
        <a href="/utama" class="card">
            <img src="{{ asset('img/umum.jpg') }}" class="card-img">
            <h2>Pelajaran Umum</h2>
            <p>Matematika, Bahasa Indonesia dan Bahasa Inggris.</p>
            <span class="cta">Mulai Quiz →</span>
        </a>

        <a href="#" class="card">
            <img src="{{ asset('img/jurusan.jpg') }}" class="card-img">
            <h2>Pelajaran Kejuruan</h2>
            <p>Materi sesuai jurusan seperti SIJA, RPL dan TKJ.</p>
            <span class="cta">Mulai Quiz →</span>
        </a>
    </div>

</section>

<!-- SCRIPT -->
<script>
const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('fileInput');
const fileText = document.getElementById('fileText');
const form = document.getElementById('summaryForm');
const loadingBox = document.getElementById('loadingBox');

// klik buka file
dropArea.addEventListener('click', () => fileInput.click());

// drag effect
dropArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('dragover');
});

// drop file
dropArea.addEventListener('drop', (e) => {
    e.preventDefault();
    dropArea.classList.remove('dragover');

    fileInput.files = e.dataTransfer.files;
    fileText.innerText = e.dataTransfer.files[0].name;
});

// change file
fileInput.addEventListener('change', () => {
    fileText.innerText = fileInput.files[0].name;
});

// loading state
form.addEventListener('submit', () => {
    loadingBox.style.display = 'block';
});
</script>

</body>
</html>