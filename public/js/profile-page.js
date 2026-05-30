const profileImage = document.getElementById('profile-image');
const profileDisplayName = document.getElementById('profile-display-name');
const profileUsername = document.getElementById('profile-username');
const profileEmail = document.getElementById('profile-email');
const profileForm = document.getElementById('profile-form');
const profileFeedback = document.getElementById('profile-feedback');
const logoutProfileButton = document.getElementById('logout-button');
const profileFormDisplayName = document.getElementById('profile-form-display-name');
const profileFormEmail = document.getElementById('profile-form-email');

// ===== FEEDBACK =====
function setProfileFeedback(message, isError = false) {
  profileFeedback.textContent = message || '';
  profileFeedback.style.display = message ? 'block' : 'none';
  profileFeedback.style.background = isError ? '#fee2e2' : '#dcfce7';
  profileFeedback.style.color = isError ? '#991b1b' : '#166534';
}

// ===== APPLY DATA =====
function applyProfile(user) {
  profileImage.src = user.profile_picture_url || 'https://i.pravatar.cc/150';
  profileDisplayName.textContent = user.display_name || 'No Name';
  profileUsername.textContent = user.username || '-';
  profileEmail.textContent = user.email || '-';
  profileFormDisplayName.value = user.display_name || '';
  profileFormEmail.value = user.email || '';
}

// ===== LOAD PROFILE =====
async function loadProfile() {
  try {
    const response = await window.NeuroomApi.request('/api/v1/auth/me');
    applyProfile(response.data?.user || {});
  } catch (error) {
    setProfileFeedback('Gagal memuat profil.', true);
  }
}

// ===== SUBMIT PROFILE =====
profileForm?.addEventListener('submit', async (event) => {
  event.preventDefault();
  setProfileFeedback('');

  try {
    const formData = new FormData(profileForm);
    const response = await window.NeuroomApi.request('/api/v1/auth/me', {
      method: 'PATCH',
      data: formData,
    });

    applyProfile(response.data?.user || {});
    setProfileFeedback(response.message || 'Profil berhasil diupdate.');
    closeModal();
  } catch (error) {
    const message = error.payload?.errors
      ? Object.values(error.payload.errors).flat()[0]
      : error.message;
    setProfileFeedback(message || 'Gagal memperbarui profil.', true);
  }
});

// ===== LOGOUT =====
logoutProfileButton?.addEventListener('click', async () => {
  try {
    const response = await window.NeuroomApi.request('/api/v1/auth/logout', {
      method: 'POST',
    });
    window.location.href = response.meta?.redirect_to || '/';
  } catch (error) {
    window.location.href = '/';
  }
});

// ===== INIT =====
loadProfile();
