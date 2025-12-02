const userProfile = {
    username: "your_username",
    email: "user@mail.com",
    notelp: "08123456789",
    jenis: "hewan",
    alamat: "Jl. jalan No. 10",
    foto: ""
};

document.getElementById("username").textContent = userProfile.username;
document.getElementById("email").textContent = userProfile.email;
document.getElementById("notelp").textContent = userProfile.notelp;
document.getElementById("jenis").textContent = userProfile.jenis;
document.getElementById("alamat").textContent = userProfile.alamat;

const photo = document.getElementById("photo");
photo.style.backgroundImage = userProfile.foto
    ? `url('${userProfile.foto}')`
    : "url('https://via.placeholder.com/250')";


document.getElementById("logoutBtn").addEventListener("click", () => {
    alert("Anda berhasil logout!");

});

document.getElementById("deleteBtn").addEventListener("click", () => {
    const confirmDelete = confirm("Yakin ingin hapus akun?");
    if (confirmDelete) {
        alert("Akun sudah dihapus.");
    }
});
