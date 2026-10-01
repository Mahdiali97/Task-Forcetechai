<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/utils/env.php';

loadEnv(dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env');

/**
 * Return a shared PDO connection.
 *
 * Credentials come from environment variables (see .env.example).
 * ATTR_EMULATE_PREPARES is disabled so later queries use native
 * prepared statements rather than client-side emulation.
 */
function getDatabaseConnection(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $host = getenv('DB_HOST');
    $port = getenv('DB_PORT') ?: '3306';
    $name = getenv('DB_NAME');
    $user = getenv('DB_USER');
    $password = getenv('DB_PASSWORD');

    if ($host === false || $host === '' || $name === false || $name === '' || $user === false || $user === '') {
        throw new RuntimeException(
            'Missing database configuration. Set DB_HOST, DB_NAME, and DB_USER (see .env.example).'
        );
    }

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $port,
        $name
    );

    $pdo = new PDO($dsn, $user, $password === false ? '' : $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

/**
 * Run a parameterized SQL statement.
 *
 * Always pass user values through $params — never concatenate them into $sql.
 *
 * Example (for a later stage, not used yet):
 * dbExecute(
 *     'INSERT INTO urls (short_code, original_url) VALUES (:short_code, :original_url)',
 *     ['short_code' => $code, 'original_url' => $url]
 * );
 */
function dbExecute(string $sql, array $params = []): PDOStatement
{
    $statement = getDatabaseConnection()->prepare($sql);
    $statement->execute($params);

    return $statement;
}
