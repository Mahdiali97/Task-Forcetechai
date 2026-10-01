<?php

declare(strict_types=1);

require_once __DIR__ . '/config/database.php';

$shortCode = $_GET['code'] ?? '';

// 1. Validate short code format (alphanumeric, 6-8 chars)
if (!preg_match('/^[a-zA-Z0-9]{6,8}$/', $shortCode)) {
    http_response_code(404);
    echo 'URL not found.';
    exit;
}

try {
    // 2. Query database for original_url
    $stmt = dbExecute('SELECT original_url FROM urls WHERE short_code = :code', ['code' => $shortCode]);
    $row = $stmt->fetch();

    if ($row) {
        // 3. Redirect
        header('Location: ' . $row['original_url'], true, 302);
        exit;
    } else {
        // 4. Not found
        http_response_code(404);
        echo 'URL not found.';
        exit;
    }
} catch (Throwable $e) {
    error_log('Redirect error: ' . $e->getMessage());
    http_response_code(500);
    echo 'Internal server error.';
    exit;
}
