// /public/js/shared/ui.js
// Shared UI utilities

export function initSidebar() {
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('expanded');
    });
  }
}

export function initLogout() {
  const logoutBtn = document.getElementById('logoutBtn');
  if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
      if (confirm('Yakin ingin logout?')) {
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
        window.location.href = '/';
      }
    });
  }
}