-- URL Shortener schema
-- Safe to run against a fresh MySQL 8+ server or an empty instance.

CREATE DATABASE IF NOT EXISTS url_shortener
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE url_shortener;

-- Single table is enough for a small shortener: one row per short link.
CREATE TABLE IF NOT EXISTS urls (
  -- Surrogate primary key. Auto-increment keeps inserts simple and
  -- avoids using the public short_code as the clustered index.
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,

  -- Public identifier used in the short URL path (e.g. /Ab3xY9).
  -- VARCHAR (not TEXT) so a UNIQUE index can be applied without a prefix.
  -- Length 10 is enough for a small app; generation comes in a later stage.
  short_code VARCHAR(10) NOT NULL,

  -- Destination URL. 2048 matches common browser URL limits and is
  -- large enough for typical links without using TEXT.
  original_url VARCHAR(2048) NOT NULL,

  -- Set by MySQL on INSERT; no application clock required.
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (id),

  -- Lookups and uniqueness for redirect-by-code. UNIQUE already creates
  -- an index, so a second index on short_code is not needed.
  UNIQUE KEY uq_urls_short_code (short_code),

  -- NOT NULL still allows ''. Reject blank / whitespace-only URLs.
  CONSTRAINT chk_urls_original_url_not_empty
    CHECK (CHAR_LENGTH(TRIM(original_url)) > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
