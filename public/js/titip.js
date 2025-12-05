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

    // ======================
    // FETCH USER DATA
    // ======================
    function fetchUserData() {
        fetch('/api/auth/me', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + sessionToken
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (data.success && data.user) {
                var user = data.user;
                var userName = document.getElementById('user-name');
                var userAvatar = document.getElementById('user-avatar');
                
                if (userName) {
                    userName.textContent = user.nama_lengkap || 'Akun Saya';
                }
                if (userAvatar && user.foto_profil) {
                    userAvatar.src = user.foto_profil;
                }
            }
        })
        .catch(function(error) {
            console.error('Error fetching user data:', error);
        });
    }

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
    let hargaKamar = {
        reguler: 25000,
        premium: 35000,
        vip: 50000
    };

    let hargaLayanan = {
        grooming: 40000,
        spa: 75000,
        kuku: 15000
    };
    
    // Fetch packages and services from API
    function loadPaketAndLayanan() {
        // Fetch packages
        fetch('/api/paket', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.paket && data.paket.length > 0) {
                const kamarSelect = document.getElementById('kamar');
                if (kamarSelect) {
                    // Clear existing options except the first (placeholder)
                    kamarSelect.innerHTML = '<option value="">-- Pilih kamar --</option>';
                    
                    // Reset price map
                    hargaKamar = {};
                    
                    // Add options from database
                    data.paket.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p.nama_paket.toLowerCase();
                        opt.textContent = p.nama_paket + ' - Rp ' + p.harga_per_hari.toLocaleString('id-ID') + '/hari';
                        kamarSelect.appendChild(opt);
                        
                        // Store price
                        hargaKamar[p.nama_paket.toLowerCase()] = p.harga_per_hari;
                    });
                }
            }
        })
        .catch(err => console.warn('Could not load packages:', err));
        
        // Fetch services
        fetch('/api/layanan', {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.layanan && data.layanan.length > 0) {
                const layananContainer = document.querySelector('.input-group label:contains("Layanan Tambahan")');
                let targetDiv = layananContainer ? layananContainer.parentElement : null;
                
                // Find the div containing checkboxes
                if (!targetDiv) {
                    const labels = Array.from(document.querySelectorAll('.input-group label'));
                    for (let lbl of labels) {
                        if (lbl.textContent.includes('Layanan Tambahan')) {
                            targetDiv = lbl.parentElement;
                            break;
                        }
                    }
                }
                
                if (targetDiv) {
                    // Remove existing checkbox options
                    targetDiv.querySelectorAll('.checkbox-option').forEach(el => el.remove());
                    
                    // Reset price map
                    hargaLayanan = {};
                    
                    // Add checkboxes from database
                    data.layanan.forEach(l => {
                        const label = document.createElement('label');
                        label.className = 'checkbox-option';
                        
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'layanan';
                        checkbox.value = l.nama_layanan.toLowerCase().replace(/\s+/g, '_');
                        checkbox.addEventListener('change', updateUI);
                        
                        label.appendChild(checkbox);
                        label.appendChild(document.createTextNode(' ' + l.nama_layanan + ' - Rp ' + l.harga.toLocaleString('id-ID')));
                        
                        targetDiv.appendChild(label);
                        
                        // Store price
                        hargaLayanan[checkbox.value] = l.harga;
                    });
                }
            }
        })
        .catch(err => console.warn('Could not load services:', err));
    }

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
        fetchUserData();
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

        // Load packages and services from database
        loadPaketAndLayanan();

        // EVENT REALTIME HITUNG
        document.getElementById('tgl_checkin').addEventListener('change', updateUI);
        document.getElementById('tgl_checkout').addEventListener('change', updateUI);
        document.getElementById('kamar')?.addEventListener('change', updateUI);
        
        // Note: layanan checkboxes will be dynamically added by loadPaketAndLayanan
        // so we don't need to add listeners here

        document.getElementById('titipForm').addEventListener('submit', handleSubmit);
        updateUI();
    }

    if (document.readyState === 'loading')
        document.addEventListener('DOMContentLoaded', init);
    else
        init();

})();
