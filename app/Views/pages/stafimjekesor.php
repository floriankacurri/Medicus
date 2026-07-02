<?php
$pageTitle = 'Stafi Mjekësor';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Stafi Mjekësor</div>
</section>

<div class="doctor-grid" id="doktorContainer">
  <p style="text-align: center; padding: 24px; color: var(--color-text-muted);">Duke ngarkuar...</p>
</div>

<script>
(function(){
  var BASE = '<?php echo BASE_URL; ?>';
  var ASSETS = '<?php echo ASSETS_URL; ?>';
  fetch(BASE + '/api/doctors.php')
    .then(function(r) { return r.json(); })
    .then(function(lista) {
      var grid = document.getElementById('doktorContainer');
      if (!lista || lista.length === 0) {
        grid.innerHTML = '<p style="text-align:center;padding:24px;color:var(--color-text-muted);">Nuk ka të dhëna mjekësh. Regjistro mjekë për të parë këtu.</p>';
        return;
      }
      grid.innerHTML = '';
      lista.forEach(function(d) {
        var card = document.createElement('div');
        card.className = 'doctor-card';
        card.innerHTML = '<div style="width:100%;height:200px;background:var(--color-background);display:flex;align-items:center;justify-content:center;color:var(--color-text-muted);"><i class="bi bi-person-badge" style="font-size:3rem;"></i></div>' +
          '<h4>Dr. ' + (d.name || 'Unknown') + '</h4><p>' + (d.specialization || 'Përgjithshme') + '</p>';
        grid.appendChild(card);
      });
    })
    .catch(function() {
      document.getElementById('doktorContainer').innerHTML = '<p style="text-align:center;padding:24px;color:var(--color-error);">Gabim në ngarkim. Provoni përsëri.</p>';
    });
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
