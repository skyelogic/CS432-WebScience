<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ====================================================================
//  QuillCheck — TF-IDF Engine
//
//  Formulas used (per assignment spec):
//
//  TF  (term frequency)
//      TF(t, d) = (number of times term t appears in document d)
//                 ÷ (total number of terms in document d)
//      → Raw count divided by document length. Normalises for doc size.
//
//  IDF (inverse document frequency)  — log BASE 2
//      IDF(t, D) = log2( N / df(t) )
//      where N    = total number of documents in the corpus
//            df(t) = number of documents that contain term t at least once
//      → Rare terms score higher; terms in every doc approach 0.
//        Per spec: "don't forget log base 2."
//
//  TF-IDF
//      TF-IDF(t, d, D) = TF(t, d) × IDF(t, D)
//
//  Significant digits: all values rounded to 7 decimal places for the
//  display table, matching the precision expected in the HW2 report.
// ====================================================================

/**
 * Tokenise a plain-text document into lowercase words.
 * Strips punctuation, keeps only alphabetic tokens (a–z).
 *
 * @param  string $text  Cleaned plain-text content from cache/txt/
 * @return string[]      Array of lowercase word tokens
 */
function tokenise(string $text): array {
    // Lowercase everything
    $text = mb_strtolower($text, 'UTF-8');

    // Keep only a–z characters and spaces (strip punctuation/numbers)
    $text = preg_replace('/[^a-z\s]/u', ' ', $text);

    // Split on whitespace and discard empty tokens
    $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    return $tokens ?: [];
}

/**
 * Count how many times $term appears in $tokens (exact, lowercase match).
 */
function term_frequency_count(string $term, array $tokens): int {
    return count(array_filter($tokens, fn($t) => $t === $term));
}

/**
 * Compute TF for a single document.
 *
 *   TF(t, d) = count(t in d) / |d|
 *
 * Returns 0.0 if the document has no tokens.
 */
function compute_tf(string $term, array $tokens): float {
    $total = count($tokens);
    if ($total === 0) return 0.0;
    return term_frequency_count($term, $tokens) / $total;
}

/**
 * Compute IDF across a corpus of token arrays.
 *
 *   IDF(t, D) = log2( N / df(t) )
 *
 * df(t) = number of documents containing term t at least once.
 * If df(t) = 0 (term absent from all docs), returns 0.0 to avoid
 * division by zero (the term is meaningless for ranking anyway).
 */
function compute_idf(string $term, array $corpus): float {
    $N  = count($corpus);
    if ($N === 0) return 0.0;

    $df = 0;
    foreach ($corpus as $tokens) {
        if (term_frequency_count($term, $tokens) > 0) {
            $df++;
        }
    }

    if ($df === 0) return 0.0;

    return log($N / $df, 2);   // log base 2, per assignment spec
}

/**
 * Load the plain-text cache file for a given URL hash.
 * Returns null if the file doesn't exist.
 */
function load_cached_text(string $hash): ?string {
    $path = CACHE_TXT_DIR . $hash . '.txt';
    if (!file_exists($path)) return null;
    return file_get_contents($path);
}

/**
 * Core TF-IDF computation over all scanned documents in the DB.
 *
 * For a given $query_term, reads every cached .txt file, tokenises
 * each one, then computes TF, IDF, and TF-IDF per document.
 *
 * Returns an array sorted by TF-IDF descending:
 * [
 *   [
 *     'url'       => string,
 *     'hash'      => string,
 *     'tf'        => float,   // raw TF
 *     'idf'       => float,   // shared IDF (same for all docs)
 *     'tfidf'     => float,
 *     'term_count'=> int,     // raw occurrences of term in doc
 *     'word_count'=> int,     // total tokens in doc
 *   ],
 *   ...
 * ]
 */
function compute_tfidf_table(string $query_term, int $limit = 10): array {
    $term = mb_strtolower(trim($query_term), 'UTF-8');
    if ($term === '') return [];

    $db = get_db();

    // Pull the most recent scan per URL (deduplicated by url_hash)
    $stmt = $db->query("
        SELECT url, url_hash
        FROM   scans
        GROUP  BY url_hash
        ORDER  BY MAX(scanned_at) DESC
        LIMIT  {$limit}
    ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) return [];

    // ---- Pass 1: Load & tokenise every document ----
    $docs = [];   // ['url' => ..., 'hash' => ..., 'tokens' => [...]]
    foreach ($rows as $row) {
        $text = load_cached_text($row['url_hash']);
        if ($text === null) continue;   // skip if cache file missing

        $docs[] = [
            'url'    => $row['url'],
            'hash'   => $row['url_hash'],
            'tokens' => tokenise($text),
        ];
    }

    if (empty($docs)) return [];

    // ---- Pass 2: Compute IDF (needs full corpus) ----
    $corpus = array_column($docs, 'tokens');
    $idf    = compute_idf($term, $corpus);

    // ---- Pass 3: Compute TF and TF-IDF per document ----
    $results = [];
    foreach ($docs as $doc) {
        $tokens     = $doc['tokens'];
        $term_count = term_frequency_count($term, $tokens);
        $word_count = count($tokens);
        $tf         = compute_tf($term, $tokens);
        $tfidf      = $tf * $idf;

        $results[] = [
            'url'        => $doc['url'],
            'hash'       => $doc['hash'],
            'tf'         => $tf,
            'idf'        => $idf,
            'tfidf'      => $tfidf,
            'term_count' => $term_count,
            'word_count' => $word_count,
        ];
    }

    // ---- Sort by TF-IDF descending ----
    usort($results, fn($a, $b) => $b['tfidf'] <=> $a['tfidf']);

    return $results;
}