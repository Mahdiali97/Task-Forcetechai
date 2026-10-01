# URL Shortener

A web application that converts long URLs into short links.

## Technology stack

- Frontend: React (Vite)
- Backend: PHP
- Database: MySQL (not connected yet)

## Current setup

This repository currently contains the project foundation only:

- A React frontend with a placeholder URL form
- A PHP backend health-check endpoint
- Folders reserved for future config, API, utilities, and database work

URL shortening, database operations, and authentication are not implemented yet.

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
