const viewModal = document.getElementById("viewModal");
const editModal = document.getElementById("editModal");
const viewTitle = document.getElementById("viewTitle");
const viewContent = document.getElementById("viewContent");
const editTitle = document.getElementById("editTitle");
const editor = document.getElementById("editor");
const saveNoteButton = document.getElementById("saveNote");

let currentNoteId = null;

// SEARCH
const searchInput = document.getElementById("searchInput");
searchInput?.addEventListener("keyup", function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll(".note-card").forEach(card => {
        const title = card.dataset.title;
        card.style.display = title.includes(keyword) ? "flex" : "none";
    });
});

// VIEW / EDIT / DELETE (delegated)
document.body.addEventListener("click", (event) => {
    const viewBtn = event.target.closest(".open-view");
    if (viewBtn) {
        viewModal.style.display = "flex";
        viewTitle.innerText = viewBtn.dataset.title;
        viewContent.innerHTML = viewBtn.dataset.content;
        return;
    }

    const editBtn = event.target.closest(".open-edit");
    if (editBtn) {
        currentNoteId = editBtn.dataset.id || null;
        editModal.style.display = "flex";
        editTitle.value = editBtn.dataset.title;
        editor.innerHTML = editBtn.dataset.content;
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
    editor.innerHTML = "";
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
function formatText(cmd) {
    document.execCommand(cmd, false, null);
}

// SAVE NOTE
saveNoteButton?.addEventListener('click', async () => {
    const title = editTitle.value.trim();
    const content = editor.innerHTML.trim();

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