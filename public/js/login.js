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

function login() {
    const email = document.getElementById("username").value; // Assuming username field is for email
    const password = document.getElementById("password").value;
    const errorBox = document.getElementById("errorBox");
    const errorPassword = document.getElementById("errorPassword");

    // Reset errors
    errorBox.textContent = "";
    errorPassword.textContent = "";
    let hasError = false;

    if (!email) {
        errorBox.textContent = "Please enter your email.";
        hasError = true;
    }
    if (!password) {
        errorPassword.textContent = "Please enter your password.";
        hasError = true;
    }

    if (hasError) {
        return;
    }

    // API call
    fetch('/api/auth/login', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ email: email, password: password })
    })
    .then(response => {
        if (response.ok) {
            return response.json();
        } else {
            // If the server returns a non-200 status, handle it as a login failure.
            throw new Error('Login failed. Please check your credentials.');
        }
    })
    .then(data => {
        if (data.success) {
            // On successful login, redirect to the dashboard.
            window.location.href = '/dashboard.php';
        } else {
            // This case might be redundant if !response.ok is caught, but good for explicit server-side failures.
            throw new Error(data.error || 'Login failed. Please check your credentials.');
        }
    })
    .catch(error => {
        // Display a generic error message for any kind of failure
        errorBox.textContent = error.message;
        console.error('Login error:', error);
    });
}
