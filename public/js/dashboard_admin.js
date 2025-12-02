// /public/js/dashboard_admin.js
// Admin Dashboard script with session handling

(function () {
  'use strict';

  // Check if user is logged in
  const sessionToken = localStorage.getItem('session_token');
  const expiresAt = localStorage.getItem('session_expires_at');

  if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
    localStorage.removeItem('session_token');
    localStorage.removeItem('session_expires_at');
    window.location.href = '/login';
    return;
  }

  // Fetch user data and verify admin role
  function fetchUserData() {
    fetch('/api/auth/me', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success && data.user) {
        var user = data.user;
        
        // Verify user is admin
        if (user.role !== 'admin') {
          window.location.href = '/my';
          return;
        }
        
        var userDisplay = document.getElementById('user-display');
        var welcomeHeading = document.getElementById('welcome-heading');
        
        if (userDisplay) {
          userDisplay.textContent = user.nama_lengkap || 'Admin';
        }
        if (welcomeHeading) {
          welcomeHeading.textContent = 'Halo ' + (user.nama_lengkap || 'Admin') + ' 👋';
        }
      }
    })
    .catch(function(error) {
      console.error('Error fetching user data:', error);
    });
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
    .then(function(res) { return res.json(); })
    .then(function(data) {
      if (data.totalUsers !== undefined) {
        document.getElementById('stat-users').textContent = data.totalUsers;
      }
      if (data.totalPet !== undefined) {
        document.getElementById('stat-penitipan-aktif').textContent = data.totalPet;
      }
      if (data.totalPenitipan !== undefined) {
        document.getElementById('stat-penitipan').textContent = data.totalPenitipan;
      }
      if (data.totalIncome !== undefined) {
        document.getElementById('stat-income').textContent = 'Rp' + data.totalIncome.toLocaleString();
      }
    })
    .catch(function(err) {
      console.error('Dashboard fetch error:', err);
    });
  }

  // Logout function
  function initLogout() {
    var logoutBtn = document.getElementById('btn-logout');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function() {
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
        window.location.href = '/logout';
      });
    }
  }

  // Initialize when DOM is ready
  function init() {
    fetchUserData();
    fetchDashboardStats();
    initLogout();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();