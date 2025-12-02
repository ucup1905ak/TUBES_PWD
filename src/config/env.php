<?php
function loadEnvToArray($filePath = __DIR__ . '/../.env') {
    $env = [];
    if (!file_exists($filePath)) {
        return $env;
    }
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, "\"'");
            $env[$key] = $value;
        }
    }
    return $env;
}

// Usage:
// $envObj = loadEnvToArray();
// echo json_encode($envObj, JSON_PRETTY_PRINT);
