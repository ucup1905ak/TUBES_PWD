function updatePreview() {
    var url = document.getElementById('foto').value;
    document.getElementById('photo-preview').src = url;
}

function confirmSave() {
    return confirm('Apakah Anda yakin ingin menyimpan perubahan?');
}
