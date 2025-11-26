// API Configuration
const API_BASE_URL = 'http://localhost/PWD/TUBES_PWD/backend/index.php';

// Make API request using Fetch API
async function makeRequest() {
    const url = document.querySelector('input[name="url"]').value;
    const method = document.querySelector('input[name="method"]:checked')?.value || 'GET';
    const jsonInput = document.querySelector('input[name="json"]').value;
    
    try {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json'
            }
        };
        
        // Add body for POST/PUT requests
        if ((method === 'POST' || method === 'PUT') && jsonInput) {
            options.body = JSON.stringify(JSON.parse(jsonInput));
        }
        
        const response = await fetch(url, options);
        const data = await response.json();
        
        return {
            status: response.status,
            data: data
        };
    } catch (error) {
        console.error('Request error:', error);
        return {
            error: error.message
        };
    }
}

// Handle button click
async function handleClick() {
    try {
        const result = await makeRequest();
        console.log('Response:', result);
        
        // Display response in alert
        alert('Status: ' + (result.status || 'Error') + '\n\nResponse:\n' + JSON.stringify(result.data || result.error, null, 2));
    } catch (error) {
        console.error('Error:', error);
        alert('Error: ' + error.message);
    }
}

// Make function globally available
window.handleClick = handleClick;

