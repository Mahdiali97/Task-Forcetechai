<?php

declare(strict_types=1);

const SHORT_CODE_MIN_LENGTH = 6;
const SHORT_CODE_MAX_LENGTH = 8;

/**
 * URL-safe alphabet: letters and digits only.
 * Characters such as +, /, and = are omitted so the code can sit in a path
 * without encoding (e.g. /s/Ab3xY9).
 */
const SHORT_CODE_ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

/**
 * Generate one random short-code candidate.
 *
 * Uses random_int(), a CSPRNG. Codes are public URL tokens: if they were
 * produced with rand(), mt_rand(), or a timestamp, an attacker could more
 * easily predict unused codes and probe for other people's links.
 *
 * This function does not talk to the database. short_code is UNIQUE, so the
 * API should INSERT the row and, on a duplicate-key error, call this again.
 * Checking "does this code exist?" before insert is racy under concurrency.
 *
 * @param int|null $length 6–8 characters. Null picks a length with random_int().
 */
function generateShortCode(?int $length = null): string
{
    if ($length === null) {
        $length = random_int(SHORT_CODE_MIN_LENGTH, SHORT_CODE_MAX_LENGTH);
    }

    if ($length < SHORT_CODE_MIN_LENGTH || $length > SHORT_CODE_MAX_LENGTH) {
        throw new InvalidArgumentException(
            'Short code length must be between ' . SHORT_CODE_MIN_LENGTH
            . ' and ' . SHORT_CODE_MAX_LENGTH . '.'
        );
    }

    $alphabetLength = strlen(SHORT_CODE_ALPHABET);
    $code = '';

    for ($i = 0; $i < $length; $i++) {
        $code .= SHORT_CODE_ALPHABET[random_int(0, $alphabetLength - 1)];
    }

    return $code;
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    for ($i = 0; $i < 5; $i++) {
        echo generateShortCode(), PHP_EOL;
    }
}
