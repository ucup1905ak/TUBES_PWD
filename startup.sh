#!/bin/bash

# Azure App Service custom startup script
# Copy custom nginx config to the correct location for Azure Linux PHP

# Azure uses /etc/nginx/sites-enabled/default
cat > /etc/nginx/sites-enabled/default << 'EOF'
server {
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot;
    index index.php index.html;
    server_name _;
    port_in_redirect off;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\. {
        deny all;
    }
}
EOF

echo "Custom nginx config applied"
