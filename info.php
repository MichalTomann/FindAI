<!DOCTYPE html>
<html lang="cs">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ExplorAI – Info</title>
  <link href="https://fonts.googleapis.com/css2?family=Inclusive+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>
<body class="page-full">

  <?php require_once 'navbar.php'; ?>

  <div class="info-panel">
    <div class="info-frame">

      <div class="info-box" id="infoBox">
        <h1 class="info-title">O projektu</h1>
        <p class="info-text">
          ExplorAI je katalog AI nástrojů, který ti pomůže najít ten správný nástroj pro tvoji práci.
          Procházej dostupné modely, filtruj je podle kategorie, čti hodnocení od ostatních uživatelů
          a přidávej vlastní. Každý nástroj má svůj detail s popisem, tagy a průměrným hodnocením.
        </p>
        <p class="info-text">
          Projekt je postavený na PHP a MySQL bez externích frameworků. Data jsou uložena
          v relační databázi a stránka je plně responzivní pro desktop i mobil.
        </p>
        <p class="info-text">
          Pomocí vyhledávání podle kategorií (tagů) můžeš rychle filtrovat AI nástroje podle
          oblasti použití — psaní, kódování, design, hudba a další.
        </p>
      </div>

      <div class="scrollbar-track" id="infoTrack">
        <div class="scrollbar-thumb" id="infoThumb"></div>
      </div>

    </div>
  </div>

  <script>
    (function () {
      const list  = document.getElementById('infoBox');
      const thumb = document.getElementById('infoThumb');
      const track = document.getElementById('infoTrack');
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
