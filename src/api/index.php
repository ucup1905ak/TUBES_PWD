<?php
$DB_HOST="localhost";
$DB_PORT=3306;
$DB_USER="root";
$DB_PASSWORD="123";
$DB_NAME="pwd";
include 'initialize_database.php';


initDatabase($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT);

$conn = new mysqli( $DB_HOST,
                    $DB_USER,
                    $DB_PASSWORD,
                    $DB_NAME,
                    $DB_PORT);

$myObj = new stdClass();
$myObj->name = "UCUP";
$myObj->age = 19;


if($_SERVER["REQUEST_METHOD"] == "POST"){
    $input = file_get_contents('php://input');
    header('Content-Type: application/json');
    
    // Parse the input as query string
    parse_str($input, $data);
    $myObj->age = $data['age'];
    $myObj->name = $data['name'];
    //error handling if either of value is null or 0
    if (empty($myObj->name) || empty($myObj->age)) {
        http_response_code(400);
        echo json_encode(['error' => 'Name and age must not be empty or zero.']);
        exit;
    }
    //insert into database
    $stmt = $conn->prepare("INSERT INTO users (name, age) VALUES (?, ?)");
    $stmt->bind_param("si", $myObj->name, $myObj->age);
    $stmt->execute();
    $stmt->close();
    // Convert to JSON
    // echo json_encode($data);
}else{
    // fetch all objects from database;
    $result = $conn->query("SELECT name, age FROM users ORDER BY id ASC");
    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = [
                'name' => $row['name'],
                'age' => (int)$row['age']
            ];
        }
    }
    header('Content-Type: application/json');
    echo json_encode($users);
}
?>