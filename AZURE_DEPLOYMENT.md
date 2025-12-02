# Azure Deployment Guide

## Azure Linux App Service Setup

Your app is running on **Azure Linux App Service** with nginx + PHP.

## 1. Configure Startup Command

In Azure Portal:
1. Go to your App Service: **tubes-pwd**
2. **Settings** → **Configuration** → **General settings**
3. Set **Startup Command** to:
   ```bash
   cp /home/site/wwwroot/nginx.conf /etc/nginx/sites-available/default && service nginx reload && php-fpm
   ```
4. Click **Save** and **Restart** the app

## 2. Setting Environment Variables

Azure App Service uses **Application Settings** instead of `.env` files. Follow these steps:

### 1. Navigate to Configuration
1. Go to [Azure Portal](https://portal.azure.com)
2. Select your App Service: **tubes-pwd-server**
3. Go to **Settings** → **Configuration** → **Application settings**

### 2. Add Environment Variables

Click **+ New application setting** and add these variables:

```
DB_HOST = tubes-pwd-server.mysql.database.azure.com
DB_PORT = 3306
DB_USER = jvcgurmasx
DB_PASSWORD = your_actual_password
DB_NAME = your_database_name
DB_USE_SSL = true
DB_SSL_CERT = DigiCertGlobalRootG2.crt.pem
```

### 3. Save and Restart
- Click **Save** at the top
- The app will automatically restart
- Environment variables will be available in `$_ENV`

## SSL Certificate Location

The SSL certificate `DigiCertGlobalRootG2.crt.pem` is already included in:
```
src/api/DigiCertGlobalRootG2.crt.pem
```

The code will automatically use it when `DB_USE_SSL=true`.

## How It Works

The `backend.php` now:
1. Checks `$_ENV` first (Azure Application Settings)
2. Falls back to `.env` file (for local development)
3. Uses SSL connection only when `DB_USE_SSL=true`
4. Works on Azure, cPanel, and localhost seamlessly

## Testing Locally

For local development, update your `.env`:
```env
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=123
DB_NAME=pwd
DB_USE_SSL=false
DB_SSL_CERT=
```

## Troubleshooting Azure MySQL Connection

If you get connection errors:

1. **Check Firewall Rules** in Azure MySQL:
   - Go to Azure MySQL resource
   - **Connection security** → **Firewall rules**
   - Add rule: `Allow Azure services` (0.0.0.0 - 0.0.0.0)

2. **Verify SSL is Required**:
   - In Azure MySQL → **Connection security**
   - Check "Enforce SSL connection"

3. **Check Application Logs**:
   ```bash
   az webapp log tail --name tubes-pwd-server --resource-group your-resource-group
   ```

4. **Test Connection** using the test endpoint:
   ```
   https://your-app.azurewebsites.net/test/env
   ```

## Deployment Files

### web.config (for URL rewriting)
Already created - Azure will use this automatically.

### .htaccess (for cPanel)
Already exists - cPanel will use this.

Both environments now work with the same codebase!
