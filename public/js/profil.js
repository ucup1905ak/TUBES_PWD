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
async function fetchUserProfile() {
    try {
        const response = await fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
            }
        });

        const data = await response.json();

        if (data.success && data.user) {
            userProfile = data.user;
            displayUserProfile();
        } else {
            console.error('Failed to load user data:', data.error);
            alert('Failed to load profile. Please login again.');
            localStorage.removeItem('session_token');
            localStorage.removeItem('session_expires_at');
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Error fetching user data:', error);
    }
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
        viewElements.forEach(el => el.style.display = 'none');
        editElements.forEach(el => el.style.display = 'block');
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
        cancelBtn.style.display = 'inline-block';

        // Populate input fields with current values
        document.getElementById('username-input').value = userProfile.nama_lengkap || '';
        document.getElementById('notelp-input').value = userProfile.no_telp || '';
        document.getElementById('alamat-input').value = userProfile.alamat || '';
        document.getElementById('role-input').value = userProfile.role || 'user';
    } else {
        // Switch to view mode
        viewElements.forEach(el => el.style.display = 'block');
        editElements.forEach(el => el.style.display = 'none');
        editBtn.style.display = 'inline-block';
        saveBtn.style.display = 'none';
        cancelBtn.style.display = 'none';
    }
}

async function saveProfile() {
    const updatedData = {
        nama_lengkap: document.getElementById('username-input').value.trim(),
        no_telp: document.getElementById('notelp-input').value.trim(),
        alamat: document.getElementById('alamat-input').value.trim(),
        role: document.getElementById('role-input').value
    };

    try {
        const response = await fetch('/api/user/update', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(updatedData)
        });

        const data = await response.json();

        if (data.success) {
            userProfile = data.user;
            displayUserProfile();
            toggleEditMode(false);
            alert('Profile updated successfully!');
        } else {
            alert('Failed to update profile: ' + (data.error || 'Unknown error'));
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert('An error occurred while updating profile.');
    }
}

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    fetchUserProfile();

    // Edit button
    document.getElementById("editBtn").addEventListener("click", () => {
        toggleEditMode(true);
    });

    // Save button
    document.getElementById("saveBtn").addEventListener("click", () => {
        saveProfile();
    });

    // Cancel button
    document.getElementById("cancelBtn").addEventListener("click", () => {
        toggleEditMode(false);
    });

    // Logout button
    document.getElementById("logoutBtn").addEventListener("click", () => {
        if (confirm("Are you sure you want to logout?")) {
            localStorage.removeItem('session_token');
            localStorage.removeItem('session_expires_at');
            window.location.href = '/';
        }
    });

    // Delete account button
    document.getElementById("deleteBtn").addEventListener("click", () => {
        const confirmDelete = confirm("Are you sure you want to delete your account? This action cannot be undone.");
        if (confirmDelete) {
            alert("Account deletion is not yet implemented.");
        }
    });
});
