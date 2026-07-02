<?php
$pageTitle = 'Regjistrohu';
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="form-card" style="max-width: 520px;">
    <h2>Regjistrohu</h2>
    <form id="registerForm">
      <label for="name">Emri i plotë</label>
      <input id="name" type="text" required placeholder="Emri Mbiemri">
      <label for="email">Email</label>
      <input id="email" type="email" required placeholder="email@shembull.com">
      <label for="password">Fjalëkalimi</label>
      <input id="password" type="password" required placeholder="••••••••">
      <label for="role">Lloji i llogarisë</label>
      <select id="role" disabled="disabled">
        <option value="patient" selected>Pacient</option>
        <!-- <option value="doctor">Mjek</option> -->
      </select>
      <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">Regjistrohu</button>
    </form>
    <p id="msg" class="mt-2" role="alert"></p>
    <p class="mt-2" style="font-size: 14px; color: var(--color-text-muted);">
      Keni tashmë llogari? <a href="<?php echo BASE_URL; ?>/login">Hyr</a>
    </p>
  </div>
</section>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e){
  e.preventDefault();
  var msg = document.getElementById('msg');
  msg.textContent = '';
  msg.className = 'mt-2';
  var payload = {
    name: document.getElementById('name').value.trim(),
    email: document.getElementById('email').value.trim(),
    password: document.getElementById('password').value,
    role: document.getElementById('role').value
  };
  try {
    var res = await fetch('<?php echo BASE_URL; ?>/api/register.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await res.json().catch(function(){ return {}; });
    if (res.ok) {
      msg.textContent = 'Regjistrimi u krye. Mund të hyni tani.';
      msg.className = 'msg success mt-2';
    } else {
      msg.textContent = data.error || 'Regjistrimi dështoi.';
      msg.className = 'msg error mt-2';
    }
  } catch (err) {
    msg.textContent = 'Gabim në lidhje. Provoni përsëri.';
    msg.className = 'msg error mt-2';
  }
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
