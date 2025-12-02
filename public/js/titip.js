// /public/js/titip.js
// Form Penitipan & Pet management

(function() {
    'use strict';
    
    // Check session
    const sessionToken = localStorage.getItem('session_token');
    const expiresAt = localStorage.getItem('session_expires_at');
    
    if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
        window.location.href = '/login';
        return;
    }
    
    let userPets = [];
    
    // Load user's pets
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
        
        // Clear existing options except first two
        while (select.options.length > 2) {
            select.remove(2);
        }
        
        // Add user's pets
        userPets.forEach(function(pet) {
            const option = document.createElement('option');
            option.value = pet.id_pet;
            option.textContent = pet.nama_pet + (pet.jenis_pet ? ' (' + pet.jenis_pet + ')' : '');
            select.appendChild(option);
        });
    }
    
    function toggleNewPetFields(show) {
        const fields = document.getElementById('newPetFields');
        if (fields) {
            fields.style.display = show ? 'block' : 'none';
        }
    }
    
    // Add new pet
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
        
        if (data.success) {
            return data.pet_id;
        } else {
            throw new Error(data.error || 'Failed to add pet');
        }
    }
    
    // Add penitipan
    async function addPenitipan(petId) {
        const penitipanData = {
            id_pet: petId,
            tgl_checkin: document.getElementById('tgl_checkin').value,
            tgl_checkout: document.getElementById('tgl_checkout').value
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
        
        if (data.success) {
            return data.penitipan_id;
        } else {
            throw new Error(data.details ? data.details.join(', ') : (data.error || 'Failed to add penitipan'));
        }
    }
    
    // Handle form submit
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
        
        try {
            let petId;
            
            if (selectedValue === 'new') {
                // Validate new pet fields
                const namaPet = document.getElementById('nama_pet').value.trim();
                if (!namaPet) {
                    alert('Nama pet harus diisi.');
                    return;
                }
                
                // Add new pet first
                petId = await addNewPet();
            } else {
                petId = parseInt(selectedValue);
            }
            
            // Add penitipan
            await addPenitipan(petId);
            
            alert('Penitipan berhasil disimpan!');
            window.location.href = '/my';
            
        } catch (error) {
            alert('Error: ' + error.message);
        }
    }
    
    // Initialize
    function init() {
        loadPets();
        
        // Set minimum date to today
        const today = new Date().toISOString().split('T')[0];
        const checkinInput = document.getElementById('tgl_checkin');
        const checkoutInput = document.getElementById('tgl_checkout');
        
        if (checkinInput) checkinInput.min = today;
        if (checkoutInput) checkoutInput.min = today;
        
        // Pet select change handler
        const petSelect = document.getElementById('pet_select');
        if (petSelect) {
            petSelect.addEventListener('change', function() {
                toggleNewPetFields(this.value === 'new');
            });
        }
        
        // Form submit handler
        const form = document.getElementById('titipForm');
        if (form) {
            form.addEventListener('submit', handleSubmit);
        }
    }
    
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
