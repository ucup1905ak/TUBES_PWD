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

    clearErrors();

    let user = usernameInput.value.trim();
    let pass = passwordInput.value.trim();

    // Username kosong → tampilkan error di username
    if (user === "") {
        errorUser.style.display = "inline-block";
        errorUser.innerText = "Username cannot be empty.";
        return;
    }

    // Password kosong → tampilkan error di password
    if (pass === "") {
        errorPass.style.display = "inline-block";
        errorPass.innerText = "Password cannot be empty.";
        return;
    }

    // Login gagal (username + password salah)
    if (!(user === "admin" && pass === "123")) {
        errorPass.style.display = "inline-block";
        errorPass.innerText = "Incorrect username or password.";
        return;
    }

    // Jika benar → redirect
    window.location.href = "dashboard.xhtml";
}
