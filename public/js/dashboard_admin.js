fetch('/api/admin/dashboard')
    .then(res => res.json())
    .then(data => {
        document.getElementById('stat-users').textContent = data.totalUsers;
        document.getElementById('stat-penitipan-aktif').textContent = data.totalPet;
        document.getElementById('stat-penitipan').textContent = data.totalPenitipan;
        document.getElementById('stat-income').textContent = 'Rp' + data.totalIncome.toLocaleString();
    })
    .catch(err => console.error('Dashboard fetch error:', err));