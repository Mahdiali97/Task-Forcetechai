<?php

declare(strict_types=1);

const MAX_ORIGINAL_URL_LENGTH = 2048;

/**
 * Validate a submitted long URL.
 * Returns an error message, or null when the URL is acceptable.
 */
function validateLongUrl(mixed $url): ?string
{
    if (!is_string($url)) {
        return 'Invalid or missing URL.';
    }

    $url = trim($url);

    if ($url === '') {
        return 'Invalid or missing URL.';
    }

    if (strlen($url) > MAX_ORIGINAL_URL_LENGTH) {
        return 'Invalid or missing URL.';
    }

    if (filter_var($url, FILTER_VALIDATE_URL) === false) {
        return 'Invalid or missing URL.';
    }

    $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

    if ($scheme !== 'http' && $scheme !== 'https') {
        return 'Invalid or missing URL.';
    }

    return null;
}
