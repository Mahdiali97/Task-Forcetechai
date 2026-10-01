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
- A `POST /api/shorten.php` endpoint that stores a short link

Redirects and frontend API calls are not implemented yet.

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
php -S localhost:8000 -t backend backend/router.php
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
| `APP_BASE_URL` | Public base used to build short URLs (e.g. `http://localhost:8000`) |

The PDO connection in `backend/config/database.php` reads these values.

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

## Shorten API

Start the PHP built-in server from the project root. Using `router.php` allows local testing of short URL redirects:

```bash
php -S localhost:8000 -t backend backend/router.php
```

### Endpoint

`POST /api/shorten.php`

`Content-Type: application/json`

### Request

```json
{
  "url": "https://www.google.com/search?q=force-tech"
}
```

### Success (201)

```json
{
  "success": true,
  "short_url": "http://localhost:8000/JXie23"
}
```

The path after the base URL is the generated short code.

### Redirects (302)

Visiting the short code path (e.g. `http://localhost:8000/JXie23`) will return an HTTP 302 redirect to the original URL if found, or an HTTP 404 text response if the short code does not exist. This is handled by `backend/redirect.php`.

For Apache, routing is provided in `backend/.htaccess`. For the PHP dev server, `backend/router.php` routes the request.

### Validation errors (400)

Returned when the body is not JSON, `url` is missing, empty, not a valid URL, longer than 2048 characters, or not `http`/`https` (for example `ftp://example.com`).

```json
{
  "success": false,
  "error": "Invalid or missing URL."
}
```

### Server errors (500)

Unexpected database or runtime failures return a generic message. Database details are not included in the response.

```json
{
  "success": false,
  "error": "Unable to shorten URL."
}
```

### Example

```bash
curl -i -X POST http://localhost:8000/api/shorten.php ^
  -H "Content-Type: application/json" ^
  -d "{\"url\":\"https://www.google.com/search?q=force-tech\"}"
```

On Unix:

```bash
curl -i -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url":"https://www.google.com/search?q=force-tech"}'
```

