// /public/js/dashboard_user.js
// Dashboard user script with session handling

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

  // Fetch user data and update UI
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
        var welcomeText = document.getElementById('welcome-text');
        var userName = document.getElementById('user-name');
        
        if (welcomeText) {
          welcomeText.textContent = 'Halo ' + (user.nama_lengkap || 'User') + ' 👋';
        }
        if (userName) {
          userName.textContent = user.nama_lengkap || 'Akun Saya';
        }
      }
    })
    .catch(function(error) {
      console.error('Error fetching user data:', error);
    });
  }

  // Fetch and display penitipan
  function fetchPenitipan() {
    fetch('/api/penitipan', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var tbody = document.getElementById('penitipan-tbody');
      if (!tbody) return;
      // Cek jika ada data penitipan
      if (data.success && data.penitipan && data.penitipan.length > 0) {
        tbody.innerHTML = '';
        data.penitipan.forEach(function(p) {
          var tr = document.createElement('tr');
          // Kolom Nama Pet
          var tdPet = document.createElement('td');
          tdPet.textContent = p.nama_pet || 'Unknown';
          // Kolom Tanggal Penitipan
          var tdCheckin = document.createElement('td');
          tdCheckin.textContent = formatDate(p.tgl_checkin);
          // Kolom Tanggal Pengambilan
          var tdCheckout = document.createElement('td');
          tdCheckout.textContent = formatDate(p.tgl_checkout);
          // Kolom Status
          var tdStatus = document.createElement('td');
          var statusBadge = document.createElement('span');
          statusBadge.className = 'status-badge status-' + (p.status_penitipan || 'aktif');
          statusBadge.textContent = p.status_penitipan || 'Aktif';
          tdStatus.appendChild(statusBadge);

          // Kolom Aksi (Edit & Hapus)
          var tdAksi = document.createElement('td');
          tdAksi.style.textAlign = 'center';

          // Tombol Edit (biru)
          var editBtn = document.createElement('button');
          editBtn.textContent = 'Edit';
          editBtn.className = 'edit-btn';
          editBtn.style.background = '#2196f3'; // biru
          editBtn.style.color = 'white';
          editBtn.style.border = 'none';
          editBtn.style.borderRadius = '5px';
          editBtn.style.padding = '6px 12px';
          editBtn.style.marginRight = '8px';
          editBtn.style.cursor = 'pointer';
          // Saat klik edit, arahkan ke halaman input penitipan dengan id penitipan
          editBtn.addEventListener('click', function() {
            // arahkan ke halaman edit penitipan dengan id
            window.location.href = '/titip?id=' + encodeURIComponent(p.id_penitipan);
          });

          // Tombol Hapus (merah)
          var deleteBtn = document.createElement('button');
          deleteBtn.textContent = 'Hapus';
          deleteBtn.className = 'delete-btn';
          deleteBtn.style.background = '#f44336'; // merah
          deleteBtn.style.color = 'white';
          deleteBtn.style.border = 'none';
          deleteBtn.style.borderRadius = '5px';
          deleteBtn.style.padding = '6px 12px';
          deleteBtn.style.cursor = 'pointer';
          // Saat klik hapus, panggil API hapus penitipan
          deleteBtn.addEventListener('click', function() {
            if (confirm('Yakin ingin menghapus data penitipan ini?')) {
              //hapus data penitipan di database
              fetch('/api/penitipan/delete/' + encodeURIComponent(p.id_penitipan), {
                method: 'POST',
                headers: {
                  'Authorization': 'Bearer ' + sessionToken,
                  'Content-Type': 'application/json'
                }
              })
              .then(function(response) { return response.json(); })
              .then(function(res) {
                if (res.success) {
                  fetchPenitipan(); // refresh tabel
                } else {
                  alert('Gagal menghapus data penitipan.');
                }
              })
              .catch(function(error) {
                alert('Terjadi kesalahan saat menghapus data penitipan.');
              });
            }
          });

          tdAksi.appendChild(editBtn);
          tdAksi.appendChild(deleteBtn);

          tr.appendChild(tdPet);
          tr.appendChild(tdCheckin);
          tr.appendChild(tdCheckout);
          tr.appendChild(tdStatus);
          tr.appendChild(tdAksi); // tambahkan kolom aksi

          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="no-active">Tidak ada penitipan</td></tr>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching penitipan:', error);
      var tbody = document.getElementById('penitipan-tbody');
      if (tbody) {
        tbody.innerHTML = '<tr><td colspan="5" class="no-active">Gagal memuat data</td></tr>';
      }
    });
  }

  // Fetch and display pets
  function fetchPets() {
    fetch('/api/hewan', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var container = document.getElementById('pets-container');
      if (!container) return;
      
      if (data.success && data.pets && data.pets.length > 0) {
        container.innerHTML = '';
        
        var petsGrid = document.createElement('div');
        petsGrid.className = 'pets-grid';
        
        data.pets.forEach(function(pet) {
          var petCard = document.createElement('div');
          petCard.className = 'pet-card';
          
          petCard.innerHTML = 
            '<div class="pet-icon">🐾</div>' +
            '<div class="pet-info">' +
              '<strong>' + escapeHtml(pet.nama_pet) + '</strong>' +
              '<span class="pet-type">' + escapeHtml(pet.jenis_pet || '') + (pet.ras ? ' - ' + escapeHtml(pet.ras) : '') + '</span>' +
            '</div>';
          
          petsGrid.appendChild(petCard);
        });
        
        container.appendChild(petsGrid);
      } else {
        container.innerHTML = '<p class="muted">Belum ada pet terdaftar. <a href="/titip">Tambah pet baru</a></p>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching pets:', error);
      var container = document.getElementById('pets-container');
      if (container) {
        container.innerHTML = '<p class="muted">Gagal memuat data pet</p>';
      }
    });
  }

  function formatDate(dateStr) {
    if (!dateStr) return '-';
    var date = new Date(dateStr);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"']/g, function(s) {
      return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
    });
  }

  // Initialize sidebar toggle
  function initSidebar() {
    var sidebar = document.getElementById('sidebar');
    var toggleBtn = document.getElementById('toggleSidebar');
    
    if (toggleBtn && sidebar) {
      toggleBtn.addEventListener('click', function() {
        sidebar.classList.toggle('expanded');
      });
    }
  }

  // Initialize logout button
  function initLogout() {
    var logoutBtn = document.getElementById('logoutBtn');
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

  // Initialize when DOM is ready
  function init() {
    fetchUserData();
    fetchPenitipan();
    fetchPets();
    initSidebar();
    initLogout();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();

