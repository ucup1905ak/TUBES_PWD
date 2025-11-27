<?php
/*
include "config.php";

// Tangkap data form
$username = $_POST['username'];
$password = $_POST['password'];
$confirm  = $_POST['confirmPassword'];
$email    = $_POST['email'];
$telepon  = $_POST['telepon'];
$alamat   = $_POST['alamat'];

// Validasi confirm password
if ($password !== $confirm) {
    echo "<script>alert('Password tidak sama!'); history.back();</script>";
    exit;
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Upload foto
$fotoName = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === 0) {
    $fotoName = time() . "_" . $_FILES['foto']['name'];
    $path = "../uploader_foto/" . $fotoName;
    move_uploaded_file($_FILES['foto']['tmp_name'], $path);
}

// Query insert — TABEL SUDAH DIBENERIN
$query = "INSERT INTO sign_up (username, password, email, telepon, alamat, foto)
          VALUES ('$username', '$hashedPassword', '$email', '$telepon', '$alamat', '$fotoName')";

if (mysqli_query($conn, $query)) {
    echo "
        <script>
            alert('Akun berhasil dibuat!');
            window.location.href = '../index.xhtml';
        </script>
    ";
} else {
    echo 'Error: ' . mysqli_error($conn);
}
*/
?>
