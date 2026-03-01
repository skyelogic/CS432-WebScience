<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/tfidf.php';

$query   = '';
$results = [];
$error   = null;
$N       = 0;   // corpus size (for display)
$df      = 0;   // document frequency of term (for display)

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['query'])) {
    $query = trim($_POST['query']);

    if (strlen($query) < 2) {
        $error = 'Please enter at least 2 characters.';
    } else {
        $results = compute_tfidf_table($query, 10);

        // Derive display stats
        $N  = count($results);
        $df = count(array_filter($results, fn($r) => $r['term_count'] > 0));

        if (empty($results)) {
            $error = 'No cached documents found. Scan some URLs on the main page first.';
        }
    }
}

// Significant digits for display
function fmt(float $v, int $dec = 7): string {
    return number_format($v, $dec);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuillCheck · TF-IDF Rankings</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cinzel+Decorative:wght@400;700;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- NAV -->
<nav>
  <div class="logo">Quill<span>Check</span></div>
  <div class="nav-links">
    <a href="index.php">Scanner</a>
    <a href="tfidf-table.php" style="color:var(--oxblood)">TF-IDF</a>
    <a href="index.php#rankings">Rankings</a>
    <a href="index.php#pricing">Pricing</a>
    <a href="#" class="btn-nav">Sign Up</a>
  </div>
</nav>

<!-- HERO -->
<section class="hero">
  <div class="hero-tag">Term Frequency · Inverse Document Frequency</div>
  <h1>Rank by <em>relevance</em></h1>
  <p>Enter a query term to compute TF-IDF scores across all scanned documents and rank them by relevance.</p>

  <form class="scan-form" method="POST" action="#tfidf-results">
    <input
      type="text"
      name="query"
      placeholder="e.g. climate, regulation, artificial…"
      value="<?= htmlspecialchars($query) ?>"
      required
      minlength="2"
    >
    <button type="submit">▶ Compute</button>
  </form>
</section>

<a name="tfidf-results"></a>

<!-- ERROR -->
<?php if ($error): ?>
<div class="error-wrap">
  <div class="error-card"><strong>⚠</strong> <?= htmlspecialchars($error) ?></div>
</div>
<?php endif; ?>

<!-- RESULTS -->
<?php if (!empty($results)): ?>
<section class="rankings tfidf-section" id="tfidf-results">

  <!-- Formula box -->
  <div class="formula-card">
    <div class="formula-card-head">Computation Details</div>
    <div class="formula-grid">

      <div class="formula-block">
        <div class="formula-label">Term Frequency</div>
        <div class="formula-expr">TF(t, d) = count(t in d) &divide; |d|</div>
        <div class="formula-note">Raw occurrences of the term divided by total tokens in the document. Normalises for document length.</div>
      </div>

      <div class="formula-block">
        <div class="formula-label">Inverse Document Frequency</div>
        <div class="formula-expr">IDF(t, D) = log&#8322;( N &divide; df(t) )</div>
        <div class="formula-note">N = total documents in corpus &nbsp;|&nbsp; df(t) = documents containing the term at least once. Log base 2 per spec.</div>
      </div>

      <div class="formula-block">
        <div class="formula-label">TF-IDF</div>
        <div class="formula-expr">TF-IDF = TF &times; IDF</div>
        <div class="formula-note">Higher scores indicate the term is frequent in that document but rare across the corpus — a strong relevance signal.</div>
      </div>

    </div>

    <!-- Corpus stats for this query -->
    <div class="corpus-stats">
      <?php
        $idf_val = !empty($results) ? $results[0]['idf'] : 0;
        $wc_total = array_sum(array_column($results, 'word_count'));
      ?>
      <div class="stat-chip">
        <span class="stat-num"><?= $N ?></span>
        <span class="stat-lbl">Documents (N)</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num"><?= $df ?></span>
        <span class="stat-lbl">Containing "<?= htmlspecialchars($query) ?>" (df)</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num"><?= fmt($idf_val, 4) ?></span>
        <span class="stat-lbl">IDF = log&#8322;(<?= $N ?>/<?= $df ?>)</span>
      </div>
      <div class="stat-chip">
        <span class="stat-num"><?= number_format($wc_total) ?></span>
        <span class="stat-lbl">Total Tokens Analyzed</span>
      </div>
    </div>
  </div>

  <!-- Results table -->
  <div class="section-head" style="margin-top:2.5rem">
    <h2>TF-IDF Rankings</h2>
    <small>Query: <em>"<?= htmlspecialchars($query) ?>"</em> · Sorted by TF-IDF ↓</small>
  </div>

  <div class="table-scroll">
  <table class="tfidf-table">
    <thead>
      <tr>
        <th>#</th>
        <th>URI</th>
        <th title="count(t) / |d|">TF</th>
        <th title="log₂(N / df)">IDF</th>
        <th title="TF × IDF">TF-IDF</th>
        <th>Term Count</th>
        <th>Doc Tokens</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($results as $i => $row): ?>
      <tr class="<?= $row['term_count'] === 0 ? 'row-zero' : '' ?>">
        <td class="rank-num"><?= $i + 1 ?></td>
        <td class="rank-url">
          <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" rel="noopener">
            <?= htmlspecialchars($row['url']) ?>
          </a>
        </td>
        <td class="tfidf-val"><?= fmt($row['tf']) ?></td>
        <td class="tfidf-val tfidf-idf"><?= fmt($row['idf']) ?></td>
        <td class="tfidf-val tfidf-score <?= $row['tfidf'] > 0 ? 'nonzero' : '' ?>">
          <?= fmt($row['tfidf']) ?>
        </td>
        <td class="tfidf-count"><?= number_format($row['term_count']) ?></td>
        <td style="color:var(--muted-text)"><?= number_format($row['word_count']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>

  <p class="tfidf-footnote">
    All TF-IDF values expressed to 7 significant decimal places.
    IDF uses log base 2. Documents with TF-IDF = 0 do not contain the query term.
    Corpus limited to the <?= $N ?> most recently scanned unique URLs.
  </p>

</section>
<?php endif; ?>

<!-- FORMULA EXPLAINER (shown when no query yet) -->
<?php if (empty($results) && !$error): ?>
<section class="rankings">
  <div class="section-head">
    <h2>How TF-IDF Works</h2>
  </div>
  <div class="formula-card">
    <div class="formula-grid">
      <div class="formula-block">
        <div class="formula-label">TF — Term Frequency</div>
        <div class="formula-expr">TF(t, d) = count(t in d) &divide; |d|</div>
        <div class="formula-note">How often the term appears in a single document, normalised by document length. A term appearing 5 times in a 100-word document has TF = 0.05.</div>
      </div>
      <div class="formula-block">
        <div class="formula-label">IDF — Inverse Document Frequency</div>
        <div class="formula-expr">IDF(t, D) = log&#8322;( N &divide; df(t) )</div>
        <div class="formula-note">Penalises terms that appear in many documents (common words score low). Uses log base 2. A term in every document has IDF = 0. A term in 1 of 10 documents has IDF = log&#8322;(10) ≈ 3.32.</div>
      </div>
      <div class="formula-block">
        <div class="formula-label">TF-IDF — The Score</div>
        <div class="formula-expr">TF-IDF(t, d, D) = TF &times; IDF</div>
        <div class="formula-note">Combines both signals. A high TF-IDF means the term is frequent in this document <em>and</em> rare across the corpus — a strong relevance indicator. Documents are then ranked in decreasing order of TF-IDF.</div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- FOOTER -->
<footer>
  <div>QuillCheck · CS432 Web Science · ODU <?= date('Y') ?></div>
  <div>Built with EdenAI · PHP · SQLite</div>
</footer>

<!-- DARK MODE TOGGLE -->
<button class="dark-toggle" id="darkToggle" aria-label="Toggle dark mode" title="Toggle dark mode">
  <span class="dark-toggle-icon">☽</span>
</button>

<script>
(function () {
  const btn  = document.getElementById('darkToggle');
  const icon = btn.querySelector('.dark-toggle-icon');
  const KEY  = 'qc-dark-mode';

  function setDark(on) {
    document.documentElement.classList.toggle('dark', on);
    icon.textContent = on ? '☀' : '☽';
    localStorage.setItem(KEY, on ? '1' : '0');
  }

  const saved = localStorage.getItem(KEY);
  setDark(saved === '1');

  btn.addEventListener('click', () => {
    setDark(!document.documentElement.classList.contains('dark'));
  });
})();
</script>

</body>
</html>