// Check if user is logged in
const sessionToken = localStorage.getItem('session_token');
const expiresAt = localStorage.getItem('session_expires_at');

if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
    localStorage.removeItem('session_token');
    localStorage.removeItem('session_expires_at');
    window.location.href = '/login';
}

let userProfile = null;
let isEditMode = false;

// Fetch user data from API
function fetchUserProfile() {
    fetch('/api/auth/me?include_photo=1', {
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
        displayUserProfile();
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

    const photo = document.getElementById("photo");
    if (userProfile.foto_profil) {
        photo.style.backgroundImage = `url('data:image/jpeg;base64,${userProfile.foto_profil}')`;
    } else {
        photo.style.backgroundImage = "url('https://via.placeholder.com/250')";
    }
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

        // Komentar: Populate input fields dengan nilai saat ini
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
        displayUserProfile();
        toggleEditMode(false);
        alert('Profile updated successfully!');
      } else {
        alert('Failed to update profile: ' + (data.error || 'Unknown error'));
      }
    })
    .catch(function(error) {
      console.error('Error updating profile:', error);
      alert('An error occurred while updating profile.');
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

// Komentar: Gelapkan background username saat di halaman profil
function initProfileHeader() {
  var userProfile = document.querySelector('.user-profile');
  if (userProfile) {
    userProfile.classList.add('active');
  }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    fetchUserProfile();
    initSidebar();
    initProfileHeader();

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

    // Logout button
    document.getElementById("logoutBtn").addEventListener("click", function() {
        if (confirm("Yakin ingin logout?")) {
            localStorage.removeItem('session_token');
            localStorage.removeItem('session_expires_at');
            window.location.href = '/';
        }
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
});
