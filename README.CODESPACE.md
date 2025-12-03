# Codespaces / Devcontainer for this project

This repository contains a PHP (Apache) application and a small API in `src/`. The included devcontainer sets up a PHP+Apache workspace plus a MariaDB service for local development in GitHub Codespaces or with local Docker Compose.

Files added:
- `.devcontainer/Dockerfile` - builds a PHP 8.2 Apache image with common extensions
- `.devcontainer/docker-compose.yml` - brings up the `workspace` service and `db` (mariadb)
- `.devcontainer/devcontainer.json` - Codespaces/devcontainer configuration

Quick start (GitHub Codespaces):
1. Create a Codespace from this repo (GitHub web UI). The devcontainer will build automatically.
2. The web server is on port `80` (forwarded). The database is MariaDB on port `3306`.

Quick start (local Docker Compose):
1. From the repo root run:

```bash
docker compose -f .devcontainer/docker-compose.yml up --build -d
```

2. Visit `http://localhost/` to open the app in your browser.

DB credentials (defaults in compose file):
- host: `db` (from container) or `localhost` (from host via port 3306)
- database: `tubespwd`
- user: `appuser`
- password: `password`
- root password: `rootpass`

Security note: Change the default passwords for any shared Codespace or production usage. In GitHub Codespaces use repository or organization secrets instead of committing secrets to files.
