// /public/js/all_user.js
// Admin - All Users Page script

(function () {
  'use strict';

  // Komentar: Check if user is logged in
  const sessionToken = localStorage.getItem('session_token');
  const expiresAt = localStorage.getItem('session_expires_at');

  if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
    localStorage.removeItem('session_token');
    localStorage.removeItem('session_expires_at');
    window.location.href = '/login';
    return;
  }

  // Komentar: Fetch user data dan verify admin role
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
        var userAvatar = document.getElementById('user-avatar');
        
        if (userName) {
          userName.textContent = user.nama_lengkap || 'Admin';
        }
        if (userAvatar && user.foto_profil) {
          userAvatar.src = user.foto_profil;
        }
      }
    })
    .catch(function(error) {
      console.error('Error fetching user data:', error);
    });
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
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var tbody = document.getElementById('user-tbody');
      if (!tbody) return;

      if (data.success && data.users && data.users.length > 0) {
        tbody.innerHTML = '';
        
        data.users.forEach(function(user) {
          var tr = document.createElement('tr');
          
          var tdId = document.createElement('td');
          tdId.textContent = user.id_user || '-';
          
          var tdNama = document.createElement('td');
          tdNama.textContent = user.nama_lengkap || '-';
          
          var tdEmail = document.createElement('td');
          tdEmail.textContent = user.email || '-';
          
          var tdNoTelp = document.createElement('td');
          tdNoTelp.textContent = user.no_telp || '-';
          
          var tdRole = document.createElement('td');
          tdRole.textContent = user.role === 'admin' ? 'Admin' : 'User';
          
          // Komentar: Kolom aksi - tombol detail
          var tdAksi = document.createElement('td');
          var detailBtn = document.createElement('button');
          detailBtn.textContent = 'Detail';
          detailBtn.className = 'detail-btn';
          
          // Komentar: Saat klik detail, arahkan ke halaman detail user
          detailBtn.addEventListener('click', function() {
            window.location.href = '/admin/detail?id=' + encodeURIComponent(user.id_user);
          });
          
          tdAksi.appendChild(detailBtn);
          
          tr.appendChild(tdId);
          tr.appendChild(tdNama);
          tr.appendChild(tdEmail);
          tr.appendChild(tdNoTelp);
          tr.appendChild(tdRole);
          tr.appendChild(tdAksi);
          
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="6" class="no-data">Tidak ada data user</td></tr>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching users:', error);
      var tbody = document.getElementById('user-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="6" class="no-data">Gagal memuat data</td></tr>';
      }
    });
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
    fetchAllUsers();
    initLogout();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
