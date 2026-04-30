const viewModal = document.getElementById("viewModal");
const editModal = document.getElementById("editModal");

// SEARCH
document.getElementById("searchInput").addEventListener("keyup", function () {
    const keyword = this.value.toLowerCase();

    document.querySelectorAll(".note-card").forEach(card => {
        const title = card.dataset.title;
        card.style.display = title.includes(keyword) ? "flex" : "none";
    });
});

// VIEW
document.querySelectorAll(".open-view").forEach(btn => {
    btn.onclick = () => {
        viewModal.style.display = "flex";
        viewTitle.innerText = btn.dataset.title;
        viewContent.innerHTML = btn.dataset.content;
    };
});

// EDIT
document.querySelectorAll(".open-edit").forEach(btn => {
    btn.onclick = () => {
        editModal.style.display = "flex";
        editTitle.value = btn.dataset.title;
        editor.innerHTML = btn.dataset.content;
    };
});

// ADD
document.getElementById("addNote").onclick = () => {
    editModal.style.display = "flex";
    editTitle.value = "";
    editor.innerHTML = "";
};

// CLOSE
document.querySelectorAll(".close").forEach(btn => {
    btn.onclick = () => {
        viewModal.style.display = "none";
        editModal.style.display = "none";
    };
});

// DELETE (backend nanti handle)
document.querySelectorAll(".btn-delete").forEach(btn => {
    btn.onclick = () => {
        if (confirm("Hapus catatan?")) {
            console.log("DELETE ID:", btn.dataset.id);
            // fetch('/catatan/' + id, { method: 'DELETE' })
        }
    };
});

// FORMAT
function formatText(cmd) {
    document.execCommand(cmd, false, null);
}

// SAVE NOTE (frontend only)
document.getElementById("saveNote").onclick = () => {

    const title = editTitle.value.trim();
    const content = editor.innerHTML.trim();

    if (title === "" || content === "") {
        alert("Judul dan isi catatan wajib diisi!");
        return;
    }

    alert("Catatan berhasil dibuat!");

    console.log({
        title: title,
        content: content
    });

    // nanti backend fetch disini

    editModal.style.display = "none";
};