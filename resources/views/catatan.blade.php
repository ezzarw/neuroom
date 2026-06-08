<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    <div class="notes-grid" id="notesGrid">

        <!-- LOADING -->
        <div class="empty-state" id="loadingState">
            <h3>Memuat catatan...</h3>
        </div>

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
            <input id="editTitle" placeholder="Judul catatan..." spellcheck="false">
            <button class="close">&times;</button>
        </div>

        <div class="toolbar" aria-label="Toolbar editor catatan">
            <button type="button" class="toolbar-btn" data-command="undo" title="Undo">
                <i class="fa-solid fa-rotate-left"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="redo" title="Redo">
                <i class="fa-solid fa-rotate-right"></i>
            </button>
            <span class="toolbar-separator"></span>
            <select id="blockFormat" class="toolbar-select" title="Format teks">
                <option value="P">Paragraf</option>
                <option value="H2">Judul</option>
                <option value="H3">Subjudul</option>
            </select>
            <span class="toolbar-separator"></span>
            <button type="button" class="toolbar-btn" data-command="bold" title="Bold">
                <strong>B</strong>
            </button>
            <button type="button" class="toolbar-btn" data-command="italic" title="Italic">
                <em>I</em>
            </button>
            <button type="button" class="toolbar-btn" data-command="underline" title="Underline">
                <u>U</u>
            </button>
            <span class="toolbar-separator"></span>
            <button type="button" class="toolbar-btn" data-command="insertUnorderedList" title="Bullet list">
                <i class="fa-solid fa-list-ul"></i>
            </button>
            <button type="button" class="toolbar-btn" data-command="insertOrderedList" title="Numbered list">
                <i class="fa-solid fa-list-ol"></i>
            </button>
        </div>

        <textarea id="editor" placeholder="Tulis catatan Markdown di sini..." spellcheck="false"></textarea>

        <div class="modal-footer">
            <button class="btn" id="saveNote">Simpan</button>
        </div>

    </div>
</div>

<!-- API -->
<script src="{{ asset('js/stateful-api.js') }}"></script>

<!-- JS -->
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.2.6/dist/purify.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="{{ asset('js/catatan.js') }}"></script>

<script>

// =========================
// LOAD NOTES API
// =========================

async function loadNotes() {

    try {

        const response = await window.NeuroomApi.request(
            '/api/v1/notes'
        );

        const notesGrid = document.getElementById('notesGrid');
        const notes = response.data?.items || [];

        notesGrid.innerHTML = '';

        // EMPTY STATE
        if (!notes.length) {

            notesGrid.innerHTML = `
                <div class="empty-state">
                    <h3>Belum ada catatan</h3>
                    <p>Mulai belajar dan buat catatan pertamamu 🚀</p>
                    <a href="/belajar" class="btn-primary">
                        Mulai Belajar
                    </a>
                </div>
            `;

            return;
        }

        // RENDER NOTES
        notes.forEach(note => {

            notesGrid.innerHTML += `
                <div class="note-card"
                     data-title="${escapeHtml(note.title.toLowerCase())}">

                    <h3>${escapeHtml(note.title)}</h3>

                    <p>
                        ${escapeHtml(markdownToText(note.content)).slice(0, 110)}...
                    </p>

                    <div class="note-meta">
                        ${formatDate(note.created_at)}
                    </div>

                    <div class="note-actions">

                        <!-- VIEW -->
                        <button class="open-view"
                            data-title="${escapeHtml(note.title)}"
                            data-content="${escapeHtml(note.content)}">

                            <i class="fa-solid fa-eye"></i>
                        </button>

                        <!-- EDIT -->
                        <button class="open-edit"
                            data-id="${note.id}"
                            data-title="${escapeHtml(note.title)}"
                            data-content="${escapeHtml(note.content)}">

                            <i class="fa-solid fa-pen"></i>
                        </button>

                        <!-- DELETE -->
                        <button class="btn-delete"
                            data-id="${note.id}">

                            <i class="fa-solid fa-trash"></i>
                        </button>

                    </div>
                </div>
            `;
        });

    } catch (error) {

        console.error('Load notes error:', error);

        document.getElementById('notesGrid').innerHTML = `
            <div class="empty-state">
                <h3>Gagal memuat catatan</h3>
                <p>Coba refresh halaman.</p>
            </div>
        `;
    }
}


// =========================
// HELPERS
// =========================

function stripHtml(html) {

    const div = document.createElement('div');

    div.innerHTML = html;

    return div.textContent || div.innerText || '';
}

function markdownToText(markdown) {

    const source = markdown || '';
    const html = window.marked
        ? window.marked.parse(source)
        : source.replace(/\n/g, '<br>');

    return stripHtml(html);
}

function escapeHtml(text) {

    const div = document.createElement('div');

    div.textContent = text;

    return div.innerHTML;
}

function formatDate(dateString) {

    const date = new Date(dateString);

    return date.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric'
    });
}


// =========================
// INIT
// =========================

loadNotes();

</script>

</body>
</html>
