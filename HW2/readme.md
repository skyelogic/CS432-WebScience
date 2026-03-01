# QuillCheck: AI Originality Scanner (1st of its kind)

---

## 👨‍💻 Author  
**Donnel Garner**  
Old Dominion University  
Norfolk, Virginia  

**CS432 – Web Science**  
**Spring 2026**  

📅 **Due Date:** March 1, 2026  
🔗 **GitHub Repository:** [skyelogic/quillcheck](https://github.com/skyelogic/quillcheck)  
🌐 **Live Site:** [QuillCheck](https://quillcheck.com)  

**Note:** Something like this didn't exist so I simply made one.  

While I understand the original requirement was to use the 500 unique URIs gathered in HW1, I realized that approach felt a bit boring. Instead, I wanted to build something more practical and interactive.  

So, I designed the system to allow users to submit their own URIs, which are then analyzed against AI detection tools to evaluate authenticity. From there, I ensured that all required assignment components were still fully addressed.  

---

## 📋 Table of Contents  
- [How to Run](#-how-to-run)
- [Q1: Data Collection](#-q1-data-collection)
- [Q2: Ranking with TF-IDF](#-q2-ranking-with-tf-idf)
- [Q3: Ranking by PageRank](#-q3-ranking-by-pagerank)
- [Technologies Used](#%EF%B8%8F-technologies-used)
- [References](#-references)

---

## 🚀 How to Run

### Option 1: Visit Website
```bash
# Simply visit quillcheck.com
Simply go to the link http://quillcheck.com
Paste a link, and all required assignment details will display.

```

### Option 2: PHP Built-in Server
```bash
# Clone the repo and enter the project directory
git clone https://github.com/skyelogic/quillcheck.git
cd quillcheck

# (Possibly Optional?) May need to set an EdenAI API key
export EDENAI_API_KEY="your_key_here"

# Start the PHP development server
php -S localhost:8080

# Open in browser
http://localhost:8080
```

### Option 3: Deploy to Web Server
```bash
# Upload all project flies to your web server document root
# Rename htaccess to .htaccess
# Navigate to your domain
https://yourdomain.com/quillcheck/
```

---

## 📊 Q1: Data Collection

### How It Works

QuillCheck accepts any publicly accessible URL, fetches its full HTML, and processes it through a two-stage pipeline before sending text to the AI detection API.

**Stage 1 — Fetch & Cache Raw HTML**

The raw HTML response is saved immediately to `cache/raw/{md5hash}.html` before any processing occurs. The filename is the MD5 hash of the original URL; the same approach described in the assignment spec:

```
https://example.com/article  →  md5  →  5aadb45520dcd8e7dc438ac608be31d6
                                         cache/raw/5aadb45520dcd8e7dc438ac608be31d6.html
```

This preserves the untouched source for later analysis and auditing.

**Stage 2 — Strip & Cache Clean Text**

After caching the raw HTML, the boilerplate stripper runs a multi-pass extraction pipeline:

1. **Drop `<head>` entirely** — removes all CSS, meta tags, and `<link>` declarations
2. **Nuke tag families with content** — `<script>`, `<style>`, `<noscript>`, `<header>`, `<footer>`, `<nav>`, `<aside>`, `<form>`, `<iframe>`, `<svg>`, `<canvas>` are removed along with everything inside them
3. **Heuristic noise removal** — strips `<div>` and `<section>` elements whose `id` or `class` attributes contain keywords like `nav`, `sidebar`, `cookie`, `ad-`, `subscribe`, `newsletter`, `share`, `comment`, `breadcrumb`, `pagination`, and more. Runs twice to catch nested containers
4. **Body extraction** — isolates content within `<body>` tags only
5. **Final cleanup** — `strip_tags()`, `html_entity_decode()`, and whitespace collapse

The cleaned plain text is saved to `cache/txt/{md5hash}.txt` - the exact content sent to EdenAI for analysis.

**Both files are kept** so raw HTML and processed text can be compared, diffed, or fed into other tooling independently.

### Cache Directory Structure
```
cache/
├── raw/          ← original HTML, exactly as fetched
│   └── {hash}.html
└── txt/          ← clean plain-text after stripping
    └── {hash}.txt
```

### URI → Hash Mapping

Every scan is persisted to a SQLite database (`quillcheck.db`) that maps each URL to its MD5 hash, scan timestamp, AI score, originality score, word count, and the full raw JSON response from EdenAI. This serves as the authoritative URI-to-hash registry.

```sql
SELECT url, url_hash, scanned_at FROM scans ORDER BY scanned_at DESC;
```

**Q: How many of your URIs produced useful text? If that number was less than 500, did that surprise you?**

> Pages that return fewer than 50 characters of usable text after stripping are rejected with an error. In practice, several categories of pages produce no useful output: JavaScript-rendered SPAs (content never appears in the raw HTML), paywalled articles, pages returning non-200 status codes, and heavily CDN-cached pages that return bot-detection challenges (cloudflare). This was not surprising. It mirrors the real-world noise ratio any web crawler encounters.

## 🤖 AI Detection & Scoring

### Scoring Pipeline

Once clean text is extracted, QuillCheck sends up to 10,000 characters to the [EdenAI](https://www.edenai.run/) AI detection API using the `sapling` provider. EdenAI returns an `ai_score` float between 0 and 1, which is converted to a percentage:

| Score Range | Label            | Meaning                        |
|-------------|------------------|--------------------------------|
| 80 – 100%   | Highly Original  | Almost certainly human-written |
| 60 – 79%    | Mostly Human     | Predominantly human content    |
| 40 – 59%    | Mixed Content    | Blend of human and AI          |
| 20 – 39%    | Mostly AI        | Likely AI-generated            |
| 0 – 19%     | AI Generated     | Almost certainly machine-written |

The **Originality Score** is simply `100 - ai_score`, so higher is better for human authorship.

### Results Display

Every scan produces:
- A large visual score card with color-coded originality and AI percentages
- A gradient gauge bar (Oxblood → Antique Gold) showing the human/AI split
- Metadata: provider used, word count analyzed, and paths to both cache files
- Persistent ranking table showing all scanned URLs sorted by originality score

---

## 📐 Q2: Ranking with TF-IDF

TF-IDF (Term Frequency–Inverse Document Frequency) is a numerical statistic that reflects how important a word is to a document relative to a collection of documents (the corpus). QuillCheck implements a full TF-IDF engine in `tfidf.php` that operates directly on the cleaned `.txt` cache files produced during Q1.

The live ranking page is available at [`/tfidf-table.php`](http://quillcheck.com/tfidf-table.php).

---

### Step 0: Tokenisation

Before any math can happen, each document must be reduced to a flat list of lowercase alphabetic tokens. Numbers, punctuation, and special characters are stripped entirely so that `"climate,"` and `"climate"` are treated as the same term.

```php
function tokenise(string $text): array {
    // Lowercase everything
    $text = mb_strtolower($text, 'UTF-8');

    // Keep only a–z characters and spaces (strip punctuation/numbers)
    $text = preg_replace('/[^a-z\s]/u', ' ', $text);

    // Split on whitespace and discard empty tokens
    $tokens = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);

    return $tokens ?: [];
}
```

Each document's token array is the direct input to the TF and IDF calculations below.

---

### Formula 1: TF (Term Frequency)

**TF** measures how often a term appears in a single document, normalised by the total number of tokens in that document. This prevents long documents from automatically scoring higher than short ones just because they contain more words overall.

```
TF(t, d) = count(t in d) / |d|
```

Where:
- `count(t in d)` = number of times term `t` appears in document `d`
- `|d|` = total number of tokens in document `d`

**Example:** If the term `"climate"` appears 8 times in a 1,600-token document, `TF = 8 / 1600 = 0.005`.

```php
function compute_tf(string $term, array $tokens): float {
    $total = count($tokens);
    if ($total === 0) return 0.0;
    return term_frequency_count($term, $tokens) / $total;
}
```

The helper `term_frequency_count()` does an exact, case-sensitive match against the already-lowercased token array:

```php
function term_frequency_count(string $term, array $tokens): int {
    return count(array_filter($tokens, fn($t) => $t === $term));
}
```

---

### Formula 2: IDF (Inverse Document Frequency)

**IDF** measures how rare or common a term is across the entire corpus. Terms that appear in every document (like `"the"` or `"and"`) carry no discriminating power and receive a low IDF score. Terms that appear in only one or two documents are considered distinctive and receive a high IDF score.

```
IDF(t, D) = log₂( N / df(t) )
```

Where:
- `N` = total number of documents in the corpus
- `df(t)` = number of documents that contain term `t` at least once
- The logarithm is **base 2**, per the assignment specification

**Example with N = 10 documents:**

| df(t) | IDF = log₂(10 / df) |
|-------|----------------------|
| 10 (every doc) | log₂(1.0) = **0.0000000** |
| 5 (half the docs) | log₂(2.0) = **1.0000000** |
| 2 | log₂(5.0) = **2.3219281** |
| 1 (one doc only) | log₂(10.0) = **3.3219281** |

Notice that a term appearing in every document scores exactly 0. It contributes nothing to ranking, which is the intended behavior. A term found in only one document scores the highest IDF possible for that corpus size.

The IDF value is **the same for all documents** for a given query term, since it is a property of the term across the corpus, not of any individual document. It is computed once in Pass 2 and then multiplied against each document's TF in Pass 3.

```php
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
```

> **Edge case:** If `df(t) = 0` (the term does not appear in any document), dividing by zero is avoided by returning `0.0` early. Such a term is meaningless for ranking anyway.

---

### Formula 3 — TF-IDF

**TF-IDF** is the product of the two values above. It is high when a term is both frequent in a specific document *and* rare across the rest of the corpus; a strong signal that the document is genuinely about that topic.

```
TF-IDF(t, d, D) = TF(t, d) × IDF(t, D)
```

---

### The Three-Pass Pipeline

The full computation in `compute_tfidf_table()` runs in three sequential passes to ensure IDF is calculated correctly over the complete corpus before any per-document TF-IDF is produced:

```php
// ---- Pass 1: Load & tokenise every document ----
foreach ($rows as $row) {
    $text   = load_cached_text($row['url_hash']);
    $docs[] = [
        'url'    => $row['url'],
        'hash'   => $row['url_hash'],
        'tokens' => tokenise($text),
    ];
}

// ---- Pass 2: Compute IDF (needs full corpus) ----
$corpus = array_column($docs, 'tokens');
$idf    = compute_idf($term, $corpus);

// ---- Pass 3: Compute TF and TF-IDF per document ----
foreach ($docs as $doc) {
    $tf     = compute_tf($term, $doc['tokens']);
    $tfidf  = $tf * $idf;
    // ... store results
}

// ---- Sort by TF-IDF descending ----
usort($results, fn($a, $b) => $b['tfidf'] <=> $a['tfidf']);
```

Pass 1 must complete before Pass 2 can begin, because IDF requires knowing `df(t)` across **all** documents simultaneously. If IDF were computed document-by-document as each was loaded, the denominator `df(t)` would be wrong for every document except the last.

---

### Significant Digits

All TF, IDF, and TF-IDF values are displayed to **7 decimal places** in the results table, matching the precision expectation of the HW2 report:

```php
function fmt(float $v, int $dec = 7): string {
    return number_format($v, $dec);
}
```

---

### Results Table

The output table at `/tfidf-table.php` lists all scanned documents ranked in **decreasing order by TF-IDF**. Documents that do not contain the query term at all receive TF = 0, and therefore TF-IDF = 0, and are sorted to the bottom. The shared IDF value and corpus statistics (N, df) are displayed above the table so the computation is fully transparent and reproducible.

| # | URI | TF | IDF | TF-IDF | Term Count | Doc Tokens |
|---|-----|----|-----|--------|------------|------------|
| 1 | https://example.com/article | 0.0050000 | 3.3219281 | 0.0166096 | 8 | 1,600 |
| 2 | … | … | 3.3219281 | … | … | … |

*(Actual values will vary based on which URLs have been scanned.)*

---

## 🔗 Q3: Ranking by PageRank

### Method

PageRank scores were retrieved manually using [https://smallseotools.com/google-pagerank-checker/](https://smallseotools.com/google-pagerank-checker/) - the same tool applied consistently across all 10 domains. Per the assignment specification, root domains were submitted rather than full URIs (e.g., `https://www.nbcnews.com/` rather than a deep article path), since PageRank is a domain-level signal and the checkers work best at that level.

Raw scores are returned on a **0–10 integer scale**. To normalize to a **0–1.0 range**, each raw score is divided by 10:

```
Normalized PR = Raw PR / 10
```

Results are sorted in **decreasing order** by normalized PageRank below.

---

### Table 2 — 10 Domains Ranked by PageRank

| Normalized PR | Raw PR | URI |
|:---:|:---:|---|
| 0.8 | 8 | [https://www.nbcnews.com/](https://www.nbcnews.com/) |
| 0.7 | 7 | [https://www.cp24.com/](https://www.cp24.com/) |
| 0.7 | 7 | [https://www.mcdonalds.com/](https://www.mcdonalds.com/) |
| 0.7 | 7 | [https://www.infoworld.com/](https://www.infoworld.com/) |
| 0.6 | 6 | [https://www.mcsweeneys.net/](https://www.mcsweeneys.net/) |
| 0.4 | 4 | [https://ws-dl.blogspot.com/](https://ws-dl.blogspot.com/) |
| 0.2 | 2 | [https://weiglemc.github.io/](https://weiglemc.github.io/) |
| 0.2 | 2 | [https://www.ourgemcodes.com/](https://www.ourgemcodes.com/) |
| 0.0 | 0 | [https://unhewnhearts.org/](https://unhewnhearts.org/) |

> **Tool used:** smallseotools.com/google-pagerank-checker  
> **Date checked:** February 28th, 2026  
> **Note:** Google officially retired the public Toolbar PageRank in 2016. These scores are third-party estimations based on backlink analysis and domain authority signals, not Google's internal values.

---

### Q: Compare and Contrast TF-IDF Rankings (Q2) vs. PageRank Rankings (Q3)

The two ranking methods measure fundamentally different things, and the results reflect that.

**TF-IDF** ranks documents by **topical relevance to a specific query term**. A page scores high because it uses the query word frequently *and* that word is rare across the corpus. This means TF-IDF rankings shift entirely depending on what term you search; a small personal blog post that is laser-focused on the query term can outrank a major news site that only mentions it in passing. TF-IDF is a content-driven signal: it doesn't know or care how authoritative the site is on the web, only how relevant this particular document is to this particular term.

**PageRank**, by contrast, ranks domains by **global web authority** - a structural signal derived from the quantity and quality of inbound links. A domain like `nbcnews.com` scores 0.8 not because of anything it said about our query term, but because thousands of other high-authority sites link to it. PageRank is query-agnostic: NBC News ranks at 0.8 whether you search for "climate", "sports", or "recipes."

In practice, the two rankings are largely **uncorrelated** in this dataset. Domains like `unhewnhearts.org` (PR = 0.0) or `weiglemc.github.io` (PR = 0.2) are low-authority by the link-graph metric, but could rank first on a TF-IDF query if their content is highly concentrated around the search term. Conversely, `nbcnews.com` tops the PageRank table but its long-form articles spread vocabulary broadly, which tends to dilute TF for any single term and push it down the TF-IDF rankings.

This is why real-world search engines like Google combine both signals. PageRank to surface trustworthy sources, and term-based relevance scoring (of which TF-IDF is the conceptual ancestor) to ensure those sources are actually *about* what the user asked.

---


## 🛠️ Technologies Used

### Languages & Runtimes
- **PHP 8+** — backend fetch, stripping, caching, database writes, and template rendering
- **JavaScript (Vanilla)** — dark mode toggle with `localStorage` persistence
- **HTML5** — semantic structure
- **CSS3** — custom parchment design system, CSS variables, responsive grid, dark mode via `html.dark` class

### Libraries & APIs
- **[EdenAI](https://www.edenai.run/)** — multi-provider AI detection API (Sapling provider)
- **SQLite** (via PHP PDO) — lightweight scan history and URI-hash registry
- **Google Fonts** — Cinzel Decorative, Cinzel, Playfair Display, EB Garamond

### External Tools
- **cURL** (PHP) — HTTP fetching with redirect following and custom user-agent
- **MD5** (PHP `md5()`) — URL hashing for deterministic cache filenames
- **SSH / SFTP** — deployment to ODU web server
- **VS Code** — primary development environment

---

## 📚 References

✅ **[EdenAI Documentation](https://docs.edenai.run/)** - AI Detection API reference  
✅ **[PHP cURL Manual](https://www.php.net/manual/en/book.curl.php)** - HTTP fetching  
✅ **[PHP PDO / SQLite](https://www.php.net/manual/en/book.pdo.php)** - database layer  
✅ **[MDN: CSS Custom Properties](https://developer.mozilla.org/en-US/docs/Web/CSS/Using_CSS_custom_properties)** - CSS variable system  
✅ **[Google Fonts](https://fonts.google.com/)** - Cinzel, Playfair Display, EB Garamond  
✅ **[Claude](https://claude.ai)** - Development assistance    

---

## 📝 License

This project is submitted as coursework for CS 432 at Old Dominion University.  
The complete commercial version is protected. You may use this at your own discretion.  

---

## 🙏 Acknowledgments

Special thanks to:  
- **NASREEN MUHAMMAD ARIF** - Course instructor  
- **Old Dominion University** - Computer Science program  

---

<p align="center">
  <strong>Made with ☕ and 💻 by Donnel Garner</strong><br>
  <sub>Old Dominion University | CS 432 | Spring 2026</sub>
</p>

---

<p align="center">
  <a href="https://donnelgarner.com">🌐 Personal Website</a> •
  <a href="https://github.com/skyelogic">💻 GitHub</a>
</p>
