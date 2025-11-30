// Tampilkan nama file yang dipilih
document.getElementById("foto").addEventListener("change", function () {
    var fileLabel = document.getElementById("fileName");
    fileLabel.innerHTML = (this.files.length > 0)
        ? this.files[0].name
        : "No file selected";
});

// Auto-expand untuk textarea alamat
var textArea = document.getElementById("alamat");
textArea.addEventListener("input", function () {
    this.style.height = "auto";
    this.style.height = this.scrollHeight + "px";
});

// Cek password & confirm password sebelum submit
document.getElementsByTagName("form")[0].addEventListener("submit", function (e) {
    var p1 = document.getElementById("password").value;
    var p2 = document.getElementById("confirmPassword").value;

    if (p1 !== p2) {
        alert("Password dan Confirm Password tidak sama!");
        e.preventDefault();
    }
});
