<?php
/**
 * Environment Configuration Loader
 * Loads configuration from .env file
 */

function loadEnv($filePath = __DIR__ . '/../.env') {
    if (!file_exists($filePath)) {
        // If .env doesn't exist, use environment variables or defaults
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Skip comments
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parse KEY=VALUE format
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            
            // Remove quotes if present
            $value = trim($value, '"\'');
            
            // Set as environment variable if not already set
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

/**
 * Get environment variable with fallback
 */
function env($key, $default = null) {
    $value = $_ENV[$key] ?? getenv($key);
    return $value !== false ? $value : $default;
}

// Load environment variables
loadEnv();
