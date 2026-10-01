# URL Shortener

A web application that converts long URLs into short links.

## Technology stack

- Frontend: React (Vite)
- Backend: PHP
- Database: MySQL

## Current setup

- A React frontend with a functional URL shortening form
- A PHP backend health-check endpoint
- A PHP URL shortening API endpoint
- A PHP redirect handler for short links
- A MySQL schema and PDO connection layer
- A cryptographically secure short-code generator

All core features are implemented.

## Run the frontend

Prerequisites: Node.js (npm)

```bash
cd frontend
npm install
npm run dev
```

Open the local URL printed by Vite (typically `http://localhost:5173`).

The frontend dev server proxies `/api` requests to the PHP backend at `http://localhost:8000` via Vite's proxy configuration. Make sure the PHP backend is running (see below) before using the frontend.

### Frontend features

- Controlled form input with React state
- Loading state during API request
- Error handling for validation and server errors
- Success state with clickable shortened URL
- Copy to clipboard button with visual confirmation
- Empty input prevention
- Enter key submits the form
- Semantic HTML with accessible labels
- Responsive design (desktop, tablet, mobile)

### Vite proxy configuration

The `vite.config.js` includes a proxy for `/api` routes:

```js
server: {
  proxy: {
    '/api': 'http://localhost:8000',
  },
}
```

This allows the frontend to call `/api/shorten.php` directly without CORS issues during development.

## Test the PHP backend

Prerequisites: PHP

From the project root:

```bash
php -S localhost:8000 backend/redirect.php
```

The router script (`backend/redirect.php`) handles all routes:
- `/` — health check (JSON)
- `/api/health.php` — health check (JSON)
- `/api/shorten.php` — create short URL (POST, JSON)
- `/:shortCode` — redirect to original URL (GET)

Then test:

- `http://localhost:8000/` — returns JSON with `"status": "ok"`
- `http://localhost:8000/api/health.php` — returns JSON with `"status": "ok"`

## URL Shortening API

### Endpoint

```
POST /api/shorten.php
```

### Request

Content-Type: `application/json`

```json
{
  "url": "https://www.example.com/very/long/url/with/query?param=value"
}
```

### Response

#### 201 Created — URL successfully shortened

```json
{
  "success": true,
  "short_url": "http://localhost:8000/JXie23"
}
```

#### 400 Bad Request — Invalid or missing URL

```json
{
  "success": false,
  "error": "Missing \"url\" field"
}
```

Other 400 errors:
- `URL cannot be empty`
- `Invalid URL format`
- `Only HTTP and HTTPS URLs are allowed`

#### 405 Method Not Allowed

```json
{
  "success": false,
  "error": "Method not allowed"
}
```

#### 500 Internal Server Error — Unexpected server/database error

```json
{
  "success": false,
  "error": "Internal server error"
}
```

### Validation behavior

| Input | Result |
|-------|--------|
| Missing `url` field | 400 |
| Empty `url` string | 400 |
| Whitespace-only `url` | 400 |
| Invalid URL format (e.g. `not-a-url`) | 400 |
| Non-HTTP scheme (e.g. `ftp://example.com`) | 400 |
| Valid `http://` or `https://` URL | 201 |

The short code is a 6–8 character random string using `[A-Za-z0-9]`.
Collisions are handled automatically by retrying up to 10 times.
The base URL for the short link comes from the `APP_BASE_URL` environment variable (default: `http://localhost:8000`).

### Test with curl

```bash
# Valid URL
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url": "https://www.google.com/search?q=force-tech"}'

# Empty URL
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url": ""}'

# Missing URL field
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{}'

# Invalid URL
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url": "not-a-url"}'

# Non-HTTP URL
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url": "ftp://example.com/file.txt"}'
```

## URL Redirect

### Endpoint

```
GET /:shortCode
```

Where `:shortCode` is the 6–8 character code returned by the shortening API (e.g., `JXie23`).

### Behavior

| Input | Result |
|-------|--------|
| Valid existing short code | 302 Found — redirects to original URL |
| Valid non-existent short code | 404 Not Found — HTML page |
| Invalid format (not 6-8 alphanumeric) | 404 Not Found — HTML page |

### Test with curl

```bash
# Create a short URL first
curl -X POST http://localhost:8000/api/shorten.php \
  -H "Content-Type: application/json" \
  -d '{"url": "https://www.example.com/very/long/path"}'

# Response: {"success":true,"short_url":"http://localhost:8000/Ab3xY9"}

# Test redirect (follows redirect with -L)
curl -L http://localhost:8000/Ab3xY9

# Test redirect without following (shows 302)
curl -v http://localhost:8000/Ab3xY9

# Test nonexistent short code
curl -v http://localhost:8000/nonexistent123

# Test malformed short code (contains hyphen)
curl -v http://localhost:8000/invalid-code
```

### Response details

- **Valid short code**: Returns HTTP 302 with `Location` header set to the original URL. Browser will automatically redirect.
- **Not found**: Returns HTTP 404 with a user-friendly HTML page indicating the short link was not found.
- **Invalid format**: Returns HTTP 404 with a user-friendly HTML page indicating the short link format is invalid.

The redirect only goes to URLs stored in the database — never to arbitrary user input.

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
| `APP_BASE_URL` | Base URL for generated short links (default `http://localhost:8000`) |

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

## Shorten API (Production/Apache)

For Apache, routing is provided in `backend/.htaccess`. For the PHP dev server, `backend/router.php` routes the request.

Start the PHP built-in server from the project root:

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

