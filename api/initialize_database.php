<?php

function initDatabase($DB_HOST,
                    $DB_USER,
                    $DB_PASSWORD,
                    $DB_NAME,
                    $DB_PORT){
    $conn = new mysqli( $DB_HOST,
                        $DB_USER,
                        $DB_PASSWORD,
                        $DB_NAME,
                        $DB_PORT);
    if($conn == false) return false;
    $tableCheck = $conn->query("SHOW TABLES LIKE 'users'");
    $result = false;
    if ($tableCheck->num_rows == 0) {
        $createTableSQL = "CREATE TABLE users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            age INT NOT NULL
        )";
        $result =  $conn->query($createTableSQL);
    }
    $conn->close();
    return $result;
}









?>