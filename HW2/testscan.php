#!/usr/bin/env php
<?php
/**
 * QuillCheck CLI Test Script
 * ========================
 * Demonstrates Q1 (URL fetch → MD5 hash → cache → boilerplate strip)
 * and Q2 (EdenAI AI detection) independently from the web UI.
 *
 * Usage:
 *   export EDENAI_API_KEY="your_key_here"
 *   php test_scan.php https://example.com
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/scanner.php';
require_once __DIR__ . '/db.php';

$urls = $argv;
array_shift($urls); // remove script name

if (empty($urls)) {
    $urls = [
        'https://example.com',
        'https://en.wikipedia.org/wiki/Artificial_intelligence',
    ];
    echo "No URL supplied — using defaults.\n\n";
}

foreach ($urls as $url) {
    echo str_repeat('─', 60) . "\n";
    echo "URL    : {$url}\n";

    // Q1: Hash
    $hash = md5($url);
    echo "MD5    : {$hash}\n";

    // Q1: Fetch + cache
    echo "Fetching...\n";
    try {
        $html  = fetch_url($url);
        $bytes = strlen($html);
        echo "HTML   : {$bytes} bytes received\n";

        cache_html($url, $html);
        echo "Cache  : " . CACHE_DIR . "{$hash}.html\n";

        // Q1: Strip boilerplate
        $text  = strip_boilerplate($html);
        $words = str_word_count($text);
        echo "Text   : " . strlen($text) . " chars | {$words} words after boilerplate removal\n";
        echo "Preview: " . mb_substr($text, 0, 120) . "...\n";

        // Q2: EdenAI call
        if (EDENAI_API_KEY !== 'YOUR_EDENAI_API_KEY_HERE') {
            echo "EdenAI : calling AI detection...\n";
            $detection = call_edenai($text);
            echo "AI %   : {$detection['ai_score']}%\n";
            echo "Orig % : " . round(100 - $detection['ai_score'], 2) . "%\n";
            echo "Providers: {$detection['providers']}\n";

            // Save to DB
            save_scan($url, $hash, $detection['ai_score'], $detection['providers'], $words, $detection['raw']);
            echo "Saved  : result written to SQLite DB\n";
        } else {
            echo "EdenAI : SKIPPED (set EDENAI_API_KEY env var to enable)\n";
        }
    } catch (Throwable $e) {
        echo "ERROR  : " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo str_repeat('─', 60) . "\n";
echo "Rankings (top 10 by originality):\n\n";

$ranked = get_ranked_scans(10);
if (empty($ranked)) {
    echo "  No scans in DB yet.\n";
} else {
    printf("%-4s %-50s %8s %8s\n", "Rank", "URL", "Orig%", "AI%");
    foreach ($ranked as $i => $r) {
        printf("%-4d %-50s %7.1f%% %7.1f%%\n",
            $i + 1,
            mb_substr($r['url'], 0, 50),
            $r['orig_score'],
            $r['ai_score']
        );
    }
}