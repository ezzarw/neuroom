// =============================
// POMODORO WEBSOCKET — Echo Subscriber
// =============================

(function () {
  if (!window.Echo || !window.NeuroomApi) return;

  const userId = document.querySelector('meta[name="user-id"]')?.content;
  if (!userId) {
    console.warn('Pomodoro Echo: meta user-id tidak ditemukan.');
    return;
  }

  const channel = Echo.private('user.' + userId + '.pomodoro');

  channel.listen('.pomodoro.state.changed', (payload) => {
    console.log('[Pomodoro WS]', payload);

    // Update global state
    if (window.__pomodoroState) {
      window.__pomodoroState(payload);
    }

    // Auto-refresh history kalo finished/stopped
    if (payload.status === 'finished' || payload.status === 'stopped') {
      if (window.loadHistory) {
        setTimeout(window.loadHistory, 500);
      }
    }
  });

  // Expose unlisten for cleanup
  window.__pomodoroEchoCleanup = () => {
    channel.stopListening('.pomodoro.state.changed');
  };
})();
