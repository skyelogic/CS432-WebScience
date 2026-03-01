<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/scanner.php';
require_once __DIR__ . '/db.php';

$result  = null;
$error   = null;
$url_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $url_input = trim($_POST['url']);

    // Add scheme if missing
    if (!preg_match('#^https?://#i', $url_input)) {
        $url_input = 'https://' . $url_input;
    }

    try {
        $result = scan_url($url_input);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$ranked = get_ranked_scans(10);

// Helpers
function score_label(float $orig): string {
    if ($orig >= 80) return 'Highly Original';
    if ($orig >= 60) return 'Mostly Human';
    if ($orig >= 40) return 'Mixed Content';
    if ($orig >= 20) return 'Mostly AI';
    return 'AI Generated';
}

function score_color(float $orig): string {
    if ($orig >= 80) return '#1D3B2A'; // Forest Ink — highly original
    if ($orig >= 60) return '#4A7C5E'; // Muted green
    if ($orig >= 40) return '#C4A747'; // Antique Gold — mixed
    if ($orig >= 20) return '#8B4513'; // Saddle brown
    return '#5C1A1B';                  // Oxblood — AI generated
}

function score_emoji(float $orig): string {
    if ($orig >= 80) return '✦';
    if ($orig >= 60) return '◈';
    if ($orig >= 40) return '◇';
    if ($orig >= 20) return '▲';
    return '⚠';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuillCheck · Originality Scanner</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@400;600;700;900&family=Cinzel+Decorative:wght@400;700;900&family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,900;1,400;1,600&family=EB+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="styles.css">
</head>
<body>

<!-- ===================== NAV ===================== -->
<nav>
  <div class="logo">Quill<span>Check</span></div>
  <div class="nav-links">
    <a href="#rankings">Rankings</a>
    <a href="tfidf-table.php">TF-IDF</a>
    <a href="#pricing">Pricing</a>
    <a href="#" class="btn-nav">Sign Up</a>
  </div>
</nav>

<!-- ===================== HERO ===================== -->
<section class="hero">
  <div class="hero-tag">AI Content Scanner · Powered by EdenAI</div>
  <h1>Is this page<br><em>human</em> or machine?</h1>
  <p>Paste any URL below. QuillCheck fetches the page, strips boilerplate, and runs multi-provider AI detection to score its originality.</p>

  <form class="scan-form" method="POST" action="#result">
    <input
      type="url"
      name="url"
      placeholder="https://example.com/article"
      value="<?= htmlspecialchars($url_input) ?>"
      required
    >
    <button type="submit">▶ Scan</button>
  </form>
</section>

<!-- ===================== RESULT ===================== -->
<a name="result"></a>

<?php if ($error): ?>
<div class="error-wrap" style="max-width:800px;margin:2rem auto;padding:0 2rem;">
  <div class="error-card">
    <strong>⚠ Scan failed:</strong> <?= htmlspecialchars($error) ?>
  </div>
</div>
<?php endif; ?>

<?php if ($result): ?>
<?php
  $orig   = $result['orig_score'];
  $ai     = $result['ai_score'];
  $color  = score_color($orig);
  $label  = score_label($orig);
  $emoji  = score_emoji($orig);
?>
<div class="result-wrap" id="result">
  <div class="result-card">
    <div class="result-url">
      Scanned: <span><?= htmlspecialchars($result['url']) ?></span>
      &nbsp;·&nbsp; hash: <?= $result['hash'] ?>
    </div>

    <div class="score-row">
      <div class="score-box">
        <div class="label">Originality Score</div>
        <div class="value" style="color:<?= $color ?>"><?= $orig ?>%</div>
        <div class="verdict" style="color:<?= $color ?>"><?= $emoji ?> <?= $label ?></div>
      </div>
      <div class="score-box">
        <div class="label">AI-Generated Content</div>
        <div class="value" style="color:<?= score_color(100-$ai) ?>"><?= $ai ?>%</div>
        <div class="verdict" style="color:var(--muted)">of text flagged by AI</div>
      </div>
    </div>

    <!-- Gauge -->
    <div class="gauge-wrap">
      <div class="gauge-label">
        <span>AI Generated</span>
        <span>Human Original</span>
      </div>
      <div class="gauge-track">
        <div class="gauge-fill" style="width:<?= $orig ?>%;background:<?= $color ?>"></div>
      </div>
    </div>

    <div class="meta-row">
      <div>Providers: <span><?= htmlspecialchars($result['providers']) ?></span></div>
      <div>Words analyzed: <span><?= number_format($result['word_count']) ?></span></div>
      <div>Raw HTML: <span>cache/raw/<?= $result['hash'] ?>.html</span></div>
      <div>Clean TXT: <span>cache/txt/<?= $result['hash'] ?>.txt</span></div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===================== RANKINGS ===================== -->
<section class="rankings" id="rankings">
  <div class="section-head">
    <h2>Ranked Pages</h2>
    <small>Sorted by originality score ↓</small>
  </div>

  <?php if (empty($ranked)): ?>
  <div class="empty-state">No pages scanned yet. Be the first to submit a URL above.</div>
  <?php else: ?>
  <div class="table-scroll">
  <table>
    <thead>
      <tr>
        <th>#</th>
        <th>URL</th>
        <th>Orig. Score</th>
        <th>AI Score</th>
        <th>Words</th>
        <th>Scanned</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($ranked as $i => $row): ?>
      <?php $c = score_color((float)$row['orig_score']); ?>
      <tr>
        <td class="rank-num"><?= $i + 1 ?></td>
        <td class="rank-url">
          <a href="<?= htmlspecialchars($row['url']) ?>" target="_blank" rel="noopener">
            <?= htmlspecialchars($row['url']) ?>
          </a>
        </td>
        <td class="rank-score" style="color:<?= $c ?>">
          <?= number_format((float)$row['orig_score'], 1) ?>%
        </td>
        <td style="color:var(--muted-text)"><?= number_format((float)$row['ai_score'], 1) ?>%</td>
        <td style="color:var(--muted-text)"><?= number_format((int)$row['word_count']) ?></td>
        <td style="color:var(--muted-text)"><?= date('M j', strtotime($row['scanned_at'])) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <?php endif; ?>
</section>

<!-- ===================== PRICING ===================== -->
<section class="pricing" id="pricing">
  <h2>Simple Pricing</h2>
  <p class="sub">One URL check = one credit. No hidden fees.</p>

  <div class="pricing-grid">

    <!-- FREE -->
    <div class="plan">
      <div class="plan-name">Free</div>
      <div class="plan-price"><sup>$</sup>0<sub>/mo</sub></div>
      <div class="plan-checks">5 URL checks / month</div>
      <ul class="plan-features">
        <li>Multi-provider AI detection</li>
        <li>Originality score</li>
        <li>Public leaderboard</li>
        <li>HTML cache (24h)</li>
      </ul>
      <a href="#" class="plan-btn">Get Started</a>
    </div>

    <!-- BASE -->
    <div class="plan">
      <div class="plan-name">Base</div>
      <div class="plan-price"><sup>$</sup>9<sub>/mo</sub></div>
      <div class="plan-checks">100 URL checks / month</div>
      <ul class="plan-features">
        <li>Everything in Free</li>
        <li>Scan history (30 days)</li>
        <li>Batch URL upload (CSV)</li>
        <li>Email reports</li>
      </ul>
      <a href="#" class="plan-btn">Upgrade</a>
    </div>

    <!-- PRO (featured) -->
    <div class="plan featured">
      <div class="plan-tag">Popular</div>
      <div class="plan-name">Pro</div>
      <div class="plan-price"><sup>$</sup>29<sub>/mo</sub></div>
      <div class="plan-checks">1,000 URL checks / month</div>
      <ul class="plan-features">
        <li>Everything in Base</li>
        <li>API access</li>
        <li>Priority scanning</li>
        <li>Webhook callbacks</li>
        <li>Private leaderboard</li>
      </ul>
      <a href="#" class="plan-btn">Upgrade</a>
    </div>

    <!-- ENTERPRISE -->
    <div class="plan">
      <div class="plan-name">Enterprise</div>
      <div class="plan-price">∞</div>
      <div class="plan-checks">Unlimited checks</div>
      <ul class="plan-features">
        <li>Everything in Pro</li>
        <li>Custom SLA</li>
        <li>Dedicated instance</li>
        <li>SSO / SAML</li>
        <li>White-label option</li>
      </ul>
      <a href="#" class="plan-btn">Contact Us</a>
    </div>

  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer>
  <div>QuillCheck · CS432 Web Science · ODU <?= date('Y') ?></div>
  <div>Built with EdenAI · PHP · SQLite</div>
</footer>

<!-- ===================== DARK MODE TOGGLE ===================== -->
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

  // Default is light. Only go dark if the user has explicitly saved that preference.
  const saved = localStorage.getItem(KEY);
  setDark(saved === '1');

  btn.addEventListener('click', () => {
    setDark(!document.documentElement.classList.contains('dark'));
  });
})();
</script>

</body>
</html>