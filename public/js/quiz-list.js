const searchInput = document.getElementById("searchInput");
const quizList = document.getElementById("quizList");

// 🔥 DATA DUMMY
let allQuiz = [
    { id:1, title:"Matematika Dasar", total_soal:5, kategori:"umum", sudah_dikerjakan:false },
    { id:2, title:"Bahasa Indonesia", total_soal:5, kategori:"umum", sudah_dikerjakan:true },
    { id:3, title:"HTML Dasar", total_soal:5, kategori:"kejuruan", sudah_dikerjakan:false },
    { id:4, title:"Jaringan", total_soal:5, kategori:"kejuruan", sudah_dikerjakan:false }
];

// 🔥 FILTER SESUAI HALAMAN
let filteredQuiz = allQuiz.filter(q => q.kategori === kategori);

// 🔥 RENDER
function renderQuiz(data) {
    quizList.innerHTML = "";

    data.forEach(q => {
        const el = document.createElement("div");
        el.className = "quiz-item";

        el.innerHTML = `
            <h3>${q.title}</h3>
            <p>${q.total_soal} Soal</p>
            ${q.sudah_dikerjakan ? `<span class="status">✔ Sudah dikerjakan</span>` : ""}
        `;

        el.onclick = () => {
            window.location.href = `/kerjakan-quiz?id=${q.id}`;
        };

        quizList.appendChild(el);
    });
}

// 🔍 SEARCH (DALAM KATEGORI)
searchInput.addEventListener("input", () => {
    const val = searchInput.value.toLowerCase();

    const result = filteredQuiz.filter(q =>
        q.title.toLowerCase().includes(val)
    );

    renderQuiz(result);
});

// INIT
renderQuiz(filteredQuiz);