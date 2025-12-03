import { checkSession, fetchUserData } from './shared/auth.js';
import { initSidebar, initLogout } from './shared/ui.js';
import { createPenitipanRow } from './components/penitipan-row.js';

// /public/js/dashboard_user.js
// Dashboard user script with session handling (ES module)

'use strict';

// Check if user is logged in
const sessionToken = checkSession();
if (!sessionToken) return;

// Fetch user data and update UI
function handleUserData(user) {
  const welcomeText = document.getElementById('welcome-text');
  const userName = document.getElementById('user-name');

  if (welcomeText) {
    welcomeText.textContent = 'Halo ' + (user.nama_lengkap || 'User') + ' 👋';
  }
  if (userName) {
    userName.textContent = user.nama_lengkap || 'Akun Saya';
  }
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
  .then(response => response.json())
  .then(data => {
    const tbody = document.getElementById('penitipan-tbody');
    if (!tbody) return;
    // Cek jika ada data penitipan
    if (data.success && data.penitipan && data.penitipan.length > 0) {
      tbody.innerHTML = '';
      data.penitipan.forEach(p => {
        const row = createPenitipanRow(p);

        row.addEventListener('penitipan-edit', function(e) {
          const item = e.detail;
          window.location.href = '/titip?id=' + encodeURIComponent(item.id_penitipan);
        });

        row.addEventListener('penitipan-delete', function(e) {
          const item = e.detail;
          if (confirm('Yakin ingin menghapus data penitipan ini?')) {
            fetch('/api/penitipan/delete/' + encodeURIComponent(item.id_penitipan), {
              method: 'POST',
              headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
              }
            })
            .then(res => res.json())
            .then(res => {
              if (res.success) fetchPenitipan();
              else alert('Gagal menghapus data penitipan.');
            })
            .catch(() => alert('Terjadi kesalahan saat menghapus data penitipan.'));
          }
        });

        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="5" class="no-active">Tidak ada penitipan</td></tr>';
    }
  })
  .catch(error => {
    console.error('Error fetching penitipan:', error);
    const tbody = document.getElementById('penitipan-tbody');
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
  .then(response => response.json())
  .then(data => {
    const container = document.getElementById('pets-container');
    if (!container) return;

    if (data.success && data.pets && data.pets.length > 0) {
      container.innerHTML = '';

      const petsGrid = document.createElement('div');
      petsGrid.className = 'pets-grid';

      data.pets.forEach(pet => {
        const petCard = document.createElement('div');
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
  .catch(error => {
    console.error('Error fetching pets:', error);
    const container = document.getElementById('pets-container');
    if (container) {
      container.innerHTML = '<p class="muted">Gagal memuat data pet</p>';
    }
  });
}

function formatDate(dateStr) {
  if (!dateStr) return '-';
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}

function escapeHtml(str) {
  if (!str) return '';
  return String(str).replace(/[&<>"']/g, function(s) {
    return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[s];
  });
}

// Initialize when DOM is ready
function init() {
  fetchUserData(sessionToken, handleUserData);
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

