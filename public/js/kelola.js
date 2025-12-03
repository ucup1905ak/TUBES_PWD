// /public/js/kelola.js
// Admin - Kelola Layanan & Paket Page script

import { createLayananRow } from './components/layanan-row.js';
import { createPaketRow } from './components/paket-row.js';

// /public/js/kelola.js
// Admin - Kelola Layanan & Paket Page script (ES module)

 'use strict';

// Komentar: Check if user is logged in
const sessionToken = localStorage.getItem('session_token');
const expiresAt = localStorage.getItem('session_expires_at');

if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
  localStorage.removeItem('session_token');
  localStorage.removeItem('session_expires_at');
  window.location.href = '/login';
}

// Komentar: State untuk track edit mode
let editLayananId = null;
let editPaketId = null;

// Komentar: Fetch user data dan verify admin role
function fetchUserData() {
  fetch('/api/auth/me', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success && data.user) {
      const user = data.user;
      if (user.role !== 'admin') {
        window.location.href = '/my';
        return;
      }
      const userName = document.getElementById('user-name');
      if (userName) userName.textContent = user.nama_lengkap || 'Admin';
    }
  })
  .catch(error => console.error('Error fetching user data:', error));
}

// ==================== LAYANAN ====================

function fetchLayanan() {
  fetch('/api/admin/layanan', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    const tbody = document.getElementById('layanan-tbody');
    if (!tbody) return;

    if (data.success && data.layanan && data.layanan.length > 0) {
      tbody.innerHTML = '';
      data.layanan.forEach(layanan => {
        const row = createLayananRow(layanan);

        row.addEventListener('layanan-edit', function(e) {
          const item = e.detail;
          editLayananId = item.id_layanan;
          document.getElementById('formLayananTitle').textContent = 'Edit Layanan';
          document.getElementById('inputNamaLayanan').value = item.nama_layanan || '';
          document.getElementById('inputDeskripsiLayanan').value = item.deskripsi || '';
          document.getElementById('inputHargaLayanan').value = item.harga || '';
          document.getElementById('formLayanan').style.display = 'block';
        });

        row.addEventListener('layanan-delete', function(e) {
          const item = e.detail;
          if (confirm('Yakin ingin menghapus layanan ini?')) {
            fetch('/api/admin/layanan/' + encodeURIComponent(item.id_layanan), {
              method: 'DELETE',
              headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
              }
            })
            .then(res => res.json())
            .then(res => {
              if (res.success) fetchLayanan();
              else alert('Gagal menghapus layanan');
            })
            .catch(() => alert('Terjadi kesalahan saat menghapus'));
          }
        });

        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="5" class="no-data">Tidak ada data layanan</td></tr>';
    }
  })
  .catch(error => console.error('Error fetching layanan:', error));
}

// ==================== PAKET ====================

function fetchPaket() {
  fetch('/api/admin/paket', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
  .then(response => response.json())
  .then(data => {
    const tbody = document.getElementById('paket-tbody');
    if (!tbody) return;

    if (data.success && data.paket && data.paket.length > 0) {
      tbody.innerHTML = '';
      data.paket.forEach(paket => {
        const row = createPaketRow(paket);

        row.addEventListener('paket-edit', function(e) {
          const item = e.detail;
          editPaketId = item.id_paket;
          document.getElementById('formPaketTitle').textContent = 'Edit Paket';
          document.getElementById('inputNamaPaket').value = item.nama_paket || '';
          document.getElementById('inputDeskripsiPaket').value = item.deskripsi || '';
          document.getElementById('inputHargaPaket').value = item.harga || '';
          document.getElementById('formPaket').style.display = 'block';
        });

        row.addEventListener('paket-delete', function(e) {
          const item = e.detail;
          if (confirm('Yakin ingin menghapus paket ini?')) {
            fetch('/api/admin/paket/' + encodeURIComponent(item.id_paket), {
              method: 'DELETE',
              headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
              }
            })
            .then(res => res.json())
            .then(res => {
              if (res.success) fetchPaket();
              else alert('Gagal menghapus paket');
            })
            .catch(() => alert('Terjadi kesalahan saat menghapus'));
          }
        });

        tbody.appendChild(row);
      });
    } else {
      tbody.innerHTML = '<tr><td colspan="5" class="no-data">Tidak ada data paket</td></tr>';
    }
  })
  .catch(error => console.error('Error fetching paket:', error));
}

// ==================== FORM HANDLERS ====================

function initLayananHandlers() {
  const btnAdd = document.getElementById('btnAddLayanan');
  const btnSave = document.getElementById('btnSaveLayanan');
  const btnCancel = document.getElementById('btnCancelLayanan');

  if (btnAdd) {
    btnAdd.addEventListener('click', function() {
      editLayananId = null;
      document.getElementById('formLayananTitle').textContent = 'Tambah Layanan Baru';
      document.getElementById('inputNamaLayanan').value = '';
      document.getElementById('inputDeskripsiLayanan').value = '';
      document.getElementById('inputHargaLayanan').value = '';
      document.getElementById('formLayanan').style.display = 'block';
    });
  }

  if (btnSave) {
    btnSave.addEventListener('click', function() {
      const nama = document.getElementById('inputNamaLayanan').value.trim();
      const deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
      const harga = document.getElementById('inputHargaLayanan').value.trim();

      if (!nama || !deskripsi || !harga) {
        alert('Semua field harus diisi');
        return;
      }

      const payload = {
        nama_layanan: nama,
        deskripsi: deskripsi,
        harga: parseFloat(harga)
      };

      let url = '/api/admin/layanan';
      let method = 'POST';
      if (editLayananId) {
        url = '/api/admin/layanan/' + encodeURIComponent(editLayananId);
        method = 'PUT';
      }

      fetch(url, {
        method: method,
        headers: {
          'Authorization': 'Bearer ' + sessionToken,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('formLayanan').style.display = 'none';
          fetchLayanan();
        } else {
          alert('Gagal menyimpan layanan');
        }
      })
      .catch(() => alert('Terjadi kesalahan'));
    });
  }

  if (btnCancel) {
    btnCancel.addEventListener('click', function() {
      document.getElementById('formLayanan').style.display = 'none';
    });
  }
}

function initPaketHandlers() {
  const btnAdd = document.getElementById('btnAddPaket');
  const btnSave = document.getElementById('btnSavePaket');
  const btnCancel = document.getElementById('btnCancelPaket');

  if (btnAdd) {
    btnAdd.addEventListener('click', function() {
      editPaketId = null;
      document.getElementById('formPaketTitle').textContent = 'Tambah Paket Baru';
      document.getElementById('inputNamaPaket').value = '';
      document.getElementById('inputDeskripsiPaket').value = '';
      document.getElementById('inputHargaPaket').value = '';
      document.getElementById('formPaket').style.display = 'block';
    });
  }

  if (btnSave) {
    btnSave.addEventListener('click', function() {
      const nama = document.getElementById('inputNamaPaket').value.trim();
      const deskripsi = document.getElementById('inputDeskripsiPaket').value.trim();
      const harga = document.getElementById('inputHargaPaket').value.trim();

      if (!nama || !deskripsi || !harga) {
        alert('Semua field harus diisi');
        return;
      }

      const payload = {
        nama_paket: nama,
        deskripsi: deskripsi,
        harga: parseFloat(harga)
      };

      let url = '/api/admin/paket';
      let method = 'POST';
      if (editPaketId) {
        url = '/api/admin/paket/' + encodeURIComponent(editPaketId);
        method = 'PUT';
      }

      fetch(url, {
        method: method,
        headers: {
          'Authorization': 'Bearer ' + sessionToken,
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          document.getElementById('formPaket').style.display = 'none';
          fetchPaket();
        } else {
          alert('Gagal menyimpan paket');
        }
      })
      .catch(() => alert('Terjadi kesalahan'));
    });
  }

  if (btnCancel) {
    btnCancel.addEventListener('click', function() {
      document.getElementById('formPaket').style.display = 'none';
    });
  }
}

function initSidebar() {
  const sidebar = document.getElementById('sidebar');
  const toggleBtn = document.getElementById('toggleSidebar');
  if (toggleBtn && sidebar) {
    toggleBtn.addEventListener('click', function() {
      sidebar.classList.toggle('expanded');
    });
  }
}

function initLogout() {
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

function init() {
  fetchUserData();
  fetchLayanan();
  fetchPaket();
  initLayananHandlers();
  initPaketHandlers();
  initSidebar();
  initLogout();
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init);
} else {
  init();
}
