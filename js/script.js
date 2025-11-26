const API_URL = 'http://localhost/PWD/TUBES_PWD/backend/index.php';


async function fetchData() {
    const fieldName = document.getElementById("name");
    const fieldAge = document.getElementById("age");
    const res = await fetch(API_URL);
    const data = await res.json();
    
    fieldName.value = data.name;
    fieldAge.value = data.age;
}



