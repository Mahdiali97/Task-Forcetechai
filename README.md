# URL Shortener

A web application that converts long URLs into short links.

## Technology stack

- Frontend: React (Vite)
- Backend: PHP
- Database: MySQL

## Current setup

- A React frontend with a placeholder URL form
- A PHP backend health-check endpoint
- A MySQL schema and PDO connection layer
- A cryptographically secure short-code generator

URL shortening, redirects, and frontend API calls are not implemented yet.

## Run the frontend

Prerequisites: Node.js (npm)

```bash
cd frontend
npm install
npm run dev
```

Open the local URL printed by Vite (typically `http://localhost:5173`).

## Test the PHP health endpoint

Prerequisites: PHP

From the project root:

```bash
php -S localhost:8000 -t backend
```

Then open:

- `http://localhost:8000/`
- `http://localhost:8000/api/health.php`

Both should return JSON with `"status": "ok"`.

## Database

Prerequisites: MySQL 8+ (CHECK constraints are enforced)

### Required environment variables

Copy `.env.example` to `.env` in the project root and set:

| Variable | Purpose |
| --- | --- |
| `DB_HOST` | MySQL host |
| `DB_PORT` | MySQL port (default `3306`) |
| `DB_NAME` | Database name (`url_shortener`) |
| `DB_USER` | MySQL user |
| `DB_PASSWORD` | MySQL password |

The PDO connection in `backend/config/database.php` reads these values. It is not called by the health endpoint yet.

### Create the database and tables

From the project root, run `schema.sql` as a MySQL user that can create databases:

```bash
mysql -u root -p < backend/database/schema.sql
```

On Windows PowerShell:

```powershell
Get-Content backend/database/schema.sql | mysql -u root -p
```

The script creates the `url_shortener` database if it does not exist, then creates the `urls` table.

If you already created an empty database named `url_shortener`, you can also run:

```bash
mysql -u root -p url_shortener < backend/database/schema.sql
```

## Test short-code generation

From the project root, run the utility as a CLI script (not an HTTP endpoint):

```bash
php backend/utils/short_code.php
```

It prints five sample codes. Including the file from PHP does not print anything.
