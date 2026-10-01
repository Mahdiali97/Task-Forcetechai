<?php

declare(strict_types=1);

/**
 * Load KEY=VALUE pairs from a .env file into the process environment.
 * Existing environment variables are not overwritten.
 */
function loadEnv(string $path): void
{
    if (!is_file($path) || !is_readable($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (!str_contains($line, '=')) {
            continue;
        }

        [$key, $value] = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);

        if ($key === '') {
            continue;
        }

        $valueLength = strlen($value);
        if (
            $valueLength >= 2
            && (
                ($value[0] === '"' && $value[$valueLength - 1] === '"')
                || ($value[0] === "'" && $value[$valueLength - 1] === "'")
            )
        ) {
            $value = substr($value, 1, -1);
        }

        if (getenv($key) === false) {
            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}
