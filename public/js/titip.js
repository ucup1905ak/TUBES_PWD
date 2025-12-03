// /public/js/titip.js
// Form Penitipan & Pet management (with auto total & durasi)

(function () {
    'use strict';

    // ======================
    // SESSION CHECK
    // ======================
    const sessionToken = localStorage.getItem('session_token');
    const expiresAt = localStorage.getItem('session_expires_at');

    if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
        window.location.href = '/login';
        return;
    }

    let userPets = [];
    let editMode = false;
    let editPenitipanId = null;

    function getEditId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id');
    }

    // ======================
    // LOAD PETS
    // ======================
    async function loadPets() {
        try {
            const response = await fetch('/api/hewan', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + sessionToken,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success && data.pets) {
                userPets = data.pets;
                populatePetSelect();
            }
        } catch (error) {
            console.error('Error loading pets:', error);
        }
    }

    function populatePetSelect() {
        const select = document.getElementById('pet_select');
        if (!select) return;

        while (select.options.length > 2) {
            select.remove(2);
        }

        userPets.forEach(function (pet) {
            const option = document.createElement('option');
            option.value = pet.id_pet;
            option.textContent =
                pet.nama_pet + (pet.jenis_pet ? ' (' + pet.jenis_pet + ')' : '');
            select.appendChild(option);
        });
    }

    // ======================
    // LOAD PENITIPAN (EDIT)
    // ======================
    async function loadPenitipanData(penitipanId) {
        try {
            const response = await fetch('/api/penitipan/' + penitipanId, {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + sessionToken,
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success && data.penitipan) {
                const p = data.penitipan;

                // Pre-fill
                document.getElementById('pet_select').value = p.id_pet;
                document.getElementById('tgl_checkin').value = p.tgl_checkin;
                document.getElementById('tgl_checkout').value = p.tgl_checkout;

                editMode = true;
                editPenitipanId = penitipanId;

                // Ubah judul
                document.querySelector('.title').textContent = 'Edit Penitipan';
                document.querySelector('.submit-btn').textContent = 'Simpan Perubahan';

                // Sembunyikan opsi tambah pet
                const newOption = document.querySelector('option[value="new"]');
                if (newOption) newOption.style.display = 'none';

                updateUI();

            } else {
                alert('Penitipan tidak ditemukan.');
                window.location.href = '/my';
            }
        } catch (error) {
            alert('Gagal memuat data penitipan.');
            window.location.href = '/my';
        }
    }

    function toggleNewPetFields(show) {
        const fields = document.getElementById('newPetFields');
        fields.style.display = show ? 'block' : 'none';
    }

    // ======================
    // ADD PET
    // ======================
    async function addNewPet() {
        const petData = {
            nama_pet: document.getElementById('nama_pet').value,
            jenis_pet: document.getElementById('jenis_pet').value,
            ras: document.getElementById('ras').value,
            umur: document.getElementById('umur').value,
            jenis_kelamin: document.getElementById('jenis_kelamin').value,
            warna: document.getElementById('warna').value,
            alergi: document.getElementById('alergi').value,
            catatan_medis: document.getElementById('catatan_medis').value
        };

        const response = await fetch('/api/hewan/tambah', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(petData)
        });

        const data = await response.json();

        if (data.success) return data.pet_id;
        else throw new Error(data.error);
    }

    // ======================
    // ADD PENITIPAN
    // ======================
    async function addPenitipan(petId) {
        const penitipanData = {
            id_pet: petId,
            tgl_checkin: document.getElementById('tgl_checkin').value,
            tgl_checkout: document.getElementById('tgl_checkout').value,
            kamar: document.getElementById('kamar').value,
            layanan: getSelectedLayanan(),
            durasi: getDurasi(),
            total_biaya: getTotal()
        };


        const response = await fetch('/api/penitipan/tambah', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(penitipanData)
        });

        const data = await response.json();

        if (data.success) return data.penitipan_id;
        else throw new Error(data.error);
    }

    // ======================
    // UPDATE PENITIPAN
    // ======================
    async function updatePenitipan(penitipanId, petId) {
        const penitipanData = {
            id_pet: petId,
            tgl_checkin: document.getElementById('tgl_checkin').value,
            tgl_checkout: document.getElementById('tgl_checkout').value
        };

        const response = await fetch('/api/penitipan/update/' + penitipanId, {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + sessionToken,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(penitipanData)
        });

        const data = await response.json();

        if (data.success) return true;
        else throw new Error(data.error);
    }

    // ======================
    // AUTO-HITUNG DURASI
    // ======================
    function getDurasi() {
        const cin = document.getElementById('tgl_checkin').value;
        const cout = document.getElementById('tgl_checkout').value;

        if (!cin || !cout) return 0;

        const d1 = new Date(cin);
        const d2 = new Date(cout);

        const selisih = Math.ceil((d2 - d1) / (1000 * 3600 * 24));
        return selisih > 0 ? selisih : 0;
    }

    // ======================
    // HARGA KAMAR + LAYANAN
    // ======================
    const hargaKamar = {
        reguler: 25000,
        premium: 35000,
        vip: 50000
    };


    const hargaLayanan = {
        grooming: 40000,
        spa: 75000,
        kuku: 15000
    };

    function getSelectedLayanan() {
        const layananList = [];
        document.querySelectorAll('.layanan:checked').forEach(c => {
            layananList.push(c.value);
        });
        return layananList;
    }

    function getTotal() {
        const durasi = getDurasi();
        if (durasi <= 0) return 0;

        const kamar = document.getElementById('kamar')?.value || '';
        let total = hargaKamar[kamar] * durasi || 0;

        getSelectedLayanan().forEach(l => {
            total += hargaLayanan[l] || 0;
        });

        return total;
    }

    // ======================
    // UPDATE UI
    // ======================
    function updateUI() {
        const durasiField = document.getElementById('durasi');
        const totalField = document.getElementById('total');

        if (durasiField) durasiField.value = getDurasi() + ' hari';
        if (totalField) totalField.value = 'Rp ' + getTotal().toLocaleString('id-ID');
    }

    // ======================
    // SUBMIT HANDLER
    // ======================
    async function handleSubmit(e) {
        e.preventDefault();

        const petSelect = document.getElementById('pet_select');
        const selectedValue = petSelect.value;

        if (!selectedValue) {
            alert('Silakan pilih pet atau tambah pet baru.');
            return;
        }

        const checkin = document.getElementById('tgl_checkin').value;
        const checkout = document.getElementById('tgl_checkout').value;

        if (!checkin || !checkout) {
            alert('Tanggal check-in dan check-out harus diisi.');
            return;
        }

        if (getDurasi() <= 0) {
            alert('Tanggal checkout harus lebih besar dari check-in.');
            return;
        }

        try {
            let petId;

            if (selectedValue === 'new') {
                const namaPet = document.getElementById('nama_pet').value.trim();
                if (!namaPet) {
                    alert('Nama pet harus diisi.');
                    return;
                }

                petId = await addNewPet();
            } else {
                petId = parseInt(selectedValue);
            }

            if (editMode && editPenitipanId) {
                await updatePenitipan(editPenitipanId, petId);
                alert('Penitipan berhasil diperbarui!');
            } else {
                await addPenitipan(petId);
                alert('Penitipan berhasil disimpan!');
            }

            window.location.href = '/my';

        } catch (error) {
            alert('Error: ' + error.message);
        }
    }

    // ======================
    // INIT
    // ======================
    function init() {
        loadPets().then(function () {
            const id = getEditId();
            if (id) loadPenitipanData(id);
        });

        const today = new Date().toISOString().split('T')[0];
        document.getElementById('tgl_checkin').min = today;
        document.getElementById('tgl_checkout').min = today;

        document.getElementById('pet_select').addEventListener('change', function () {
            toggleNewPetFields(this.value === 'new');
        });

        // EVENT REALTIME HITUNG
        document.getElementById('tgl_checkin').addEventListener('change', updateUI);
        document.getElementById('tgl_checkout').addEventListener('change', updateUI);
        document.getElementById('kamar')?.addEventListener('change', updateUI);
        document.querySelectorAll('.layanan').forEach(l => {
            l.addEventListener('change', updateUI);
        });

        document.getElementById('titipForm').addEventListener('submit', handleSubmit);
        updateUI();
    }

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', init);
    else
        init();

})();
