#!/bin/bash

# Azure App Service startup script for PHP with custom routing
echo "Configuring nginx for custom routing..."

# Copy custom nginx configuration
cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-enabled/default

# Test nginx configuration
nginx -t

# Reload nginx
nginx -s reload

echo "Nginx configuration updated successfully"
