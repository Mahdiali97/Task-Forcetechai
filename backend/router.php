<?php

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if (strpos($path, '/api/') === 0) {
    // Serve API requests normally
    return false;
}

if ($path === '/') {
    return false;
}

// Check for short code pattern
$code = ltrim($path, '/');
if (preg_match('/^[a-zA-Z0-9]{6,8}$/', $code)) {
    $_GET['code'] = $code;
    include __DIR__ . '/redirect.php';
    exit;
}

return false;
