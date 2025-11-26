const API_URL = 'http://localhost/PWD/TUBES_PWD/backend/index.php';


async function fetchData() {
    const DATA = document.getElementById("DATA");
    const key = document.getElementById("searchName");

    const res = await fetch(API_URL);
    const data = await res.json();
    const keyElement = key || document.querySelector('input[name="searchName"]');
    const searchKey = (keyElement && keyElement.value) ? keyElement.value.trim().toLowerCase() : '';

    if (searchKey) {
        if (!Array.isArray(data)) {
            DATA.textContent = "No users to search.";
            return;
        }

        // exact match first
        const exact = data.find(u => u.name && u.name.toLowerCase() === searchKey);
        if (exact) {
            DATA.textContent = `Name: ${exact.name}, Age: ${exact.age}`;
            return;
        }

        // fallback to partial matches
        const matches = data.filter(u => u.name && u.name.toLowerCase().includes(searchKey));
        if (matches.length > 0) {
            DATA.textContent = matches.map(u => `Name: ${u.name}, Age: ${u.age}`).join('\n');
            return;
        }

        DATA.textContent = "User not found.";
        return;
    }

    // if (!Array.isArray(data)) {
    //     DATA.textContent = JSON.stringify(data, null, 2);
    // } else if (data.length === 0) {
    //     DATA.textContent = 'No users found.';
    // } else {
    //     DATA.textContent = data.map(u => `Name: ${u.name}, Age: ${u.age}`).join('\n');
    // }
   
}



