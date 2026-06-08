const viewModal = document.getElementById("viewModal");
const editModal = document.getElementById("editModal");
const viewTitle = document.getElementById("viewTitle");
const viewContent = document.getElementById("viewContent");
const editTitle = document.getElementById("editTitle");
const editor = document.getElementById("editor");
const saveNoteButton = document.getElementById("saveNote");
const blockFormat = document.getElementById("blockFormat");
const toolbarButtons = document.querySelectorAll(".toolbar-btn");

let currentNoteId = null;

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text || "";
    return div.innerHTML;
}

function renderMarkdown(markdown) {
    const source = markdown || "";
    let html;

    if (window.marked) {
        window.marked.setOptions({
            breaks: true,
            gfm: true,
        });
        html = window.marked.parse(source);
    } else {
        html = `<p>${escapeHtml(source).replace(/\n/g, "<br>")}</p>`;
    }

    return window.DOMPurify ? window.DOMPurify.sanitize(html) : html;
}

// SEARCH (dengan debounce)
const searchInput = document.getElementById("searchInput");
let searchTimer;
searchInput?.addEventListener("keyup", function () {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll(".note-card").forEach(card => {
            const title = card.dataset.title;
            card.style.display = title.includes(keyword) ? "flex" : "none";
        });
    }, 300);
});

// VIEW / EDIT / DELETE (delegated)
document.body.addEventListener("click", (event) => {
    const viewBtn = event.target.closest(".open-view");
    if (viewBtn) {
        viewModal.style.display = "flex";
        viewTitle.innerText = viewBtn.dataset.title;
        viewContent.innerHTML = renderMarkdown(viewBtn.dataset.content);
        return;
    }

    const editBtn = event.target.closest(".open-edit");
    if (editBtn) {
        currentNoteId = editBtn.dataset.id || null;
        editModal.style.display = "flex";
        editTitle.value = editBtn.dataset.title;
        editor.value = editBtn.dataset.content || "";
        setTimeout(() => {
            editor.focus();
            updateToolbarState();
        }, 0);
        return;
    }

    const deleteBtn = event.target.closest(".btn-delete");
    if (deleteBtn) {
        if (confirm("Hapus catatan?")) {
            deleteNote(deleteBtn.dataset.id);
        }
    }
});

async function deleteNote(noteId) {
    try {
        await window.NeuroomApi.request(`/api/v1/notes/${noteId}`, {
            method: 'DELETE',
        });

        await loadNotes();
    } catch (error) {
        console.error('Hapus catatan gagal:', error);
        alert('Gagal menghapus catatan. Coba lagi.');
    }
}

// ADD
document.getElementById("addNote").onclick = () => {
    currentNoteId = null;
    editModal.style.display = "flex";
    editTitle.value = "";
    editor.value = "";
    blockFormat.value = "P";
    setTimeout(() => editor.focus(), 0);
};

// CLOSE
document.querySelectorAll(".close").forEach(btn => {
    btn.onclick = () => {
        viewModal.style.display = "none";
        editModal.style.display = "none";
        currentNoteId = null;
    };
});

// FORMAT
function getSelection() {
    return {
        start: editor.selectionStart,
        end: editor.selectionEnd,
        value: editor.value,
    };
}

function replaceSelection(nextValue, nextStart, nextEnd) {
    editor.value = nextValue;
    editor.focus();
    editor.setSelectionRange(nextStart, nextEnd);
    updateToolbarState();
}

function wrapSelection(prefix, suffix = prefix, placeholder = "teks") {
    const { start, end, value } = getSelection();
    const selected = value.slice(start, end) || placeholder;
    const replacement = `${prefix}${selected}${suffix}`;
    const nextValue = value.slice(0, start) + replacement + value.slice(end);
    const selectedStart = start + prefix.length;
    const selectedEnd = selectedStart + selected.length;

    replaceSelection(nextValue, selectedStart, selectedEnd);
}

function selectedLineRange() {
    const { start, end, value } = getSelection();
    const lineStart = value.lastIndexOf("\n", start - 1) + 1;
    let lineEnd = value.indexOf("\n", end);

    if (lineEnd === -1) {
        lineEnd = value.length;
    }

    return { start, end, value, lineStart, lineEnd };
}

function mapSelectedLines(callback) {
    const { value, lineStart, lineEnd } = selectedLineRange();
    const selectedLines = value.slice(lineStart, lineEnd);
    const nextLines = selectedLines
        .split("\n")
        .map((line, index) => callback(line, index))
        .join("\n");
    const nextValue = value.slice(0, lineStart) + nextLines + value.slice(lineEnd);

    replaceSelection(nextValue, lineStart, lineStart + nextLines.length);
}

function clearLineMarkdown(line) {
    return line
        .replace(/^#{1,6}\s+/, "")
        .replace(/^(\s*)([-*+]\s+|\d+\.\s+)/, "$1");
}

function prefixLines(prefixer) {
    mapSelectedLines((line, index) => {
        if (!line.trim()) return line;
        return `${prefixer(index)}${clearLineMarkdown(line)}`;
    });
}

function applyMarkdownCommand(command) {
    if (["undo", "redo"].includes(command)) {
        editor.focus();
        document.execCommand(command);
        updateToolbarState();
        return;
    }

    if (command === "bold") {
        wrapSelection("**", "**", "teks tebal");
        return;
    }

    if (command === "italic") {
        wrapSelection("*", "*", "teks miring");
        return;
    }

    if (command === "underline") {
        wrapSelection("<u>", "</u>", "teks bergaris");
        return;
    }

    if (command === "insertUnorderedList") {
        prefixLines(() => "- ");
        return;
    }

    if (command === "insertOrderedList") {
        prefixLines((index) => `${index + 1}. `);
    }
}

function applyBlockFormat(value) {
    if (value === "H2") {
        prefixLines(() => "## ");
        return;
    }

    if (value === "H3") {
        prefixLines(() => "### ");
        return;
    }

    prefixLines(() => "");
}

function updateToolbarState() {
    toolbarButtons.forEach((button) => button.classList.remove("active"));
}

document.querySelector(".toolbar")?.addEventListener("click", (event) => {
    const button = event.target.closest(".toolbar-btn");
    if (!button) return;

    event.preventDefault();
    const command = button.dataset.command;
    if (command) {
        applyMarkdownCommand(command);
    }
});

blockFormat?.addEventListener("change", () => {
    applyBlockFormat(blockFormat.value);
});

editor?.addEventListener("keyup", updateToolbarState);
editor?.addEventListener("mouseup", updateToolbarState);
editor?.addEventListener("focus", updateToolbarState);

editTitle?.addEventListener("keydown", (event) => {
    if (event.key === "Enter") {
        event.preventDefault();
        editor.focus();
    }
});

// SAVE NOTE
saveNoteButton?.addEventListener('click', async () => {
    const title = editTitle.value.trim();
    const content = editor.value.trim();

    if (title === "" || content === "") {
        alert("Judul dan isi catatan wajib diisi!");
        return;
    }

    try {
        if (currentNoteId) {
            await window.NeuroomApi.request(`/api/v1/notes/${currentNoteId}`, {
                method: 'PATCH',
                data: {
                    title,
                    content,
                },
            });
            alert('Catatan berhasil diperbarui.');
        } else {
            await window.NeuroomApi.request('/api/v1/notes', {
                method: 'POST',
                data: {
                    title,
                    content,
                },
            });
            alert('Catatan berhasil dibuat.');
        }

        editModal.style.display = "none";
        currentNoteId = null;
        await loadNotes();
    } catch (error) {
        console.error('Simpan catatan gagal:', error);
        const message = error.payload?.errors
            ? Object.values(error.payload.errors).flat()[0]
            : error.message;
        alert(message || 'Gagal menyimpan catatan.');
    }
});
