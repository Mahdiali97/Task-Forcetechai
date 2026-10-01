<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/utils/json_response.php';
require_once dirname(__DIR__) . '/utils/url_validator.php';
require_once dirname(__DIR__) . '/utils/shortener.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    jsonResponse(405, [
        'success' => false,
        'error' => 'Method not allowed.',
    ]);
}

$rawBody = file_get_contents('php://input');
$payload = json_decode($rawBody === false ? '' : $rawBody, true);

if (!is_array($payload) || !array_key_exists('url', $payload)) {
    jsonResponse(400, [
        'success' => false,
        'error' => 'Invalid or missing URL.',
    ]);
}

$validationError = validateLongUrl($payload['url']);

if ($validationError !== null) {
    jsonResponse(400, [
        'success' => false,
        'error' => $validationError,
    ]);
}

try {
    $shortUrl = createShortenedUrl(trim($payload['url']));
    jsonResponse(201, [
        'success' => true,
        'short_url' => $shortUrl,
    ]);
} catch (Throwable $exception) {
    error_log('URL shorten failed: ' . $exception->getMessage());
    jsonResponse(500, [
        'success' => false,
        'error' => 'Unable to shorten URL.',
    ]);
}