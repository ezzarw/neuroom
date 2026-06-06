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

// ===== SUCCESS ALERT =====
let currentRedirectUrl = null;

function showSuccessAlert(title, message, redirectUrl, autoClose = true) {
  const alertOverlay = document.getElementById('success-alert');
  const titleEl = document.getElementById('success-title');
  const messageEl = document.getElementById('success-message');
  const btnEl = document.getElementById('success-btn');
  
  titleEl.textContent = title.replace(/!$/, '') || 'Berhasil';
  messageEl.textContent = message || 'Operasi berhasil dilakukan.';
  currentRedirectUrl = redirectUrl;
  
  alertOverlay.classList.add('show');
  
  // Auto-close after 1 second for login/register
  if (autoClose) {
    setTimeout(() => {
      alertOverlay.classList.remove('show');
      if (currentRedirectUrl) {
        window.location.href = currentRedirectUrl;
      }
    }, 1000);
  }
  
  // Manual close button (if user clicks)
  btnEl.onclick = () => {
    alertOverlay.classList.remove('show');
    if (currentRedirectUrl) {
      window.location.href = currentRedirectUrl;
    }
  };
}

// ===== AUTH SUBMIT =====
async function submitAuth({ form, endpoint, button, feedback }) {
  setFeedback(feedback, '');
  setPageFeedback('');
  button.disabled = true;

  try {
    const data = Object.fromEntries(new FormData(form).entries());
    console.log('Sending data:', data); // Debug
    const response = await window.NeuroomApi.request(endpoint, {
      method: 'POST',
      data,
    });

    const message = response.reason || response.message || 'Berhasil.';
    const title = endpoint.includes('/login') ? 'Login Berhasil' : 'Daftar Berhasil';
    const redirectUrl = response.meta?.redirect_to || (endpoint.startsWith('/api/v1/auth/') ? '/utama' : null);
    
    showSuccessAlert(title, message, redirectUrl);
    return;
  } catch (error) {
    if (error.status === 409) {
      const redirectUrl = error.payload?.meta?.redirect_to || '/utama';
      window.location.href = redirectUrl;
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

// ===== PASSWORD VISIBILITY TOGGLE =====
document.querySelectorAll('.password-toggle').forEach((btn) => {
  btn.addEventListener('click', (event) => {
    event.preventDefault();
    const input = btn.previousElementSibling;
    const eyeIcon = btn.querySelector('.eye-icon');
    const eyeClosedIcon = btn.querySelector('.eye-closed-icon');
    const isPassword = input.type === 'password';
    
    input.type = isPassword ? 'text' : 'password';
    
    if (isPassword) {
      eyeIcon.style.display = 'none';
      eyeClosedIcon.style.display = 'block';
    } else {
      eyeIcon.style.display = 'block';
      eyeClosedIcon.style.display = 'none';
    }
  });
});
