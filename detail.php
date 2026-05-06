<?php
require_once 'db.php';
require_once 'functions.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id  = (int) $_GET['id'];
$llm = getLlmById($conn, $id);

if (!$llm) {
    header('Location: index.php');
    exit;
}

$tagy    = getTagsForLlm($conn, $id);
$recenze = getReviewsForLlm($conn, $id);
$rating  = getAvgRatingForLlm($conn, $id);

$zprava = '';
if (isset($_GET['rated'])) {
    $zprava = 'success';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stars    = $_POST['stars'] ?? '';
    $vysledek = saveReview($conn, $id, $stars);
    if ($vysledek === true) {
        header('Location: detail.php?id=' . $id . '&rated=1');
        exit;
    }
    $zprava  = $vysledek;
    $recenze = getReviewsForLlm($conn, $id);
    $rating  = getAvgRatingForLlm($conn, $id);
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($llm['name']) ?> – ExplorAI</title>
  <link href="https://fonts.googleapis.com/css2?family=Inclusive+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-scroll">

  <?php require_once 'navbar.php'; ?>

  <a class="detail-back" href="index.php">← Zpět na seznam</a>

  <div class="detail-panel">

    <!-- LLM card -->
    <div class="detail-card">
      <div class="detail-logo">
        <img src="img/<?= htmlspecialchars($llm['logo']) ?>"
             alt="<?= htmlspecialchars($llm['name']) ?>"
             onerror="this.style.display='none'">
      </div>
      <div class="detail-info">
        <h1 class="detail-name"><?= htmlspecialchars($llm['name']) ?></h1>
        <p class="detail-desc"><?= htmlspecialchars($llm['description']) ?></p>
        <a href="<?= htmlspecialchars($llm['url']) ?>" target="_blank" class="detail-visit">
          Navštívit web →
        </a>
      </div>
    </div>

    <!-- Tags -->
    <div class="detail-section">
      <h2 class="detail-section-title">Kategorie</h2>
      <?php if (!empty($tagy)): ?>
        <div class="detail-tags-list">
          <?php foreach ($tagy as $tag): ?>
            <span class="detail-tag"><?= htmlspecialchars($tag['name']) ?></span>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <p class="detail-empty">Žádné kategorie.</p>
      <?php endif; ?>
    </div>

    <!-- Reviews -->
    <div class="detail-section">
      <h2 class="detail-section-title">
        Hodnocení
        <?php if ($rating['review_count'] > 0): ?>
          <span style="font-size:0.65em;color:#888;margin-left:8px;">
            průměr <?= number_format($rating['avg_rating'], 1) ?> / 5
            (<?= $rating['review_count'] ?> <?= $rating['review_count'] === 1 ? 'hodnocení' : 'hodnocení' ?>)
          </span>
        <?php endif; ?>
      </h2>
      <?php if (!empty($recenze)): ?>
        <?php foreach ($recenze as $r): ?>
          <div class="detail-review-item">
            <span class="detail-stars"><?= renderHvezdicky($r['stars']) ?></span>
            <span class="detail-date"><?= htmlspecialchars(date('j. n. Y', strtotime($r['created_at']))) ?></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="detail-empty">Zatím žádné hodnocení. Buď první!</p>
      <?php endif; ?>
    </div>

    <!-- Rating form -->
    <div class="detail-section">
      <h2 class="detail-section-title">Ohodnotit nástroj</h2>

      <?php if ($zprava === 'success'): ?>
        <p class="detail-msg-success">Hodnocení bylo úspěšně přidáno!</p>
      <?php elseif (!empty($zprava)): ?>
        <p class="detail-msg-error"><?= htmlspecialchars($zprava) ?></p>
      <?php endif; ?>

      <form method="POST" action="detail.php?id=<?= $id ?>" class="detail-form-group">
        <select id="stars" name="stars" class="detail-select" required>
          <option value="">– Vyber hodnocení –</option>
          <option value="5">★★★★★ (5)</option>
          <option value="4">★★★★☆ (4)</option>
          <option value="3">★★★☆☆ (3)</option>
          <option value="2">★★☆☆☆ (2)</option>
          <option value="1">★☆☆☆☆ (1)</option>
        </select>
        <button type="submit" class="detail-submit">Odeslat hodnocení</button>
      </form>

      <p style="margin-top:8px;font-size:clamp(12px,1.2vw,18px);color:#555;">
        nebo
        <a href="rating.php?id=<?= $id ?>" style="color:#1919EF;">otevřít grafické hodnocení</a>
      </p>
    </div>

  </div>

</body>
</html>
