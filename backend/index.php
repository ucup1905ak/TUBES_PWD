
<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Simple routing
$request_method = $_SERVER['REQUEST_METHOD'];
$request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = array_filter(explode('/', $request_path));

// Get the endpoint from query parameter or URL path
$endpoint = isset($_GET['endpoint']) ? $_GET['endpoint'] : end($path_parts);

// Route requests
switch ($endpoint) {
    case 'users':
        handleUsers($request_method);
        break;
    case 'pets':
        handlePets($request_method);
        break;
    case 'bookings':
        handleBookings($request_method);
        break;
    default:
        http_response_code(404);
        echo json_encode(['message' => 'Endpoint not found']);
        break;
}

// User management endpoints
function handleUsers($method) {
    switch ($method) {
        case 'GET':
            // Get all users
            echo json_encode([
                'status' => 'success',
                'data' => [
                    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'phone' => '08123456789'],
                    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'phone' => '08987654321']
                ]
            ]);
            break;
        case 'POST':
            // Create new user
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['name']) && isset($input['email'])) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'User created successfully',
                    'data' => ['id' => 3, 'name' => $input['name'], 'email' => $input['email']]
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
    }
}

// Pet management endpoints
function handlePets($method) {
    switch ($method) {
        case 'GET':
            // Get all pets
            echo json_encode([
                'status' => 'success',
                'data' => [
                    ['id' => 1, 'user_id' => 1, 'name' => 'Buddy', 'type' => 'Dog', 'breed' => 'Golden Retriever'],
                    ['id' => 2, 'user_id' => 1, 'name' => 'Whiskers', 'type' => 'Cat', 'breed' => 'Persian']
                ]
            ]);
            break;
        case 'POST':
            // Create new pet
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['user_id']) && isset($input['name']) && isset($input['type'])) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Pet added successfully',
                    'data' => ['id' => 3, 'user_id' => $input['user_id'], 'name' => $input['name'], 'type' => $input['type']]
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
    }
}

// Booking management endpoints
function handleBookings($method) {
    switch ($method) {
        case 'GET':
            // Get all bookings
            echo json_encode([
                'status' => 'success',
                'data' => [
                    ['id' => 1, 'user_id' => 1, 'pet_id' => 1, 'check_in' => '2025-12-01', 'check_out' => '2025-12-05', 'status' => 'confirmed'],
                    ['id' => 2, 'user_id' => 2, 'pet_id' => 2, 'check_in' => '2025-12-10', 'check_out' => '2025-12-12', 'status' => 'pending']
                ]
            ]);
            break;
        case 'POST':
            // Create new booking
            $input = json_decode(file_get_contents("php://input"), true);
            if (isset($input['user_id']) && isset($input['pet_id']) && isset($input['check_in']) && isset($input['check_out'])) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Booking created successfully',
                    'data' => [
                        'id' => 3,
                        'user_id' => $input['user_id'],
                        'pet_id' => $input['pet_id'],
                        'check_in' => $input['check_in'],
                        'check_out' => $input['check_out'],
                        'status' => 'pending'
                    ]
                ]);
            } else {
                http_response_code(400);
                echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
            }
            break;
        default:
            http_response_code(405);
            echo json_encode(['message' => 'Method not allowed']);
    }
}
?>

<?php
/**
 * PetCareHub API Documentation
 * =============================
 * 
 * This is a simple RESTful API for the PetCareHub pet care booking system.
 * All responses are in JSON format.
 * 
 * BASE URL: http://localhost/PWD/TUBES_PWD/backend/index.php
 * 
 * =============================
 * USERS ENDPOINTS
 * =============================
 * 
 * 1. GET /users
 *    Description: Retrieve all users
 *    Method: GET
 *    Request: No parameters required
 *    Response: 
 *    {
 *      "status": "success",
 *      "data": [
 *        {
 *          "id": 1,
 *          "name": "John Doe",
 *          "email": "john@example.com",
 *          "phone": "08123456789"
 *        }
 *      ]
 *    }
 * 
 * 2. POST /users
 *    Description: Create a new user
 *    Method: POST
 *    Content-Type: application/json
 *    Request Body:
 *    {
 *      "name": "John Doe",
 *      "email": "john@example.com",
 *      "phone": "08123456789"
 *    }
 *    Response:
 *    {
 *      "status": "success",
 *      "message": "User created successfully",
 *      "data": {
 *        "id": 3,
 *        "name": "John Doe",
 *        "email": "john@example.com"
 *      }
 *    }
 *    Required Fields: name, email
 *    Status Codes: 201 Created, 400 Bad Request
 * 
 * =============================
 * PETS ENDPOINTS
 * =============================
 * 
 * 1. GET /pets
 *    Description: Retrieve all pets
 *    Method: GET
 *    Request: No parameters required
 *    Response:
 *    {
 *      "status": "success",
 *      "data": [
 *        {
 *          "id": 1,
 *          "user_id": 1,
 *          "name": "Buddy",
 *          "type": "Dog",
 *          "breed": "Golden Retriever"
 *        }
 *      ]
 *    }
 * 
 * 2. POST /pets
 *    Description: Add a new pet
 *    Method: POST
 *    Content-Type: application/json
 *    Request Body:
 *    {
 *      "user_id": 1,
 *      "name": "Buddy",
 *      "type": "Dog",
 *      "breed": "Golden Retriever"
 *    }
 *    Response:
 *    {
 *      "status": "success",
 *      "message": "Pet added successfully",
 *      "data": {
 *        "id": 3,
 *        "user_id": 1,
 *        "name": "Buddy",
 *        "type": "Dog"
 *      }
 *    }
 *    Required Fields: user_id, name, type
 *    Status Codes: 201 Created, 400 Bad Request
 * 
 * =============================
 * BOOKINGS ENDPOINTS
 * =============================
 * 
 * 1. GET /bookings
 *    Description: Retrieve all bookings
 *    Method: GET
 *    Request: No parameters required
 *    Response:
 *    {
 *      "status": "success",
 *      "data": [
 *        {
 *          "id": 1,
 *          "user_id": 1,
 *          "pet_id": 1,
 *          "check_in": "2025-12-01",
 *          "check_out": "2025-12-05",
 *          "status": "confirmed"
 *        }
 *      ]
 *    }
 * 
 * 2. POST /bookings
 *    Description: Create a new booking
 *    Method: POST
 *    Content-Type: application/json
 *    Request Body:
 *    {
 *      "user_id": 1,
 *      "pet_id": 1,
 *      "check_in": "2025-12-01",
 *      "check_out": "2025-12-05"
 *    }
 *    Response:
 *    {
 *      "status": "success",
 *      "message": "Booking created successfully",
 *      "data": {
 *        "id": 3,
 *        "user_id": 1,
 *        "pet_id": 1,
 *        "check_in": "2025-12-01",
 *        "check_out": "2025-12-05",
 *        "status": "pending"
 *      }
 *    }
 *    Required Fields: user_id, pet_id, check_in, check_out
 *    Status Codes: 201 Created, 400 Bad Request
 * 
 * =============================
 * HTTP STATUS CODES
 * =============================
 * 200 OK - Request successful
 * 400 Bad Request - Missing or invalid parameters
 * 404 Not Found - Endpoint does not exist
 * 405 Method Not Allowed - Invalid HTTP method for endpoint
 * 
 * =============================
 * CORS HEADERS
 * =============================
 * All endpoints support CORS with the following headers:
 * Access-Control-Allow-Origin: *
 * Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
 * Access-Control-Allow-Headers: Content-Type
 * 
 * =============================
 * USAGE EXAMPLE (JavaScript/Fetch)
 * =============================
 * 
 * // Get all users
 * fetch('http://localhost/PWD/TUBES_PWD/backend/index.php?endpoint=users')
 *   .then(res => res.json())
 *   .then(data => console.log(data));
 * 
 * // Create a new user
 * fetch('http://localhost/PWD/TUBES_PWD/backend/index.php?endpoint=users', {
 *   method: 'POST',
 *   headers: {
 *     'Content-Type': 'application/json'
 *   },
 *   body: JSON.stringify({
 *     name: 'John Doe',
 *     email: 'john@example.com'
 *   })
 * })
 *   .then(res => res.json())
 *   .then(data => console.log(data));
 * 
 */
?>



