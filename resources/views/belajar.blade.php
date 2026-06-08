<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Belajar - Neuroom</title>

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/belajar.css') }}">
</head>
<body>

<x-navbar />

<main class="choose-section">
    <h1 class="headline">Ringkas materi dan simpan jadi catatan</h1>

    <section class="summary-box">
        <h2>Ringkas Materi AI</h2>
        <p class="summary-subtitle">Upload file dan dapatkan ringkasan otomatis.</p>

        <div class="summary-alert error" id="summary-error" style="display: none;"></div>

        <form method="POST" enctype="multipart/form-data" class="summary-form" id="summaryForm">
            <label class="drop-area" id="dropArea">
                <input type="file" name="document" id="fileInput" hidden required>
                <p id="fileText">Klik atau drag file ke sini</p>
            </label>

            <div class="summary-row">
                <select name="bahasa" id="bahasaInput">
                    <option value="indonesia">Indonesia</option>
                    <option value="english">English</option>
                </select>

                <button type="submit" id="submitBtn">Ringkas</button>
            </div>
        </form>

        <div id="loadingBox" class="loading-box">
            AI sedang merangkum...
        </div>

        <div class="summary-result" id="summary-result" style="display: none;">
            <p><strong>Status:</strong> <span id="summary-status">-</span></p>
            <p id="summary-message">-</p>

            <div class="summary-output" id="summary-output"></div>

            <div class="summary-actions">
                <button type="button" id="cancelSummaryBtn" class="btn-cancel">Batal</button>
                <button type="button" id="saveSummaryBtn" class="btn-save">Simpan ke Catatan</button>
            </div>
        </div>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="{{ asset('js/stateful-api.js') }}" defer></script>
<script src="{{ asset('js/belajar-summary.js') }}" defer></script>
</body>
</html>
