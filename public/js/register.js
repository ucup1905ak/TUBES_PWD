// Defer image loading until content is ready
document.addEventListener('DOMContentLoaded', function () {
    var imgs = document.querySelectorAll('img[data-src]');
    imgs.forEach(function (img) {
        // Only set src after main content is parsed
        var src = img.getAttribute('data-src');
        if (src) {
            img.setAttribute('src', src);
            img.removeAttribute('data-src');
        }
    });
});

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

