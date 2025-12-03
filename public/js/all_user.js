import { checkSession, fetchUserData } from './shared/auth.js';
import { initSidebar, initLogout } from './shared/ui.js';
import { createUserRow } from './components/user-row.js';

// /public/js/all_user.js
// Admin - All Users Page script (ES module)

'use strict';

// Komentar: Check if user is logged in
const sessionToken = checkSession();
if (!sessionToken) return;

// Komentar: Fetch user data dan verify admin role
function handleUserData(user) {
  if (user.role !== 'admin') {
    window.location.href = '/my';
    return;
  }
  const userName = document.getElementById('user-name');
  if (userName) {
    userName.textContent = user.nama_lengkap || 'Admin';
  }
}

// Komentar: Fetch dan display semua users
function fetchAllUsers() {
  fetch('/api/admin/users', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    const tbody = document.getElementById('user-tbody');
    if (!tbody) return;

    if (data.success && data.users && data.users.length > 0) {
      tbody.innerHTML = '';
      data.users.forEach(user => {
        const row = createUserRow(user);

        row.addEventListener('user-detail', function(e) {
          const item = e.detail;
          window.location.href = '/admin/detail?id=' + encodeURIComponent(item.id_user);
        });

        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="6" class="no-data">Tidak ada data user</td></tr>';
    }
  })
  .catch(error => {
    console.error('Error fetching users:', error);
    const tbody = document.getElementById('user-tbody');
    if (tbody) {
      tbody.innerHTML = '<tr><td colspan="6" class="no-data">Gagal memuat data</td></tr>';
    }
  });
}

// Initialize when DOM is ready
function init() {
  fetchUserData(sessionToken, handleUserData);
  fetchAllUsers();
  initSidebar();
  initLogout();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
