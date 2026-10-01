<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once __DIR__ . '/short_code.php';

const SHORT_CODE_INSERT_MAX_ATTEMPTS = 8;

function getAppBaseUrl(): string
{
    $base = getenv('APP_BASE_URL');

    if ($base === false || trim($base) === '') {
        return 'http://localhost:8000';
    }

    return rtrim(trim($base), '/');
}

function isDuplicateKeyError(PDOException $exception): bool
{
    return isset($exception->errorInfo[1]) && (int) $exception->errorInfo[1] === 1062;
}

/**
 * Persist a new short code for $originalUrl and return the public short URL.
 * On UNIQUE collisions, generate a new code and retry. Other DB errors bubble up.
 */
function createShortenedUrl(string $originalUrl): string
{
    for ($attempt = 0; $attempt < SHORT_CODE_INSERT_MAX_ATTEMPTS; $attempt++) {
        $shortCode = generateShortCode();

        try {
            dbExecute(
                'INSERT INTO urls (short_code, original_url) VALUES (:short_code, :original_url)',
                [
                    'short_code' => $shortCode,
                    'original_url' => $originalUrl,
                ]
            );

            return getAppBaseUrl() . '/' . $shortCode;
        } catch (PDOException $exception) {
            if (isDuplicateKeyError($exception)) {
                continue;
            }

            throw $exception;
        }
    }

    throw new RuntimeException('Could not allocate a unique short code.');
}
