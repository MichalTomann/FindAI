<?php
require_once 'db.php';
require_once 'functions.php';

$tags      = getAllTags($conn);
$tagLlmMap = getTagLlmMap($conn);
$allLlmIds = array_column(getLlmsWithRating($conn), 'id');

$selected_ids = [];
if (!empty($_GET['tags'])) {
    $selected_ids = array_values(array_filter(array_map('intval', explode(',', $_GET['tags']))));
}
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ExplorAI - Search</title>
  <link href="https://fonts.googleapis.com/css2?family=Inclusive+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-modal">

  <div class="tags-blurred-bg"></div>

  <div class="tags-modal">
    <div class="tags-box">

      <div class="tags-title">Search</div>

      <div class="tag-list-wrapper">
        <div class="tag-list" id="tagList">
          <?php foreach ($tags as $tag): ?>
            <?php $active = in_array($tag['id'], $selected_ids) ? 'active' : ''; ?>
            <div class="tag-row" data-tag-id="<?= $tag['id'] ?>">
              <button class="tag-circle <?= $active ?>" data-id="<?= $tag['id'] ?>"></button>
              <div class="tag-bar">
                <span><?= htmlspecialchars($tag['name']) ?></span>
                <span class="tag-count" id="tc-<?= $tag['id'] ?>">–</span>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
        <div class="scrollbar-track" id="tagTrack">
          <div class="scrollbar-thumb" id="tagThumb"></div>
        </div>
      </div>

      <button class="find-btn" id="findBtn">Find AI</button>

    </div>
  </div>

  <script>
    const tagLlms   = <?= json_encode($tagLlmMap) ?>;
    const allLlmIds = new Set(<?= json_encode($allLlmIds) ?>);

    function computeMatched(selectedIds) {
      if (selectedIds.length === 0) return allLlmIds;
      let matched = null;
      for (const id of selectedIds) {
        const set = new Set(tagLlms[id] || []);
        matched = matched === null ? set : new Set([...matched].filter(x => set.has(x)));
      }
      return matched || new Set();
    }

    function updateCounts() {
      const selectedIds = [...document.querySelectorAll('.tag-circle.active')].map(b => parseInt(b.dataset.id));
      const matched = computeMatched(selectedIds);
      document.querySelectorAll('.tag-row[data-tag-id]').forEach(row => {
        const tagId = parseInt(row.dataset.tagId);
        const el = document.getElementById('tc-' + tagId);
        if (!el) return;
        const llmsForTag = new Set(tagLlms[tagId] || []);
        const count = [...matched].filter(id => llmsForTag.has(id)).length;
        el.textContent = count;
        el.classList.toggle('tag-count-zero', count === 0);
      });
    }

    document.querySelectorAll('.tag-circle').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        updateCounts();
      });
    });

    document.getElementById('findBtn').addEventListener('click', function () {
      const ids = [...document.querySelectorAll('.tag-circle.active')].map(b => b.dataset.id);
      window.location.href = ids.length ? 'index.php?tags=' + ids.join(',') : 'index.php';
    });

    (function () {
      const list  = document.getElementById('tagList');
      const thumb = document.getElementById('tagThumb');
      const track = document.getElementById('tagTrack');
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

    updateCounts();
  </script>

</body>
</html>
