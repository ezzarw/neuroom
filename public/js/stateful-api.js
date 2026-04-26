(function () {
  // ===== CSRF TOKEN =====
  function getCsrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  }

  // ===== REQUEST HELPER =====
  async function request(url, options = {}) {
    const headers = {
      Accept: 'application/json',
      ...(options.headers || {}),
    };

    const csrfToken = getCsrfToken();
    if (csrfToken) {
      headers['X-CSRF-TOKEN'] = csrfToken;
    }

    const config = {
      method: options.method || 'GET',
      headers,
      credentials: 'include',
    };

    if (options.data instanceof FormData) {
      config.body = options.data;
      delete config.headers['Content-Type'];
    } else if (options.data !== undefined) {
      config.headers['Content-Type'] = 'application/json';
      config.body = JSON.stringify(options.data);
    }

    const response = await fetch(url, config);
    const contentType = response.headers.get('content-type') || '';
    const isJson = contentType.includes('application/json');
    const payload = response.status === 204
      ? null
      : isJson
        ? await response.json()
        : await response.text();

    if (!response.ok) {
      const error = new Error(payload?.message || `Request gagal (${response.status})`);
      error.status = response.status;
      error.payload = payload;
      throw error;
    }

    return payload;
  }

  // ===== FORMAT TANGGAL =====
  function formatDate(value) {
    if (!value) {
      return '-';
    }

    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
      return value;
    }

    return date.toLocaleString('id-ID');
  }

  // ===== FORMAT DURASI =====
  function formatDuration(seconds) {
    const totalSeconds = Number(seconds || 0);
    const hours = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
    const minutes = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
    const secs = String(totalSeconds % 60).padStart(2, '0');
    return `${hours}:${minutes}:${secs}`;
  }

  window.NeuroomApi = {
    request,
    formatDate,
    formatDuration,
  };
})();
