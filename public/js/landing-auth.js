const loginForm = document.getElementById('login-form');
const registerForm = document.getElementById('register-form');
const loginFeedback = document.getElementById('login-feedback');
const registerFeedback = document.getElementById('register-feedback');
const pageFeedback = document.getElementById('page-feedback');
const loginSubmit = document.getElementById('login-submit');
const registerSubmit = document.getElementById('register-submit');

// ===== FEEDBACK =====
function setFeedback(element, message) {
  if (!element) return;
  element.textContent = message;
  element.style.display = message ? 'block' : 'none';
}

function setPageFeedback(message, isError = false) {
  if (!pageFeedback) return;
  pageFeedback.textContent = message;
  pageFeedback.style.display = message ? 'block' : 'none';
  pageFeedback.style.background = isError ? '#fee2e2' : '#dcfce7';
  pageFeedback.style.color = isError ? '#991b1b' : '#166534';
}

// ===== AUTH SUBMIT =====
async function submitAuth({ form, endpoint, button, feedback }) {
  setFeedback(feedback, '');
  setPageFeedback('');
  button.disabled = true;

  try {
    const data = Object.fromEntries(new FormData(form).entries());
    const response = await window.NeuroomApi.request(endpoint, {
      method: 'POST',
      data,
    });

    setPageFeedback(response.message || 'Berhasil.');

    if (response.meta?.redirect_to) {
      window.location.href = response.meta.redirect_to;
    }
  } catch (error) {
    if (error.status === 409 && error.payload?.meta?.redirect_to) {
      window.location.href = error.payload.meta.redirect_to;
      return;
    }

    const message = error.payload?.errors
      ? Object.values(error.payload.errors).flat()[0]
      : error.message;
    setFeedback(feedback, message || 'Request gagal.');
  } finally {
    button.disabled = false;
  }
}

// ===== LOGIN =====
loginForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  submitAuth({
    form: loginForm,
    endpoint: '/api/v1/auth/login',
    button: loginSubmit,
    feedback: loginFeedback,
  });
});

// ===== REGISTER =====
registerForm?.addEventListener('submit', (event) => {
  event.preventDefault();
  submitAuth({
    form: registerForm,
    endpoint: '/api/v1/auth/register',
    button: registerSubmit,
    feedback: registerFeedback,
  });
});
