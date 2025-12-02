// Check if user is already logged in
if (localStorage.getItem('session_token')) {
    const expiresAt = localStorage.getItem('session_expires_at');
    if (expiresAt && new Date(expiresAt) > new Date()) {
        // Session is still valid, redirect to dashboard
        window.location.href = '/my';
    } else {
        // Session expired, clear storage
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
    }
}

// SHA-256 hash function
async function sha256(message) {
    const msgBuffer = new TextEncoder().encode(message);
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgBuffer);
    const hashArray = Array.from(new Uint8Array(hashBuffer));
    return hashArray.map(b => b.toString(16).padStart(2, '0')).join('');
}

// Wait for DOM to be loaded
document.addEventListener('DOMContentLoaded', function() {
    // Tampilkan nama file yang dipilih
    const fotoInput = document.getElementById("foto");
    if (fotoInput) {
        fotoInput.addEventListener("change", function () {
            var fileLabel = document.getElementById("fileName");
            if (fileLabel) {
                fileLabel.innerHTML = (this.files.length > 0)
                    ? this.files[0].name
                    : "No file selected";
            }
        });
    }

    // Auto-expand untuk textarea alamat
    var textArea = document.getElementById("alamat");
    if (textArea) {
        textArea.addEventListener("input", function () {
            this.style.height = "auto";
            this.style.height = this.scrollHeight + "px";
        });
    }

    // Handle form submission with AJAX
    const form = document.getElementsByTagName("form")[0];
    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            
            var p1 = document.getElementById("password").value;
            var p2 = document.getElementById("confirmPassword").value;

            if (p1 !== p2) {
                alert("Password dan Confirm Password tidak sama!");
                return;
            }

            // Hash the password
            const hashedPassword = await sha256(p1);

            // Prepare form data
            const formData = new FormData(this);
            
            // Replace password with hashed version
            formData.set('password', hashedPassword);
            formData.set('confirmPassword', hashedPassword);

            try {
                const response = await fetch('/api/auth/register', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    alert('Registration successful! You can now login.');
                    window.location.href = '/login';
                } else {
                    // Display validation errors
                    if (data.details && Array.isArray(data.details)) {
                        alert('Registration failed:\n' + data.details.join('\n'));
                    } else {
                        alert(data.error || 'Registration failed. Please try again.');
                    }
                }
            } catch (error) {
                alert('An error occurred. Please try again.');
                console.error('Registration error:', error);
            }
        });
    }
});

