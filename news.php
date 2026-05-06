<?php
require_once 'db.php';
require_once 'functions.php';

$news = getAllNews($conn);
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ExplorAI – News</title>
  <link href="https://fonts.googleapis.com/css2?family=Inclusive+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-full">

  <?php require_once 'navbar.php'; ?>

  <div class="news-panel">

    <div class="news-header">News</div>

    <div class="news-wrapper">
      <div class="news-list" id="newsList">
        <?php if (empty($news)): ?>
          <div class="news-card" style="text-align:center;color:#888;">
            Zatím žádné novinky.
          </div>
        <?php else: ?>
          <?php foreach ($news as $item): ?>
            <div class="news-card">
              <?php if (!empty($item['title'])): ?>
                <div class="news-card-title"><?= htmlspecialchars($item['title']) ?></div>
              <?php endif; ?>
              <div class="news-card-date"><?= htmlspecialchars(date('j. n. Y', strtotime($item['created_at']))) ?></div>
              <?= nl2br(htmlspecialchars($item['content'])) ?>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="news-scrollbar-track" id="newsTrack">
        <div class="news-scrollbar-thumb" id="newsThumb"></div>
      </div>
    </div>

  </div>

  <script>
    (function () {
      const list  = document.getElementById('newsList');
      const thumb = document.getElementById('newsThumb');
      const track = document.getElementById('newsTrack');
      function update() {
        const trackH = track.clientHeight;
        const thumbH = Math.max(30, (list.clientHeight / list.scrollHeight) * trackH);
        const max    = list.scrollHeight - list.clientHeight;
        const ratio  = max > 0 ? list.scrollTop / max : 0;
        thumb.style.height = thumbH + 'px';
        thumb.style.top    = (ratio * (trackH - thumbH)) + 'px';
      }
      list.addEventListener('scroll', update);
      let dragging = false, startY = 0, startScroll = 0;
      thumb.addEventListener('mousedown', e => { dragging = true; startY = e.clientY; startScroll = list.scrollTop; e.preventDefault(); });
      document.addEventListener('mousemove', e => {
        if (!dragging) return;
        const ratio = (list.scrollHeight - list.clientHeight) / (track.clientHeight - thumb.clientHeight);
        list.scrollTop = startScroll + (e.clientY - startY) * ratio;
      });
      document.addEventListener('mouseup', () => { dragging = false; });
      update(); setTimeout(update, 100); setTimeout(update, 300);
    })();
  </script>

</body>
</html>
