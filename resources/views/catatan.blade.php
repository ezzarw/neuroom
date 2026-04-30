<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catatan — Neuroom</title>

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('css/catatan.css') }}">

    <!-- ICON -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>

<x-navbar />

<div class="container page">

    <h2 class="title">Catatan Saya</h2>

    <!-- SEARCH -->
    <div class="notes-header">
    <div class="search-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" id="searchInput" placeholder="Cari catatan...">
    </div>
</div>

    <!-- NOTES -->
    <div class="notes-grid">

        @forelse($notes ?? [] as $note)
        <div class="note-card" data-title="{{ strtolower($note->title) }}">
            <h3>{{ $note->title }}</h3>

            <p>
                {{ Str::limit(strip_tags($note->content), 100) }}
            </p>

            <div class="note-meta">
                {{ date('d M Y', strtotime($note->created_at)) }}
            </div>

            <div class="note-actions">

                <!-- VIEW -->
                <button class="open-view"
                    data-title="{{ $note->title }}"
                    data-content="{{ $note->content }}">
                    <i class="fa-solid fa-eye"></i>
                </button>

                <!-- EDIT -->
                <button class="open-edit"
                    data-title="{{ $note->title }}"
                    data-content="{{ $note->content }}">
                    <i class="fa-solid fa-pen"></i>
                </button>

                <!-- DELETE -->
                <button class="btn-delete"
                    data-id="{{ $note->id }}">
                    <i class="fa-solid fa-trash"></i>
                </button>

            </div>
        </div>

        @empty
        <div class="empty-state">
            <h3>Belum ada catatan</h3>
            <p>Mulai belajar dan buat catatan pertamamu 🚀</p>
            <a href="/belajar" class="btn-primary">Mulai Belajar</a>
        </div>
        @endforelse

    </div>

</div>

<!-- FLOAT BUTTON -->
<button class="fab" id="addNote">
    <i class="fa-solid fa-plus"></i>
</button>

<!-- MODAL VIEW -->
<div class="modal" id="viewModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="viewTitle"></h3>
            <button class="close">&times;</button>
        </div>
        <div id="viewContent" class="note-view"></div>
    </div>
</div>

<!-- MODAL EDIT -->
<div class="modal" id="editModal">
    <div class="modal-content">

        <div class="modal-header">
            <input id="editTitle" placeholder="Judul catatan...">
            <button class="close">&times;</button>
        </div>

        <div class="toolbar">
            <button onclick="formatText('bold')"><b>B</b></button>
            <button onclick="formatText('italic')"><i>I</i></button>
            <button onclick="formatText('underline')"><u>U</u></button>
        </div>

        <div id="editor" contenteditable="true"></div>

        <div class="modal-footer">
            <button class="btn" id="saveNote">Simpan</button>
        </div>

    </div>
</div>

<!-- JS -->
<script src="{{ asset('js/catatan.js') }}"></script>

</body>
</html>