#!/bin/bash
# Azure App Service startup script for nginx + PHP

# Copy custom nginx config
if [ -f /home/site/wwwroot/nginx.conf ]; then
    cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default
    service nginx reload
fi

# Start PHP-FPM
php-fpm -D

# Keep container running
nginx -g "daemon off;"
