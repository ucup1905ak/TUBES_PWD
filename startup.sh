#!/bin/bash
# Azure App Service startup script for PHP
# This runs before nginx starts

echo "Starting custom initialization..."

# Azure managed nginx doesn't allow direct config replacement
# Instead, we rely on .htaccess or application-level routing

# Ensure PHP-FPM is running (usually already started by Azure)
if ! pgrep -x "php-fpm" > /dev/null; then
    echo "Starting PHP-FPM..."
    php-fpm -D
fi

echo "Initialization complete."
