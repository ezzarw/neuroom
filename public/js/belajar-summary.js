const dropArea = document.getElementById('dropArea');
const fileInput = document.getElementById('fileInput');
const fileText = document.getElementById('fileText');
const form = document.getElementById('summaryForm');
const loadingBox = document.getElementById('loadingBox');
const summaryError = document.getElementById('summary-error');
const summaryResult = document.getElementById('summary-result');
const summaryStatus = document.getElementById('summary-status');
const summaryMessage = document.getElementById('summary-message');
const summaryOutput = document.getElementById('summary-output');
const cancelBtn = document.getElementById('cancelSummaryBtn');
const saveBtn = document.getElementById('saveSummaryBtn');

// ===== ERROR BOX =====
function setSummaryError(message) {
  summaryError.textContent = message;
  summaryError.style.display = message ? 'block' : 'none';
}

// ===== RENDER HASIL =====
function renderSummary(output) {
  summaryOutput.innerHTML = '';

  if (!output || output.trim() === '') {
    summaryOutput.textContent = 'Tidak ada poin ringkasan.';
    return;
  }

  summaryOutput.innerHTML = marked.parse(output);
}

// klik buka file
dropArea?.addEventListener('click', () => fileInput.click());

// drag effect
dropArea?.addEventListener('dragover', (event) => {
  event.preventDefault();
  dropArea.classList.add('dragover');
});

dropArea?.addEventListener('dragleave', () => {
  dropArea.classList.remove('dragover');
});

// drop file
dropArea?.addEventListener('drop', (event) => {
  event.preventDefault();
  dropArea.classList.remove('dragover');

  fileInput.files = event.dataTransfer.files;
  fileText.innerText = event.dataTransfer.files[0]?.name || 'Klik atau drag file ke sini';
});

// change file
fileInput?.addEventListener('change', () => {
  fileText.innerText = fileInput.files[0]?.name || 'Klik atau drag file ke sini';
});

// loading state + submit summary
form?.addEventListener('submit', async (event) => {
  event.preventDefault();
  setSummaryError('');
  loadingBox.style.display = 'block';
  summaryResult.style.display = 'none';

  try {
    const formData = new FormData(form);
    const response = await window.NeuroomApi.request('/api/v1/summary', {
      method: 'POST',
      data: formData,
    });

    const summary = response.data || {};
    summaryStatus.textContent = summary.status || 'success';
    summaryMessage.textContent = response.reason || response.message || 'Ringkasan selesai.';
    renderSummary(summary.output || []);
    summaryResult.style.display = 'block';
  } catch (error) {
    const message = error.payload?.errors
      ? Object.values(error.payload.errors).flat()[0]
      : error.message;
    setSummaryError(message || 'Gagal membuat ringkasan.');
  } finally {
    loadingBox.style.display = 'none';
  }
});



// 🔴 BATAL
cancelBtn?.addEventListener("click", () => {
    summaryResult.style.display = "none";
    summaryOutput.innerHTML = "";
});

// 🟢 SIMPAN KE CATATAN
saveBtn?.addEventListener("click", async () => {
    const content = summaryOutput.innerText;

    if (!content) {
        alert("Tidak ada isi untuk disimpan");
        return;
    }

    try {
        const response = await window.NeuroomApi.request('/api/v1/summary-to-notes', {
            method: 'POST'
        });

        alert(response.reason || response.message || 'Berhasil disimpan ke catatan');
        window.location.href = '/catatan';
    } catch (error) {
        const message = error.payload?.message || error.payload?.reason || error.message;
        console.error(error);
        alert(message || 'Terjadi kesalahan server');
    }
});