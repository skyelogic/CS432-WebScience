<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// ----------------------------------------------------------------
//  Boilerplate Stripper (pure PHP, no external library required)
//
//  Strategy:
//    1. Ditch everything in <head> entirely (CSS, meta, scripts)
//    2. Nuke chrome elements: <header>, <footer>, <nav>, <aside>,
//       plus any element whose role/id/class smells like navigation,
//       sidebar, cookie banners, ads, etc.
//    3. Extract whatever remains inside <body>
//    4. Strip residual tags, decode entities, collapse whitespace
// ----------------------------------------------------------------

function strip_boilerplate(string $html): string {

    // ---- 1. Drop the entire <head> block -------------------------
    $html = preg_replace('#<head[^>]*>.*?</head>#is', '', $html);

    // ---- 2. Nuke full tag families (with all their contents) -----
    $block_tags = [
        'script', 'style', 'noscript',          // code / styles
        'header', 'footer', 'nav', 'aside',      // structural chrome
        'form',                                  // forms / search bars
        'figure', 'figcaption',                  // captions (often ads)
        'iframe', 'object', 'embed',             // embedded content
        'svg', 'canvas',                         // graphics
    ];
    foreach ($block_tags as $tag) {
        $html = preg_replace("#<{$tag}[^>]*>.*?</{$tag}>#is", ' ', $html);
    }

    // ---- 3. Strip elements by role / id / class heuristics -------
    //  Catches: cookie banners, ad containers, sidebars, comment
    //  sections, share widgets, subscription prompts, etc.
    $noise_patterns = [
        // role="…" attributes used for landmark regions
        '#<[^>]+role=["\'](?:banner|navigation|complementary|contentinfo|search|form)["\'][^>]*>.*?</\w+>#is',
        // id or class containing these keywords
        '#<(?:div|section|ul|ol|aside|span|p)[^>]+(?:id|class)=["\'][^"\']*'
            . '(?:nav|navbar|navigation|menu|sidebar|widget|cookie|banner|'
            .  'popup|modal|overlay|ad-|ads-|advert|promo|subscribe|'
            .  'newsletter|social|share|comment|footer|header|breadcrumb|'
            .  'pagination|pager|related|recommended|tag-cloud|toc|'
            .  'table-of-contents)[^"\']*["\'][^>]*>.*?</(?:div|section|ul|ol|aside|span|p)>#is',
    ];
    foreach ($noise_patterns as $pattern) {
        // Run twice — nested containers sometimes need a second pass
        $html = preg_replace($pattern, ' ', $html);
        $html = preg_replace($pattern, ' ', $html);
    }

    // ---- 4. Extract only what's inside <body> --------------------
    if (preg_match('#<body[^>]*>(.*?)</body>#is', $html, $m)) {
        $html = $m[1];
    }

    // ---- 5. Strip all remaining tags, decode entities, tidy up ---
    $html = strip_tags($html);
    $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Collapse runs of whitespace / blank lines into single spaces
    $html = preg_replace('/\s+/', ' ', $html);

    return trim($html);
}

// ----------------------------------------------------------------
//  Fetch a remote URL and return its HTML body.
// ----------------------------------------------------------------

function fetch_url(string $url): string {
    $ctx = stream_context_create([
        'http' => [
            'method'     => 'GET',
            'timeout'    => 15,
            'user_agent' => 'QuillCheck-Scanner/1.0 (CS432 Web Science Project)',
            'follow_location' => true,
        ],
        'ssl' => [
            'verify_peer'      => false,
            'verify_peer_name' => false,
        ],
    ]);

    $html = @file_get_contents($url, false, $ctx);

    if ($html === false) {
        throw new RuntimeException("Could not fetch URL: {$url}");
    }

    return $html;
}

// ----------------------------------------------------------------
//  Cache the original raw HTML (before any stripping).
//  Saved to: cache/raw/{hash}.html
// ----------------------------------------------------------------

function cache_raw_html(string $url, string $html): string {
    $hash = md5($url);
    file_put_contents(CACHE_RAW_DIR . $hash . '.html', $html);
    return $hash;
}

// ----------------------------------------------------------------
//  Cache the cleaned plain text (after boilerplate stripping).
//  Saved to: cache/txt/{hash}.txt
// ----------------------------------------------------------------

function cache_clean_txt(string $url, string $text): void {
    $hash = md5($url);
    file_put_contents(CACHE_TXT_DIR . $hash . '.txt', $text);
}

// ----------------------------------------------------------------
//  Call EdenAI AI Detection endpoint.
//  Returns ['ai_score' => float, 'providers' => string, 'raw' => string]
// ----------------------------------------------------------------

function call_edenai(string $text): array {
    // EdenAI has a character limit per request; truncate if needed
    $text = mb_substr($text, 0, 10000);

    $payload = json_encode([
        'providers'        => EDENAI_PROVIDERS,
        'text'             => $text,
        'response_as_dict' => true,
        'language'         => 'en',
    ]);

    $ch = curl_init(EDENAI_AI_DETECTION_URL);
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . EDENAI_API_KEY,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $httpCode !== 200) {
        throw new RuntimeException("EdenAI API error (HTTP {$httpCode}): " . ($response ?: 'No response'));
    }

    $data = json_decode($response, true);

    // Aggregate AI scores from all providers that returned a result
    $scores      = [];
    $providerNames = [];

    foreach ($data as $provider => $result) {
        if (!is_array($result)) continue;
        if (isset($result['status']) && $result['status'] === 'fail') continue;

        // EdenAI returns `ai_score` as a float 0-1
        if (isset($result['ai_score'])) {
            $scores[]        = (float) $result['ai_score'] * 100;
            $providerNames[] = $provider;
        }
    }

    if (empty($scores)) {
        throw new RuntimeException('No AI detection providers returned a usable score.');
    }

    $avgScore = array_sum($scores) / count($scores);

    return [
        'ai_score'  => round($avgScore, 2),
        'providers' => implode(', ', $providerNames),
        'raw'       => $response,
    ];
}

// ----------------------------------------------------------------
//  Main scan function — orchestrates everything.
// ----------------------------------------------------------------

function scan_url(string $url): array {
    // 1. Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new InvalidArgumentException('Invalid URL format.');
    }

    // 2. Fetch raw HTML
    $html  = fetch_url($url);

    // 3. Save raw HTML immediately — untouched, for analysis later
    $hash  = cache_raw_html($url, $html);

    // 4. Strip boilerplate to extract clean body text
    $text  = strip_boilerplate($html);

    if (strlen($text) < 50) {
        throw new RuntimeException('Page returned too little usable text content (< 50 characters) after stripping boilerplate.');
    }

    // 5. Save stripped plain-text to its own folder
    cache_clean_txt($url, $text);

    $words = str_word_count($text);

    // 6. Call EdenAI
    $edenResult = call_edenai($text);

    // 7. Persist to DB
    $scanId = save_scan(
        $url,
        $hash,
        $edenResult['ai_score'],
        $edenResult['providers'],
        $words,
        $edenResult['raw']
    );

    return [
        'id'         => $scanId,
        'url'        => $url,
        'hash'       => $hash,
        'ai_score'   => $edenResult['ai_score'],
        'orig_score' => round(100 - $edenResult['ai_score'], 2),
        'providers'  => $edenResult['providers'],
        'word_count' => $words,
    ];
}