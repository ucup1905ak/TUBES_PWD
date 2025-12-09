// /public/js/detail_user.js
// Admin - User Detail Page script

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

  // Get user ID from URL parameter
  const urlParams = new URLSearchParams(window.location.search);
  const userId = urlParams.get('id');

  console.log('URL search params:', window.location.search);
  console.log('User ID from URL:', userId);

  if (!userId) {
    alert('User ID tidak ditemukan dalam URL');
    window.location.href = '/admin/users';
    return;
  }

  // Verify admin role
  function verifyAdmin() {
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
        
        if (user.role !== 'admin') {
          window.location.href = '/my';
          return;
        }
        
        var adminName = document.getElementById('admin-name');
        var adminAvatar = document.getElementById('admin-avatar');
        
        if (adminName) {
          adminName.textContent = user.nama_lengkap || 'Admin';
        }
        if (adminAvatar && user.foto_profil) {
          adminAvatar.src = user.foto_profil;
        }
      }
    })
    .catch(function(error) {
      console.error('Error verifying admin:', error);
    });
  }

  // Fetch user detail
  function fetchUserDetail() {
    fetch('/api/user/' + encodeURIComponent(userId), {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { 
      if (!response.ok) {
        throw new Error('HTTP error! status: ' + response.status);
      }
      return response.json(); 
    })
    .then(function(data) {
      console.log('User detail response:', data);
      
      if (data.success && data.user) {
        var user = data.user;
        
        document.getElementById('user-id').textContent = user.id_user || '-';
        document.getElementById('user-nama').textContent = user.nama_lengkap || '-';
        document.getElementById('user-email').textContent = user.email || '-';
        document.getElementById('user-telp').textContent = user.no_telp || '-';
        document.getElementById('user-alamat').textContent = user.alamat || '-';
        document.getElementById('user-role').textContent = user.role === 'admin' ? 'Admin' : 'User';
      } else {
        console.error('User not found in response:', data);
        alert('User tidak ditemukan: ' + (data.error || 'Unknown error'));
        window.location.href = '/admin/users';
      }
    })
    .catch(function(error) {
      console.error('Error fetching user detail:', error);
      alert('Gagal memuat data user: ' + error.message);
      window.location.href = '/admin/users';
    });
  }

  // Fetch user's pets
  function fetchUserPets() {
    fetch('/api/admin/user/' + encodeURIComponent(userId) + '/pets', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var container = document.getElementById('pets-container');
      
      if (data.success && data.pets && data.pets.length > 0) {
        container.innerHTML = '';
        
        data.pets.forEach(function(pet) {
          var petCard = document.createElement('div');
          petCard.className = 'pet-card';
          
          var petName = document.createElement('h3');
          petName.textContent = pet.nama_pet;
          petCard.appendChild(petName);
          
          var petInfo = document.createElement('p');
          petInfo.innerHTML = '<strong>Jenis:</strong> ' + (pet.jenis_pet || '-') + 
                             ' | <strong>Ras:</strong> ' + (pet.ras || '-') + 
                             ' | <strong>Umur:</strong> ' + (pet.umur ? pet.umur + ' tahun' : '-');
          petCard.appendChild(petInfo);
          
          if (pet.alergi) {
            var petAlergi = document.createElement('p');
            petAlergi.innerHTML = '<strong>Alergi:</strong> ' + pet.alergi;
            petCard.appendChild(petAlergi);
          }
          
          if (pet.catatan_medis) {
            var petCatatan = document.createElement('p');
            petCatatan.innerHTML = '<strong>Catatan Medis:</strong> ' + pet.catatan_medis;
            petCard.appendChild(petCatatan);
          }
          
          container.appendChild(petCard);
        });
      } else {
        container.innerHTML = '<p class="muted">User ini belum memiliki hewan peliharaan terdaftar.</p>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching pets:', error);
      var container = document.getElementById('pets-container');
      container.innerHTML = '<p class="muted">Gagal memuat data hewan peliharaan.</p>';
    });
  }

  // Fetch user's penitipan history (including cancelled)
  function fetchPenitipanHistory() {
    fetch('/api/admin/user/' + encodeURIComponent(userId) + '/penitipan', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var tbody = document.getElementById('penitipan-tbody');
      
      if (data.success && data.penitipan && data.penitipan.length > 0) {
        tbody.innerHTML = '';
        
        data.penitipan.forEach(function(p) {
          var tr = document.createElement('tr');
          
          var tdId = document.createElement('td');
          tdId.textContent = p.id_penitipan;
          
          var tdPet = document.createElement('td');
          tdPet.textContent = p.nama_pet || '-';
          
          var tdPaket = document.createElement('td');
          tdPaket.textContent = p.nama_paket || '-';
          
          var tdCheckin = document.createElement('td');
          tdCheckin.textContent = p.tgl_checkin || '-';
          
          var tdCheckout = document.createElement('td');
          tdCheckout.textContent = p.tgl_checkout || '-';
          
          var tdDurasi = document.createElement('td');
          tdDurasi.textContent = p.durasi ? p.durasi + ' hari' : '-';
          
          var tdBiaya = document.createElement('td');
          tdBiaya.textContent = p.total_biaya ? 'Rp ' + p.total_biaya.toLocaleString('id-ID') : '-';
          
          var tdStatus = document.createElement('td');
          var statusBadge = document.createElement('span');
          statusBadge.className = 'status-badge';
          
          if (p.status === 'cancelled' || p.deleted_at) {
            statusBadge.textContent = 'Dibatalkan';
            statusBadge.className += ' cancelled';
          } else if (p.status === 'completed') {
            statusBadge.textContent = 'Selesai';
            statusBadge.className += ' completed';
          } else {
            statusBadge.textContent = p.status_penitipan || 'Aktif';
            statusBadge.className += ' active';
          }
          
          tdStatus.appendChild(statusBadge);
          
          tr.appendChild(tdId);
          tr.appendChild(tdPet);
          tr.appendChild(tdPaket);
          tr.appendChild(tdCheckin);
          tr.appendChild(tdCheckout);
          tr.appendChild(tdDurasi);
          tr.appendChild(tdBiaya);
          tr.appendChild(tdStatus);
          
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="8" class="no-data">User ini belum memiliki riwayat penitipan.</td></tr>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching penitipan:', error);
      var tbody = document.getElementById('penitipan-tbody');
      tbody.innerHTML = '<tr><td colspan="8" class="no-data">Gagal memuat data riwayat.</td></tr>';
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

  // Initialize
  function init() {
    verifyAdmin();
    fetchUserDetail();
    fetchUserPets();
    fetchPenitipanHistory();
    initLogout();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
