const params = new URLSearchParams(window.location.search);
const quizId = params.get("id");

let questions = [];
let index = 0;
let answers = [];
let selected = null;
let time = 30;

const qEl = document.getElementById("question");
const optEl = document.getElementById("options");
const nextBtn = document.getElementById("nextBtn");
const timerEl = document.getElementById("timer");
const progressFill = document.getElementById("progressFill");

let interval;

async function init() {
    const res = await fetch(`/api/quizzes/${quizId}`);
    const data = await res.json();

    questions = data.questions;
    document.getElementById("quiz-title").innerText = data.title;

    loadQuestion();
}

function startTimer() {
    clearInterval(interval);
    time = 30;

    interval = setInterval(() => {
        time--;
        timerEl.innerText = `00:${time < 10 ? '0' : ''}${time}`;

        if (time <= 0) {
            clearInterval(interval);
            nextBtn.click();
        }
    }, 1000);
}

function loadQuestion() {
    selected = null;
    startTimer();

    let q = questions[index];
    qEl.innerText = q.question;
    optEl.innerHTML = "";

    progressFill.style.width = (index / questions.length) * 100 + "%";

    q.options.forEach(opt => {
        let div = document.createElement("div");
        div.className = "option";
        div.innerText = opt;

        div.onclick = () => {
            if (selected) return;
            selected = opt;

            document.querySelectorAll(".option").forEach(e => e.classList.add("disabled"));
            div.classList.add("selected");

            clearInterval(interval);
        };

        optEl.appendChild(div);
    });
}

nextBtn.onclick = async () => {
    if (!selected && time > 0) return alert("Pilih dulu!");

    answers.push({
        question_id: questions[index].id,
        answer: selected
    });

    index++;

    if (index < questions.length) {
        loadQuestion();
    } else {
        await fetch("/api/quizzes/submit", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                quiz_id: quizId,
                answers
            })
        });

        window.location.href = document.referrer;
    }
};

init();