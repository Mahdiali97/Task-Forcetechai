<?php

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/redirect.php';
    return true;
}

if ($path === '/') {
    return false;
}

require_once __DIR__ . '/redirect.php';
return true;
