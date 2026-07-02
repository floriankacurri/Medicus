<?php
$pageTitle = 'Kontakt';
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Na Kontaktoni</div>
</section>

<section class="page-container">
  <div class="form-card" style="max-width: 520px;">
    <h2>Dërgoni një mesazh</h2>
    <p style="color: var(--color-text-muted); margin: 0 0 20px; font-size: 15px;">Jemi gati t'ju përgjigjemi pyetjeve tuaja.</p>
    <form id="kontaktForm">
      <label for="emri">Emri</label>
      <input type="text" id="emri" name="emri" required placeholder="Emri juaj">
      <label for="email">Email</label>
      <input type="email" id="email" name="email" required placeholder="email@shembull.com">
      <label for="mesazhi">Mesazhi</label>
      <textarea id="mesazhi" name="mesazhi" rows="5" required placeholder="Shkruani mesazhin tuaj..."></textarea>
      <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">Dërgo mesazhin</button>
    </form>
    <p id="statusi" class="mt-2" role="alert"></p>
  </div>
</section>

<script>
(function(){
  var BASE = '<?php echo BASE_URL; ?>';
  document.getElementById('kontaktForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    var statusi = document.getElementById('statusi');
    statusi.textContent = '';
    statusi.className = 'mt-2';
    var data = {
      emri: document.getElementById('emri').value.trim(),
      email: document.getElementById('email').value.trim(),
      mesazhi: document.getElementById('mesazhi').value.trim()
    };
    try {
      var res = await fetch(BASE + '/api/submit_contact.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
      });
      if (res.ok) {
        statusi.textContent = 'Faleminderit. Mesazhi u dërgua. Do t\'ju përgjigjemi së shpejti.';
        statusi.className = 'msg success mt-2';
        document.getElementById('kontaktForm').reset();
      } else {
        var err = await res.json().catch(function(){ return {}; });
        statusi.textContent = err.message || 'Dërgimi dështoi. Provoni përsëri.';
        statusi.className = 'msg error mt-2';
      }
    } catch (err) {
      statusi.textContent = 'Gabim në lidhje. Provoni përsëri.';
      statusi.className = 'msg error mt-2';
    }
  });
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
