<?php
require_once 'db.php';
require_once 'functions.php';

$selected_ids = [];
if (!empty($_GET['tags'])) {
    $selected_ids = array_values(array_filter(array_map('intval', explode(',', $_GET['tags']))));
}

$llms     = getLlmsWithRating($conn, $selected_ids);
$tags     = getAllTags($conn);
$tagLlmMap = getTagLlmMap($conn);

// All LLM IDs for "no tag selected" baseline
$allLlmIds = array_column(getLlmsWithRating($conn), 'id');
?>
<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ExplorAI</title>
  <link href="https://fonts.googleapis.com/css2?family=Inclusive+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-full">

  <?php require_once 'navbar.php'; ?>

  <div class="content">

    <a class="mobile-search-link" href="tags.php<?= !empty($selected_ids) ? '?tags=' . implode(',', $selected_ids) : '' ?>">Search</a>

    <aside class="search-panel">
      <button class="search-btn" id="searchBtn">Search</button>
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
    </aside>

    <main class="main-area">
      <div class="ai-panel">
        <div class="ai-list" id="aiList">
          <?php if (empty($llms)): ?>
            <div style="color:#FFF;text-align:center;padding:40px;font-size:clamp(14px,1.8vw,26px);">
              Žádné AI nástroje neodpovídají vybraným kategoriím.
            </div>
          <?php else: ?>
            <?php foreach ($llms as $llm): ?>
              <?php $avg = $llm['avg_rating'] > 0 ? number_format($llm['avg_rating'], 1) : '–'; ?>
              <div class="ai-card" onclick="location.href='detail.php?id=<?= $llm['id'] ?>'">
                <div class="ai-logo">
                  <img src="img/<?= htmlspecialchars($llm['logo']) ?>"
                       alt="<?= htmlspecialchars($llm['name']) ?>"
                       onerror="this.style.display='none'">
                </div>
                <div class="ai-info">
                  <div class="ai-top">
                    <span class="ai-name"><?= htmlspecialchars($llm['name']) ?></span>
                    <div class="ai-badges">
                      <?php
                        $pricingLabel = ['free' => 'FREE', 'freemium' => 'FREE+', 'paid' => 'PRO'];
                        $p = $llm['pricing'];
                      ?>
                      <span class="ai-pricing pricing-<?= $p ?>"><?= $pricingLabel[$p] ?></span>
                      <a class="ai-rating"
                         href="rating.php?id=<?= $llm['id'] ?>"
                         onclick="event.stopPropagation()">
                        <span class="rating-score"><?= $avg ?></span>
                        <svg viewBox="0 0 24 24" fill="white">
                          <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                      </a>
                    </div>
                  </div>
                  <p class="ai-desc"><?= htmlspecialchars($llm['description']) ?></p>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div class="ai-scrollbar-track" id="aiTrack">
          <div class="ai-scrollbar-thumb" id="aiThumb"></div>
        </div>
      </div>
    </main>

  </div>

  <script>
    const tagLlms   = <?= json_encode($tagLlmMap) ?>;
    const allLlmIds = new Set(<?= json_encode($allLlmIds) ?>);

    // ── POČTY U TAGŮ ──
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

    // ── TAG TOGGLE ──
    document.querySelectorAll('.tag-circle').forEach(btn => {
      btn.addEventListener('click', () => {
        btn.classList.toggle('active');
        updateCounts();
      });
    });

    // ── SEARCH ──
    document.getElementById('searchBtn').addEventListener('click', function () {
      const ids = [...document.querySelectorAll('.tag-circle.active')].map(b => b.dataset.id);
      window.location.href = ids.length ? 'index.php?tags=' + ids.join(',') : 'index.php';
    });

    // ── SCROLLBARS ──
    function initScrollbar(list, thumb, track) {
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
    }

    initScrollbar(document.getElementById('tagList'), document.getElementById('tagThumb'), document.getElementById('tagTrack'));
    initScrollbar(document.getElementById('aiList'),  document.getElementById('aiThumb'),  document.getElementById('aiTrack'));

    // Init counts on load
    updateCounts();
  </script>

</body>
</html>
