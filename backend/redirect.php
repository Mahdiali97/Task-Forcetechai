<?php

declare(strict_types=1);

require_once __DIR__ . '/utils/env.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/utils/short_code.php';
require_once __DIR__ . '/utils/json_response.php';
require_once __DIR__ . '/utils/shortener.php';

loadEnv(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '.env');

/**
 * Validate short code format: 6-8 alphanumeric characters
 */
function isValidShortCode(string $code): bool
{
    return preg_match('/^[A-Za-z0-9]{6,8}$/', $code) === 1;
}

/**
 * Look up original URL by short code
 */
function findOriginalUrl(PDO $pdo, string $shortCode): ?string
{
    $statement = $pdo->prepare('SELECT original_url FROM urls WHERE short_code = :short_code');
    $statement->execute(['short_code' => $shortCode]);
    $row = $statement->fetch();

    return $row ? (string) $row['original_url'] : null;
}

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = trim($path, '/');

$parts = explode('/', $path, 2);
$firstSegment = $parts[0] ?? '';
$secondSegment = $parts[1] ?? '';

// Handle API routes
if ($firstSegment === 'api') {
    if ($secondSegment === 'shorten.php') {
        require_once __DIR__ . '/api/shorten.php';
        return true;
    }
    if ($secondSegment === 'health.php') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'ok',
            'service' => 'url-shortener-backend',
            'message' => 'Health check passed',
        ]);
        return true;
    }
    jsonResponse(404, [
        'success' => false,
        'error' => 'API endpoint not found',
    ]);
}

// Handle root path
if ($firstSegment === '') {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'ok',
        'service' => 'url-shortener-backend',
        'message' => 'Backend is running',
    ]);
    return true;
}

// Handle short code redirect
if (!isValidShortCode($firstSegment)) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short Link Not Found</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 4rem auto; padding: 0 1rem; text-align: center; }
        h1 { color: #333; }
        .code { font-family: monospace; background: #f4f4f4; padding: 0.2rem 0.5rem; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Short Link Not Found</h1>
    <p>The short link <span class="code">' . htmlspecialchars($firstSegment, ENT_QUOTES, 'UTF-8') . '</span> does not exist or is invalid.</p>
    <p><a href="/">Create a new short link</a></p>
</body>
</html>';
    return true;
}

try {
    $pdo = getDatabaseConnection();
} catch (RuntimeException $e) {
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 4rem auto; padding: 0 1rem; text-align: center; }
    </style>
</head>
<body>
    <h1>Internal Server Error</h1>
    <p>Database configuration error. Please try again later.</p>
</body>
</html>';
    return true;
}

try {
    $originalUrl = findOriginalUrl($pdo, $firstSegment);
} catch (Throwable $exception) {
    error_log('URL redirect lookup failed: ' . $exception->getMessage());
    http_response_code(500);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 4rem auto; padding: 0 1rem; text-align: center; }
    </style>
</head>
<body>
    <h1>Internal Server Error</h1>
    <p>We could not look up this short link. Please try again later.</p>
</body>
</html>';
    return true;
}

if ($originalUrl === null) {
    http_response_code(404);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Short Link Not Found</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 4rem auto; padding: 0 1rem; text-align: center; }
        h1 { color: #333; }
        .code { font-family: monospace; background: #f4f4f4; padding: 0.2rem 0.5rem; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Short Link Not Found</h1>
    <p>The short link <span class="code">' . htmlspecialchars($firstSegment, ENT_QUOTES, 'UTF-8') . '</span> does not exist.</p>
    <p><a href="/">Create a new short link</a></p>
</body>
</html>';
    return true;
}

header('Location: ' . $originalUrl, true, 302);
return true;