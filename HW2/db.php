<?php
require_once __DIR__ . '/config.php';

/**
 * Returns a connected PDO instance (creates schema on first run).
 */
function get_db(): PDO {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS scans (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            url         TEXT    NOT NULL,
            url_hash    TEXT    NOT NULL,
            scanned_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
            ai_score    REAL,          -- 0-100: % AI-generated
            orig_score  REAL,          -- 100 - ai_score
            provider    TEXT,          -- which providers responded
            word_count  INTEGER,
            raw_response TEXT          -- full JSON from EdenAI
        );

        CREATE INDEX IF NOT EXISTS idx_hash ON scans (url_hash);
        CREATE INDEX IF NOT EXISTS idx_scanned ON scans (scanned_at);
    ");

    return $pdo;
}

/**
 * Save a scan result to the database.
 */
function save_scan(string $url, string $hash, float $ai_score, string $provider, int $words, string $raw): int {
    $db = get_db();
    $stmt = $db->prepare("
        INSERT INTO scans (url, url_hash, ai_score, orig_score, provider, word_count, raw_response)
        VALUES (:url, :hash, :ai, :orig, :prov, :wc, :raw)
    ");
    $stmt->execute([
        ':url'  => $url,
        ':hash' => $hash,
        ':ai'   => $ai_score,
        ':orig' => 100 - $ai_score,
        ':prov' => $provider,
        ':wc'   => $words,
        ':raw'  => $raw,
    ]);
    return (int) $db->lastInsertId();
}

/**
 * Retrieve the most recent scans, ordered by originality (highest first).
 */
function get_ranked_scans(int $limit = 20): array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT url, orig_score, ai_score, word_count, scanned_at
        FROM   scans
        ORDER  BY orig_score DESC
        LIMIT  :lim
    ");
    $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Look up the most recent cached result for a URL hash.
 */
function get_cached_scan(string $hash): ?array {
    $db = get_db();
    $stmt = $db->prepare("
        SELECT * FROM scans WHERE url_hash = :hash ORDER BY scanned_at DESC LIMIT 1
    ");
    $stmt->execute([':hash' => $hash]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}