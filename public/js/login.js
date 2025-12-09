const errorUser = document.getElementById("errorBox");
const errorPass = document.getElementById("errorPassword");

const usernameInput = document.getElementById("username");
const passwordInput = document.getElementById("password");

function clearErrors() {
    errorUser.style.display = "none";
    errorPass.style.display = "none";
}

usernameInput.addEventListener("input", clearErrors);
passwordInput.addEventListener("input", clearErrors);

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

// Clear error messages when user types
function clearErrors() {
    const errorBox = document.getElementById("errorBox");
    const errorPass = document.getElementById("errorPassword");
    if (errorBox) errorBox.style.display = "none";
    if (errorPass) errorPass.style.display = "none";
}

// Add event listeners after DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const usernameInput = document.getElementById("username");
    const passwordInput = document.getElementById("password");
    
    if (usernameInput) usernameInput.addEventListener("input", clearErrors);
    if (passwordInput) passwordInput.addEventListener("input", clearErrors);
});

async function login() {
    const identifier = document.getElementById("username").value; // Can be email or username
    const password = document.getElementById("password").value;
    const errorBox = document.getElementById("errorBox");
    const errorPassword = document.getElementById("errorPassword");

    // Reset errors
    errorBox.textContent = "";
    errorBox.style.display = "none";
    errorPassword.textContent = "";
    errorPassword.style.display = "none";
    let hasError = false;

    if (!identifier) {
        errorBox.textContent = "Please enter your email or username.";
        errorBox.style.display = "block";
        hasError = true;
    }
    if (!password) {
        errorPassword.textContent = "Please enter your password.";
        errorPassword.style.display = "block";
        hasError = true;
    }

    if (hasError) {
        return;
    }

    // Hash password using SHA-256
    const hashedBuffer = await crypto.subtle.digest('SHA-256', new TextEncoder().encode(password));
    const passwordArray = Array.from(new Uint8Array(hashedBuffer));
    const hashedPassword = passwordArray.map(b => b.toString(16).padStart(2, '0')).join('');

    // Auto-detect if identifier is email or username
    const isEmail = identifier.includes('@');
    const payload = {
        password: hashedPassword
    };
    
    if (isEmail) {
        payload.email = identifier;
    } else {
        payload.username = identifier;
    }

    // API call
    try {
        const response = await fetch('/api/auth/login', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (response.ok && data.status === 200) {
            // Store session token and expiration time in localStorage
            localStorage.setItem('session_token', data.session_token);
            localStorage.setItem('session_expires_at', data.expires_at);
            
            // Also store in cookie for server-side access (expires in 7 days)
            const expires = new Date(data.expires_at);
            document.cookie = `session_token=${data.session_token}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
            
            // Redirect to dashboard
            window.location.href = '/my';
        } else {
            // Display error from server
            errorBox.textContent = data.error || 'Login failed. Please check your credentials.';
            errorBox.style.display = "block";
        }
    } catch (error) {
        // Display network or other errors
        errorBox.textContent = 'An error occurred. Please try again.';
        errorBox.style.display = "block";
        console.error('Login error:', error);
    }
}
