// /public/js/dashboard.js
// Script dibuat fleksibel untuk dihubungkan ke API/database nanti.
// - setUsernameFromServer(user) : panggil setelah backend mengembalikan data user
// - fetchActiveStays() : contoh panggilan API (mock) untuk menampilkan penitipan aktif

(function () {
  'use strict';

  // --- Helper / konfigurasi ---
  var selectors = {
    userDisplay: document.getElementById('user-display'),
    welcomeUsername: document.getElementById('welcome-username'),
    activeStaysContent: document.getElementById('active-stays-content'),
    viewAllHistoryBtn: document.getElementById('view-all-history')
  };

  // Fungsi untuk tombol lihat detail penitipan (untuk tabel statis)
  window.lihatDetailPenitipan = function(id) {
    window.location.href = 'detail_penitipan.xhtml?id=' + encodeURIComponent(id);
  };

  // Default username (akan diganti oleh setUsernameFromServer jika tersedia)
  var state = {
    username: 'Teman PawHaven',
    activeStays: null // akan berisi array jika ada
  };

  // Fungsi publik: panggil ini bila server/auth mengembalikan data user
  // Contoh pemakaian: setUsernameFromServer({ username: 'Silvanus' })
  window.setUsernameFromServer = function (user) {
    try {
      if (user && user.username) {
        state.username = user.username;
      }
    } catch (e) { /* fallback silently */ }
    renderUsername();
  };

  // Render username di beberapa tempat
  function renderUsername() {
    if (selectors.userDisplay) selectors.userDisplay.textContent = state.username;
    if (selectors.welcomeUsername) selectors.welcomeUsername.textContent = state.username;
    // bisa juga menambahkan inisialisasi avatar, dll.
  }

  // Example: ambil data penitipan aktif dari API.
  // Untuk saat ini, fungsi mencoba fetch('/api/user/active-stays') lalu fallback ke mock data.
  function fetchActiveStays() {
    var fallbackDelay = 300; // ms
    // coba fetch nyata (jika backend nanti disambungkan)
    return fetch('/api/user/active-stays', { credentials: 'same-origin' })
      .then(function (res) {
        if (!res.ok) throw new Error('no data');
        return res.json();
      })
      .catch(function () {
        // fallback: cek localStorage atau mock
        var mock = getMockActiveStays();
        return new Promise(function (resolve) {
          setTimeout(function () { resolve(mock); }, fallbackDelay);
        });
      });
  }

  // Mock data: bisa diubah / dihapus ketika backend siap
  function getMockActiveStays() {
    // contoh format: [{id, petName, checkin, checkout, status}]
    // Kosongkan array untuk menunjukkan "tidak ada penitipan aktif"
    return [
      // Uncomment contoh untuk melihat tampilan "ada penitipan aktif":
      // { id: 1, petName: 'Bimo', checkin: '2025-11-25', checkout: '2025-12-02', status: 'Dalam Perawatan' }
      // [] untuk tidak ada
    ];
  }

  // Render area penitipan aktif
  function renderActiveStays(list) {
    var container = selectors.activeStaysContent;
    if (!container) return;

    container.innerHTML = ''; // reset

    if (!list || !list.length) {
      var p = document.createElement('p');
      p.textContent = 'Belum ada penitipan aktif saat ini. Ayo pesan penitipan untuk sahabatmu!';
      p.className = 'muted';
      container.appendChild(p);
      return;
    }

    // Jika ada beberapa, tampilkan ringkasan singkat (maks 3)
    var maxShow = 3;
    list.slice(0, maxShow).forEach(function (stay) {
      var div = document.createElement('div');
      div.className = 'stay-item';

      var left = document.createElement('div');
      left.innerHTML = '<strong>' + escapeHtml(stay.petName || 'Hewan') + '</strong><div class="meta">' +
        (stay.status ? escapeHtml(stay.status) : '') + '</div>';

      var right = document.createElement('div');
      right.className = 'meta';
      right.innerText = (stay.checkout ? 'Keluar: ' + stay.checkout : 'Tanggal: -');

      div.appendChild(left);
      div.appendChild(right);
      container.appendChild(div);
    });

    if (list.length > maxShow) {
      var more = document.createElement('div');
      more.className = 'meta';
      more.style.marginTop = '8px';
      more.textContent = (list.length - maxShow) + ' penitipan lainnya...';
      container.appendChild(more);
    }
  }

  // Simple escape untuk text dari server
  function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>"'\/]/g, function (s) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;', '/': '&#47;' })[s];
    });
  }

  // Inisialisasi: render username, lalu fetch active stays
  function init() {
    renderUsername();

    // Ambil penitipan aktif (mock atau API)
    fetchActiveStays().then(function (data) {
      // Jika server mengembalikan object { data: [...] }, coba normalisasi
      var list = Array.isArray(data) ? data : (data && data.data ? data.data : []);
      state.activeStays = list;
      renderActiveStays(list);
    }).catch(function (err) {
      // fallback: tampilkan pesan error ringan
      var container = selectors.activeStaysContent;
      if (container) {
        container.innerHTML = '<p class="muted">Gagal memuat data penitipan aktif.</p>';
      }
      console.error('fetchActiveStays error', err);
    });

    // Contoh: tombol lihat semua -> bisa di-enhance nanti
    if (selectors.viewAllHistoryBtn) {
      selectors.viewAllHistoryBtn.addEventListener('click', function () {
        // Jika butuh, bisa kirim event ke analytics / lakukan prefetch
      });
    }
  }

  // Jalankan init saat DOM siap (defer digunakan), tapi tetap guard
  if (document.readyState === 'loading') {
    document.addEventListener("DOMContentLoaded", function() {
    const sidebar = document.getElementById("sidebar");
    const toggleBtn = document.getElementById("toggleSidebar");

    toggleBtn.addEventListener("click", function () {
        sidebar.classList.toggle("expanded");
    });
});
  } else {
    init();
  }

})();

function goToDetail() {
    location.href = "detail-penitipan.xhtml"
}

function goToHistory() {
    location.href = "riwayat.xhtml"
}

function goToAdd() {
    location.href = "titip.xhtml"
}

// Opsional: kalau sidebar bisa expand
const sidebar = document.getElementById("sidebar")
const content = document.querySelector(".dashboard-container")

document.getElementById("toggleSidebar").addEventListener("click", () => {
    sidebar.classList.toggle("expanded")
    content.classList.toggle("sidebar-expanded-margin")
})

