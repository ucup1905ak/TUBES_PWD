const API_URL = 'http://localhost/PWD/TUBES_PWD/api/index.php';


async function fetchData() {    

    const DATA = document.getElementById("DATA");
    const key = document.getElementById("searchName");

    const res = await fetch(API_URL);
    const data = await res.json();
    const searchKey = key ? key.value.trim().toLowerCase() : '';

    // If no search key, show all data
    if (!searchKey) {
        if (!Array.isArray(data) || data.length === 0) {
            DATA.textContent = "No users found.";
            return;
        }
        DATA.innerHTML = data.map(u => `Name: ${u.name}, Age: ${u.age}`).join('<br>');
        return;
    }

    // Search with key
    if (!Array.isArray(data) || data.length === 0) {
        DATA.textContent = "No users to search.";
        return;
    }

    // Exact match first
    const exact = data.find(u => u.name && u.name.toLowerCase() === searchKey);
    if (exact) {
        DATA.textContent = `Name: ${exact.name}, Age: ${exact.age}`;
        return;
    }

    // Fallback to partial matches
    const matches = data.filter(u => u.name && u.name.toLowerCase().includes(searchKey));
    if (matches.length > 0) {
        DATA.innerHTML = matches.map(u => `Name: ${u.name}, Age: ${u.age}`).join('<br>');
        return;
    }

    DATA.textContent = "User not found.";
}



