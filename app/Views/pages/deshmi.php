<?php
$pageTitle = 'Dëshmitë';
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Dëshmitë nga Pacientët</div>
</section>

<section class="page-container">
  <div id="deshmiContainer" style="display: grid; gap: 20px; max-width: 700px; margin: 0 auto;">
    <p style="text-align: center; color: var(--color-text-muted);">Duke ngarkuar dëshmitë...</p>
  </div>
</section>

<script>
(function(){
  var container = document.getElementById('deshmiContainer');
  function showFallback() {
    container.innerHTML = '<div class="card-box" style="text-align:center;"><p style="margin:0;color:var(--color-text-muted);">Ende nuk ka dëshmi të regjistruara. Pacientët tanë na vlerësojnë shumë – ju lutemi na kontaktoni për të ndarë përvojën tuaj.</p></div>';
  }
  fetch('/api/kontakti/').then(function(res) { return res.ok ? res.json() : null; }).then(function(data) {
    if (!data || !data.length) { showFallback(); return; }
    container.innerHTML = '';
    data.reverse().forEach(function(m) {
      var card = document.createElement('div');
      card.className = 'card-box';
      card.innerHTML = '<div style="display:flex;gap:16px;align-items:flex-start;"><div style="width:48px;height:48px;border-radius:50%;background:var(--color-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;flex-shrink:0;">' + (m.emri ? m.emri.charAt(0).toUpperCase() : '?') + '</div><div><h4 style="margin:0 0 8px;font-size:1rem;">' + (m.emri || 'Pacient') + '</h4><p style="margin:0;color:var(--color-text-muted);">"' + (m.mesazhi || '') + '"</p></div></div>';
      container.appendChild(card);
    });
  }).catch(showFallback);
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
