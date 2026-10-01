<?php

declare(strict_types=1);

/**
 * Send a JSON response and exit.
 *
 * Sets the correct Content-Type header and HTTP status code,
 * then outputs JSON-encoded data.
 *
 * @param array $data       Response payload
 * @param int   $statusCode HTTP status code
 */
function jsonResponse(array $data, int $statusCode = 200): never
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Send a successful JSON response (200 OK by default).
 */
function jsonSuccess(array $data, int $statusCode = 200): never
{
    jsonResponse(['success' => true] + $data, $statusCode);
}

/**
 * Send an error JSON response.
 *
 * @param string      $message    Human-readable error message
 * @param int         $statusCode HTTP status code (4xx or 5xx)
 * @param array|null  $errors     Optional validation error details
 */
function jsonError(string $message, int $statusCode = 400, ?array $errors = null): never
{
    $payload = ['success' => false, 'error' => $message];
    if ($errors !== null) {
        $payload['errors'] = $errors;
    }
    jsonResponse($payload, $statusCode);
}

/**
 * Send a 400 Bad Request error response.
 */
function jsonBadRequest(string $message, ?array $errors = null): never
{
    jsonError($message, 400, $errors);
}

/**
 * Send a 404 Not Found error response.
 */
function jsonNotFound(string $message = 'Resource not found'): never
{
    jsonError($message, 404);
}

/**
 * Send a 500 Internal Server Error response.
 *
 * Does not expose internal exception details to the client.
 */
function jsonServerError(string $message = 'Internal server error'): never
{
    jsonError($message, 500);
}