// Install a simple card-based replacement for window.alert
(function installAlertCard(){
  if (window.__alertCardInstalled) return;
  window.__alertCardInstalled = true;

  function ensureStyles() {
    if (document.getElementById('alert-card-styles')) return;
    const style = document.createElement('style');
    style.id = 'alert-card-styles';
    style.textContent = `
      .notice-container{position:fixed;top:16px;right:16px;z-index:9999;display:flex;flex-direction:column;gap:10px}
      .notice-card{display:flex;align-items:flex-start;gap:8px;min-width:260px;max-width:380px;padding:12px 14px;border-radius:10px;background:#fff;color:#222;border:1px solid rgba(0,0,0,.08);box-shadow:0 8px 24px rgba(0,0,0,.12)}
      .notice-card.info{border-left:4px solid #3b82f6}
      .notice-card.success{border-left:4px solid #10b981}
      .notice-card.error{border-left:4px solid #ef4444}
      .notice-message{line-height:1.35;font-size:14px;margin-right:8px;white-space:pre-line}
      .notice-close{margin-left:auto;border:none;background:transparent;color:#444;cursor:pointer;font-size:16px;line-height:1;padding:0 4px}
      .notice-close:hover{color:#000}
    `;
    document.head.appendChild(style);
  }

  function ensureContainer() {
    let c = document.querySelector('.notice-container');
    if (!c) {
      c = document.createElement('div');
      c.className = 'notice-container';
      (document.body || document.documentElement).appendChild(c);
    }
    return c;
  }

  function pickType(message){
    const m = (message || '').toLowerCase();
    if (m.includes('gagal') || m.includes('error') || m.includes('kesalahan') || m.includes('invalid') ) return 'error';
    if (m.includes('berhasil') || m.includes('success')) return 'success';
    return 'info';
  }

  window.alert = function(message){
    try{
      ensureStyles();
      const container = ensureContainer();
      const type = pickType(String(message));
      const card = document.createElement('div');
      card.className = 'notice-card ' + type;
      const msg = document.createElement('div');
      msg.className = 'notice-message';
      msg.textContent = String(message);
      const close = document.createElement('button');
      close.className = 'notice-close';
      close.setAttribute('aria-label','Close');
      close.textContent = '×';
      close.onclick = () => card.remove();
      card.appendChild(msg);
      card.appendChild(close);
      container.appendChild(card);
      setTimeout(() => card.remove(), 4000);
    }catch(e){
      window.__alertFallback ? window.__alertFallback(message) : window.prompt && window.prompt(String(message));
    }
  };
})();

// /public/js/kelola.js
// Admin - Kelola Layanan & Paket Page script

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

  // Komentar: State untuk track edit mode
  var editLayananId = null;
  var editPaketId = null;

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
        if (userName) {
          userName.textContent = user.nama_lengkap || 'Admin';
        }
      }
    })
    .catch(function(error) {
      console.error('Error fetching user data:', error);
    });
  }

  // ==================== LAYANAN ====================

  // Komentar: Fetch dan display semua layanan
  function fetchLayanan() {
    fetch('/api/admin/layanan', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var tbody = document.getElementById('layanan-tbody');
      if (!tbody) return;

      if (data.success && data.layanan && data.layanan.length > 0) {
        tbody.innerHTML = '';
        
        data.layanan.forEach(function(layanan) {
          var tr = document.createElement('tr');
          
          var tdId = document.createElement('td');
          tdId.textContent = layanan.id_layanan || '-';
          
          var tdNama = document.createElement('td');
          tdNama.textContent = layanan.nama_layanan || '-';
          
          var tdDeskripsi = document.createElement('td');
          tdDeskripsi.textContent = (layanan.deskripsi || '-').substring(0, 50) + (layanan.deskripsi && layanan.deskripsi.length > 50 ? '...' : '');
          
          var tdHarga = document.createElement('td');
          tdHarga.textContent = 'Rp' + (layanan.harga || 0).toLocaleString();
          
          // Komentar: Kolom aksi - tombol edit dan hapus
          var tdAksi = document.createElement('td');
          
          var editBtn = document.createElement('button');
          editBtn.textContent = 'Edit';
          editBtn.className = 'edit-btn';
          editBtn.addEventListener('click', function() {
            editLayananId = layanan.id_layanan;
            document.getElementById('formLayananTitle').textContent = 'Edit Layanan';
            document.getElementById('inputNamaLayanan').value = layanan.nama_layanan || '';
            document.getElementById('inputDeskripsiLayanan').value = layanan.deskripsi || '';
            document.getElementById('inputHargaLayanan').value = layanan.harga || '';
            document.getElementById('formLayanan').style.display = 'block';
          });
          
          var deleteBtn = document.createElement('button');
          deleteBtn.textContent = 'Hapus';
          deleteBtn.className = 'delete-btn';
          deleteBtn.addEventListener('click', function() {
            if (confirm('Yakin ingin menghapus layanan ini?')) {
              // Komentar: Hapus layanan dari database
              fetch('/api/admin/layanan/' + encodeURIComponent(layanan.id_layanan), {
                method: 'DELETE',
                headers: {
                  'Authorization': 'Bearer ' + sessionToken,
                  'Content-Type': 'application/json'
                }
              })
              .then(function(response) { return response.json(); })
              .then(function(res) {
                if (res.success) {
                  fetchLayanan(); // refresh tabel
                } else {
                  alert('Gagal menghapus layanan');
                }
              })
              .catch(function(error) {
                alert('Terjadi kesalahan saat menghapus');
              });
            }
          });
          
          tdAksi.appendChild(editBtn);
          tdAksi.appendChild(deleteBtn);
          
          tr.appendChild(tdId);
          tr.appendChild(tdNama);
          tr.appendChild(tdDeskripsi);
          tr.appendChild(tdHarga);
          tr.appendChild(tdAksi);
          
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="no-data">Tidak ada data layanan</td></tr>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching layanan:', error);
    });
  }

  // ==================== PAKET ====================

  // Komentar: Fetch dan display semua paket
  function fetchPaket() {
    fetch('/api/admin/paket', {
      method: 'GET',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      }
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      var tbody = document.getElementById('paket-tbody');
      if (!tbody) return;

      if (data.success && data.paket && data.paket.length > 0) {
        tbody.innerHTML = '';
        
        data.paket.forEach(function(paket) {
          var tr = document.createElement('tr');
          
          var tdId = document.createElement('td');
          tdId.textContent = paket.id_paket || '-';
          
          var tdNama = document.createElement('td');
          tdNama.textContent = paket.nama_paket || '-';
          
          var tdDeskripsi = document.createElement('td');
          tdDeskripsi.textContent = (paket.deskripsi || '-').substring(0, 50) + (paket.deskripsi && paket.deskripsi.length > 50 ? '...' : '');
          
          var tdHarga = document.createElement('td');
          tdHarga.textContent = 'Rp' + (paket.harga || 0).toLocaleString();
          
          // Komentar: Kolom aksi - tombol edit dan hapus
          var tdAksi = document.createElement('td');
          
          var editBtn = document.createElement('button');
          editBtn.textContent = 'Edit';
          editBtn.className = 'edit-btn';
          editBtn.addEventListener('click', function() {
            editPaketId = paket.id_paket;
            document.getElementById('formPaketTitle').textContent = 'Edit Paket';
            document.getElementById('inputNamaPaket').value = paket.nama_paket || '';
            document.getElementById('inputDeskripsiPaket').value = paket.deskripsi || '';
            document.getElementById('inputHargaPaket').value = paket.harga || '';
            document.getElementById('formPaket').style.display = 'block';
          });
          
          var deleteBtn = document.createElement('button');
          deleteBtn.textContent = 'Hapus';
          deleteBtn.className = 'delete-btn';
          deleteBtn.addEventListener('click', function() {
            if (confirm('Yakin ingin menghapus paket ini?')) {
              // Komentar: Hapus paket dari database
              fetch('/api/admin/paket/' + encodeURIComponent(paket.id_paket), {
                method: 'DELETE',
                headers: {
                  'Authorization': 'Bearer ' + sessionToken,
                  'Content-Type': 'application/json'
                }
              })
              .then(function(response) { return response.json(); })
              .then(function(res) {
                if (res.success) {
                  fetchPaket(); // refresh tabel
                } else {
                  alert('Gagal menghapus paket');
                }
              })
              .catch(function(error) {
                alert('Terjadi kesalahan saat menghapus');
              });
            }
          });
          
          tdAksi.appendChild(editBtn);
          tdAksi.appendChild(deleteBtn);
          
          tr.appendChild(tdId);
          tr.appendChild(tdNama);
          tr.appendChild(tdDeskripsi);
          tr.appendChild(tdHarga);
          tr.appendChild(tdAksi);
          
          tbody.appendChild(tr);
        });
      } else {
        tbody.innerHTML = '<tr><td colspan="5" class="no-data">Tidak ada data paket</td></tr>';
      }
    })
    .catch(function(error) {
      console.error('Error fetching paket:', error);
    });
  }

  // ==================== FORM HANDLERS ====================

  // Komentar: Inisialisasi button handlers untuk layanan
  function initLayananHandlers() {
    var btnAdd = document.getElementById('btnAddLayanan');
    var btnSave = document.getElementById('btnSaveLayanan');
    var btnCancel = document.getElementById('btnCancelLayanan');

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
        var nama = document.getElementById('inputNamaLayanan').value.trim();
        var deskripsi = document.getElementById('inputDeskripsiLayanan').value.trim();
        var harga = document.getElementById('inputHargaLayanan').value.trim();

        if (!nama || !deskripsi || !harga) {
          alert('Semua field harus diisi');
          return;
        }

        var payload = {
          nama_layanan: nama,
          deskripsi: deskripsi,
          harga: parseFloat(harga)
        };

        var url = '/api/admin/layanan';
        var method = 'POST';
        
        if (editLayananId) {
          url = '/api/admin/layanan/' + encodeURIComponent(editLayananId);
          method = 'PUT';
        }

        // Komentar: Save layanan (tambah atau edit)
        fetch(url, {
          method: method,
          headers: {
            'Authorization': 'Bearer ' + sessionToken,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
          if (data.success) {
            document.getElementById('formLayanan').style.display = 'none';
            fetchLayanan();
          } else {
            alert('Gagal menyimpan layanan');
          }
        })
        .catch(function(error) {
          alert('Terjadi kesalahan');
        });
      });
    }

    if (btnCancel) {
      btnCancel.addEventListener('click', function() {
        document.getElementById('formLayanan').style.display = 'none';
      });
    }
  }

  // Komentar: Inisialisasi button handlers untuk paket
  function initPaketHandlers() {
    var btnAdd = document.getElementById('btnAddPaket');
    var btnSave = document.getElementById('btnSavePaket');
    var btnCancel = document.getElementById('btnCancelPaket');

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
        var nama = document.getElementById('inputNamaPaket').value.trim();
        var deskripsi = document.getElementById('inputDeskripsiPaket').value.trim();
        var harga = document.getElementById('inputHargaPaket').value.trim();

        if (!nama || !deskripsi || !harga) {
          alert('Semua field harus diisi');
          return;
        }

        var payload = {
          nama_paket: nama,
          deskripsi: deskripsi,
          harga: parseFloat(harga)
        };

        var url = '/api/admin/paket';
        var method = 'POST';
        
        if (editPaketId) {
          url = '/api/admin/paket/' + encodeURIComponent(editPaketId);
          method = 'PUT';
        }

        // Komentar: Save paket (tambah atau edit)
        fetch(url, {
          method: method,
          headers: {
            'Authorization': 'Bearer ' + sessionToken,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(payload)
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
          if (data.success) {
            document.getElementById('formPaket').style.display = 'none';
            fetchPaket();
          } else {
            alert('Gagal menyimpan paket');
          }
        })
        .catch(function(error) {
          alert('Terjadi kesalahan');
        });
      });
    }

    if (btnCancel) {
      btnCancel.addEventListener('click', function() {
        document.getElementById('formPaket').style.display = 'none';
      });
    }
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

})();
