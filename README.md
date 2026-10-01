# URL Shortener

## Overview

URL Shortener is a small React and PHP application that converts a long HTTP or HTTPS URL into a short link. The short code is stored in MySQL, and visiting the generated link redirects to the stored destination.

## Features

- Shorten long URLs
- Cryptographically secure random short-code generation
- MySQL persistence
- Short URL redirection
- HTTP/HTTPS URL validation
- Controlled validation and server error handling
- Copy shortened URL to the clipboard
- Responsive, accessible single-page interface

## Tech Stack

**Frontend**

- React
- Vite

**Backend**

- PHP
- PDO

**Database**

- MySQL

## Architecture

Shortening requests flow through the application as follows:

```text
React frontend
    |
    v
PHP API
    |
    v
MySQL
```

Redirect requests follow this path:

```text
Short URL
    |
    v
PHP redirect handler
    |
    v
MySQL lookup
    |
    v
Original URL
```

The Vite development server proxies `/api` requests to the PHP server during local development.

## Project Structure

```text
.
├── backend/
│   ├── api/
│   │   ├── health.php          Health endpoint
│   │   └── shorten.php         URL-shortening endpoint
│   ├── config/
│   │   └── database.php        PDO connection and database helpers
│   ├── database/
│   │   └── schema.sql          MySQL database and table definition
│   ├── utils/
│   │   ├── env.php             Local environment loader
│   │   ├── json_response.php   Consistent JSON responses
│   │   ├── short_code.php       Secure short-code generation
│   │   ├── shortener.php        Persistence and collision retries
│   │   └── url_validator.php    URL validation rules
│   ├── index.php                Root health response
│   ├── redirect.php             API routing and short-link redirects
│   └── router.php                PHP development-server router
├── frontend/
│   ├── src/
│   │   ├── services/api.js      Frontend API client
│   │   ├── App.jsx              Main form and result flow
│   │   ├── App.css              Component styles
│   │   └── index.css            Global styles
│   ├── package.json              Frontend scripts and dependencies
│   └── vite.config.js            Vite configuration and API proxy
├── .env.example                  Environment variable template
└── README.md                     Project documentation
```

## Requirements

Install the following tools locally:

- PHP with the PDO MySQL extension
- MySQL
- Node.js
- npm

The schema uses MySQL features including `CHECK` constraints. Use a MySQL version that enforces the constraints defined in `backend/database/schema.sql`.

## Installation

### 1. Clone the repository

```bash
git clone <repository-url>
cd Task-Forcetechai
```

Replace `<repository-url>` with the repository URL supplied for the screening task.

### 2. Install frontend dependencies

```bash
cd frontend
npm install
cd ..
```

### 3. Create the MySQL database

Create a MySQL database user with permission to create and use the application database. The default local values use the database name `url_shortener` and user `root`.

### 4. Run the schema

From the project root, run:

```bash
mysql -u root -p < backend/database/schema.sql
```

On Windows PowerShell:

```powershell
Get-Content backend/database/schema.sql | mysql -u root -p
```

### 5. Configure environment variables

Copy the template:

```powershell
Copy-Item .env.example .env
```

Edit `.env` with the credentials for your local MySQL installation. Never commit `.env`.

### 6. Start the PHP backend

From the project root:

```bash
php -S localhost:8000 -t backend backend/router.php
```

The backend serves the API and short-link redirects on `http://localhost:8000`.

### 7. Start the React development server

In a second terminal:

```bash
cd frontend
npm run dev
```

Open the local URL printed by Vite, usually `http://localhost:5173`. The frontend proxies `/api` requests to the PHP backend at port `8000`.

## Environment Variables

Set these values in the root `.env` file:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=url_shortener
DB_USER=
DB_PASSWORD=
APP_BASE_URL=http://localhost:8000
```

- `DB_HOST`: MySQL host.
- `DB_PORT`: MySQL port.
- `DB_NAME`: Application database name.
- `DB_USER`: MySQL username.
- `DB_PASSWORD`: MySQL password.
- `APP_BASE_URL`: Base URL used in generated short links.

`.env` and other local environment files are ignored by Git. `.env.example` contains only placeholders and safe local defaults.

## API Documentation

### `POST /api/shorten.php`

Content type:

```text
application/json
```

Request:

```json
{
  "url": "https://example.com"
}
```

### Success response: `201 Created`

```json
{
  "success": true,
  "short_url": "http://localhost:8000/Ab3xY9"
}
```

### Validation error response: `400 Bad Request`

The same controlled response is used for an empty URL, missing `url`, invalid JSON, an invalid URL, an unsupported scheme, or a URL longer than 2048 characters.

```json
{
  "success": false,
  "error": "Invalid or missing URL."
}
```

### Method error response: `405 Method Not Allowed`

```json
{
  "success": false,
  "error": "Method not allowed."
}
```

### Server error response: `500 Internal Server Error`

Database and unexpected runtime details are logged server-side and are not returned to clients.

```json
{
  "success": false,
  "error": "Unable to shorten URL."
}
```

## Redirect

A generated short code can be visited directly:

```text
GET /abc123
```

The PHP redirect handler validates the code, looks up the stored destination in MySQL, and returns an HTTP `302` redirect. Existing codes redirect to their original URL. Valid but missing codes return `404`; malformed codes also return `404` without querying the database.

## Testing

The following manual cases were exercised against the local PHP server:

- HTTPS URL: returns `201` and a short URL.
- HTTP URL: returns `201` and a short URL.
- Empty, missing, invalid, `javascript:`, and invalid JSON inputs: return `400`.
- Empty request body: returns `400`.
- Wrong HTTP method: returns `405`.
- Existing short code: returns `302` to the stored URL.
- Missing or malformed short code: returns `404`.
- Forced duplicate-key insert: collision retry succeeds with `201`.
- Database authentication failure: returns a generic `500` without database details.
- Repeated submissions: produce distinct short codes; the frontend disables the submit button while loading.
- Long valid URL: succeeds within the 2048-character limit.
- URL over 2048 characters: returns `400`.
- Clipboard success and failure states were handled in the frontend.

Available frontend checks:

```bash
cd frontend
npm run lint
npm run build
```

PHP files can be syntax-checked with:

```bash
Get-ChildItem backend -Filter *.php -Recurse | ForEach-Object { php -l $_.FullName }
```

## Security Considerations

- PDO prepared statements are used for all database queries with user data.
- URLs must be valid and must use the `http` or `https` scheme.
- Original URLs are limited to 2048 characters.
- Short codes use a cryptographically secure random generator.
- The database unique constraint and retry loop handle short-code collisions.
- Database credentials are loaded from environment variables rather than source code.
- `.env` is ignored and `.env.example` contains no real credentials.
- Error responses are controlled and do not expose database or PHP internals.
- Redirects use only URLs previously validated and stored by the application.
- React escapes displayed URL values, and redirect error pages HTML-escape path values.

## Git Development History

No release history is inferred or fabricated in this document. Use `git log` to inspect the repository's actual development history.
