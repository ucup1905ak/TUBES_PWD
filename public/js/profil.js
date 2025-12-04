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
      // Fallback to native alert if something goes wrong
      window.__alertFallback ? window.__alertFallback(message) : window.prompt && window.prompt(String(message));
    }
  };
})();

// Check if user is logged in
const sessionToken = localStorage.getItem('session_token');
const expiresAt = localStorage.getItem('session_expires_at');

if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
    localStorage.removeItem('session_token');
    localStorage.removeItem('session_expires_at');
    window.location.href = '/login';
}

let userProfile = null;
let profilePhotoDataUrl = null;
let isPhotoLoading = false;
let isEditMode = false;

// Fetch user data from API
function fetchUserProfile() {
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
        userProfile = data.user;
        // reset cached photo when refetching profile
        profilePhotoDataUrl = null;
        displayUserProfile();
        updateProfilePhoto();
        if (userProfile.has_foto_profil) {
          fetchUserPhoto();
        }
        // Komentar: Update nama user di header
        var userName = document.getElementById('user-name');
        if (userName) {
          userName.textContent = userProfile.nama_lengkap || 'Akun Saya';
        }
      } else {
        console.error('Failed to load user data:', data.error);
        alert('Failed to load profile. Please login again.');
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
        window.location.href = '/login';
      }
    })
    .catch(function(error) {
      console.error('Error fetching user data:', error);
    });
}

function displayUserProfile() {
    if (!userProfile) return;

    document.getElementById("username").textContent = userProfile.nama_lengkap || 'N/A';
    document.getElementById("email").textContent = userProfile.email || 'N/A';
    document.getElementById("notelp").textContent = userProfile.no_telp || 'N/A';
    document.getElementById("role").textContent = userProfile.role === 'admin' ? 'Admin' : 'User (Customer)';
    document.getElementById("alamat").textContent = userProfile.alamat || 'N/A';

    updateProfilePhoto();
}

function updateProfilePhoto() {
  const photo = document.getElementById('photo');
  if (!photo) return;

  if (profilePhotoDataUrl) {
    photo.style.backgroundImage = `url('${profilePhotoDataUrl}')`;
    return;
  }

  if (userProfile && userProfile.has_foto_profil) {
    photo.style.backgroundImage = "url('https://via.placeholder.com/250?text=Loading...')";
  } else {
    photo.style.backgroundImage = "url('https://via.placeholder.com/250')";
  }
}

function fetchUserPhoto(forceReload) {
  if (!userProfile || (!forceReload && (isPhotoLoading || profilePhotoDataUrl))) {
    return;
  }

  isPhotoLoading = true;
  fetch('/api/auth/me/photo', {
    method: 'GET',
    headers: {
      'Authorization': 'Bearer ' + sessionToken,
      'Content-Type': 'application/json'
    }
  })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        if (data.foto_profil) {
          profilePhotoDataUrl = `data:image/jpeg;base64,${data.foto_profil}`;
          if (userProfile) {
            userProfile.has_foto_profil = true;
          }
        } else {
          profilePhotoDataUrl = null;
          if (userProfile) {
            userProfile.has_foto_profil = false;
          }
        }
        updateProfilePhoto();
      }
    })
    .catch(function(error) {
      console.error('Error fetching profile photo:', error);
    })
    .finally(function() {
      isPhotoLoading = false;
    });
}

function toggleEditMode(enable) {
    isEditMode = enable;
    
    const viewElements = document.querySelectorAll('.view-mode');
    const editElements = document.querySelectorAll('.edit-mode');
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const cancelBtn = document.getElementById('cancelBtn');

    if (enable) {
        // Switch to edit mode
        viewElements.forEach(function(el) { el.style.display = 'none'; });
        editElements.forEach(function(el) { el.style.display = 'block'; });
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';

        // Populate input fields dengan nilai saat ini
        document.getElementById('username-input').value = userProfile.nama_lengkap || '';
        document.getElementById('notelp-input').value = userProfile.no_telp || '';
        document.getElementById('alamat-input').value = userProfile.alamat || '';
        document.getElementById('role-input').value = userProfile.role || 'user';
    } else {
        // Switch to view mode
        viewElements.forEach(function(el) { el.style.display = 'block'; });
        editElements.forEach(function(el) { el.style.display = 'none'; });
        editBtn.style.display = 'inline-block';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    }
}

function saveProfile() {
    const updatedData = {
        nama_lengkap: document.getElementById('username-input').value.trim(),
        no_telp: document.getElementById('notelp-input').value.trim(),
        alamat: document.getElementById('alamat-input').value.trim(),
        role: document.getElementById('role-input').value
    };

    fetch('/api/user/update', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + sessionToken,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(updatedData)
    })
    .then(function(response) { return response.json(); })
    .then(function(data) {
      if (data.success) {
        userProfile = data.user;
        if (userProfile) {
          if (userProfile.foto_profil) {
            profilePhotoDataUrl = `data:image/jpeg;base64,${userProfile.foto_profil}`;
            userProfile.has_foto_profil = true;
          } else {
            profilePhotoDataUrl = null;
          }
          delete userProfile.foto_profil;
        }
        displayUserProfile();
        updateProfilePhoto();
        toggleEditMode(false);
        alert('Profile updated successfully!');
        if (userProfile && userProfile.has_foto_profil && !profilePhotoDataUrl) {
          fetchUserPhoto(true);
        }
      } else {
        alert('Failed to update profile: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(function(error) {
      console.error('Error updating profile:', error);
      alert('An error occurred while updating profile.');
    });
}

// Komentar: Inisialisasi profile page
function initProfilePage() {
  var userProfileHeader = document.querySelector('.user-profile');
  if (userProfileHeader) {
    userProfileHeader.classList.add('active');
  }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    fetchUserProfile();
    initProfilePage();

    // Edit button
    document.getElementById("editBtn").addEventListener("click", function() {
        toggleEditMode(true);
    });

    // Save button
    document.getElementById("saveBtn").addEventListener("click", function() {
        saveProfile();
    });

    // Cancel button
    document.getElementById("cancelBtn").addEventListener("click", function() {
        toggleEditMode(false);
    });

    // Delete account button
    document.getElementById("deleteBtn").addEventListener("click", function() {
        const confirmDelete = confirm("Yakin ingin menghapus akun? Tindakan ini tidak dapat dibatalkan.");
        if (confirmDelete) {
            const doubleConfirm = confirm("Ini akan menghapus semua data Anda termasuk pet dan pemesanan. Lanjutkan?");
            if (doubleConfirm) {
                fetch('/api/user/delete', {
                  method: 'DELETE',
                  headers: {
                    'Authorization': 'Bearer ' + sessionToken,
                    'Content-Type': 'application/json'
                  }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                  if (data.success) {
                    alert('Akun Anda telah dihapus.');
                    localStorage.removeItem('session_token');
                    localStorage.removeItem('session_expires_at');
                    window.location.href = '/';
                  } else {
                    alert('Gagal menghapus akun: ' + (data.error || 'Unknown error'));
                  }
                })
                .catch(function(error) {
                  console.error('Error deleting account:', error);
                  alert('Terjadi kesalahan saat menghapus akun.');
                });
            }
        }
    });
        // Upload Photo: buka file explorer
    document.getElementById("uploadPhotoBtn").addEventListener("click", function () {
        document.getElementById("photo-input").click();
    });

    // Upload Photo: ketika file dipilih
    document.getElementById("photo-input").addEventListener("change", function(event) {
        const file = event.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append("foto_profil", file);

        // Agar backend tidak error, ikut kirim data lain (tetap nilai lama)
        formData.append("nama_lengkap", userProfile.nama_lengkap || "");
        formData.append("no_telp", userProfile.no_telp || "");
        formData.append("alamat", userProfile.alamat || "");
        formData.append("role", userProfile.role || "user");

        fetch('/api/user/update', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
                // ❗ JANGAN tambahkan Content-Type, FormData akan mengatur sendiri
            },
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Foto profil berhasil diperbarui!');
                fetchUserPhoto(true); // reload foto
            } else {
                alert('Gagal update foto: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error("Upload error:", err);
            alert("Terjadi kesalahan saat upload foto.");
        });
    });
});
