<?php
$
$conn = new mysqli("localhost", "root", "123", "pwd");

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
    // Convert to JSON
    // echo json_encode($data);
}else{
    $myJSON = json_encode($myObj);
    echo $myJSON;
}
?>