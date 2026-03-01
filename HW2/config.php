<?php
// ============================================================
//  QuillCheck Configuration
//  Store your EdenAI API key in an environment variable or
//  replace the fallback string with your actual key.
// ============================================================

define('EDENAI_API_KEY', getenv('EDENAI_API_KEY') ?: 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiNGFkYjE1ZmItZTJjMS00ZWEwLWJlYTEtNDM1OTRiZjMzMzdkIiwidHlwZSI6ImFwaV90b2tlbiJ9.tHa_7mdG1EngdnBWJ3tTx1ouGJAK0nF7ZA_yc2o4iF4');
define('EDENAI_AI_DETECTION_URL', 'https://api.edenai.run/v2/text/ai_detection');

// Providers to use for AI detection (comma-separated string)
define('EDENAI_PROVIDERS', 'sapling');

// ---------------------------------------------------------------
//  Cache directories — raw HTML and stripped TXT are kept
//  separately so both can be used for independent data analysis.
//    cache/raw/  — original HTML exactly as fetched
//    cache/txt/  — clean plain-text after boilerplate stripping
// ---------------------------------------------------------------
define('CACHE_DIR',     __DIR__ . '/cache/');        // legacy alias
define('CACHE_RAW_DIR', __DIR__ . '/cache/raw/');    // raw HTML
define('CACHE_TXT_DIR', __DIR__ . '/cache/txt/');    // stripped TXT

// Auto-create both subdirectories on first run
foreach ([CACHE_RAW_DIR, CACHE_TXT_DIR] as $_dir) {
    if (!is_dir($_dir)) {
        mkdir($_dir, 0755, true);
    }
}

// SQLite database path
define('DB_PATH', __DIR__ . '/quillcheck.db');

// Tier limits (checks per month)
define('TIER_LIMITS', [
    'free'       => 3,
    'base'       => 50,
    'pro'        => 500,
    'enterprise' => PHP_INT_MAX,
]);

// For demo purposes the current user tier — in production this
// would come from a session / auth system.
define('CURRENT_TIER', 'free');