import { checkSession, fetchUserData } from './shared/auth.js';
import { initSidebar, initLogout } from './shared/ui.js';

// /public/js/dashboard_admin.js
// Admin Dashboard script with session handling (ES module)

'use strict';

// Check if user is logged in
const sessionToken = checkSession();
if (!sessionToken) return;

// Fetch user data and verify admin role
function handleUserData(user) {
  // Verify user is admin
  if (user.role !== 'admin') {
    window.location.href = '/my';
    return;
  }

  const userName = document.getElementById('user-name');
  const welcomeText = document.getElementById('welcome-text');

  if (userName) {
    userName.textContent = user.nama_lengkap || 'Admin';
  }
  if (welcomeText) {
    welcomeText.textContent = 'Halo ' + (user.nama_lengkap || 'Admin') + ' 👋';
  }
}

// Fetch dashboard statistics
function fetchDashboardStats() {
  fetch('/api/admin/dashboard', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(res => res.json())
  .then(data => {
    // Update statistik dengan data dari backend
    if (data.success) {
      if (data.totalUsers !== undefined) {
        document.getElementById('stat-users').textContent = data.totalUsers;
      }
      if (data.totalPet !== undefined) {
        document.getElementById('stat-pet').textContent = data.totalPet;
      }
      if (data.totalPenitipan !== undefined) {
        document.getElementById('stat-penitipan').textContent = data.totalPenitipan;
      }
      if (data.totalIncome !== undefined) {
        const incomeEl = document.getElementById('stat-income');
        if (incomeEl) {
          incomeEl.textContent = 'Rp' + (data.totalIncome || 0).toLocaleString();
        }
      }
    }
  })
  .catch(err => {
    console.error('Dashboard fetch error:', err);
  });
}

// Initialize when DOM is ready
function init() {
  fetchUserData(sessionToken, handleUserData);
  fetchDashboardStats();
  initSidebar();
  initLogout();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}