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
        
        // Komentar: Verify user is admin
        if (user.role !== 'admin') {
          window.location.href = '/my';
          return;
        }
        
        var userName = document.getElementById('user-name');
        var welcomeText = document.getElementById('welcome-text');
        
        if (userName) {
          userName.textContent = user.nama_lengkap || 'Admin';
        }
        if (welcomeText) {
          welcomeText.textContent = 'Halo ' + (user.nama_lengkap || 'Admin') + ' 👋';
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
      // Komentar: Update statistik dengan data dari backend
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
          var incomeEl = document.getElementById('stat-income');
          if (incomeEl) {
            incomeEl.textContent = 'Rp' + (data.totalIncome || 0).toLocaleString();
          }
        }
      }
    })
    .catch(function(err) {
      console.error('Dashboard fetch error:', err);
    });
  }

  // Komentar: Inisialisasi sidebar toggle
  function initSidebar() {
    var sidebar = document.getElementById('sidebar');
    var toggleBtn = document.getElementById('toggleSidebar');
    
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('expanded');
      });
    }
  }

  // Logout function
  function initLogout() {
    var logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
      logoutBtn.addEventListener('click', function() {
        if (confirm('Yakin ingin logout?')) {
          window.location.href = '/logout';
        }
      });
    }
  }

  // Initialize when DOM is ready
  function init() {
    fetchUserData();
    fetchDashboardStats();
    initSidebar();
    initLogout();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();