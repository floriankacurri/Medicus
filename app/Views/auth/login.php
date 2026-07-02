<?php
$pageTitle = 'Hyr';
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="form-card">
    <h2>Hyr në llogari</h2>
    <form id="loginForm">
      <label for="email">Email</label>
      <input id="email" type="email" required placeholder="email@shembull.com">
      <label for="password">Fjalëkalimi</label>
      <input id="password" type="password" required placeholder="••••••••">
      <button type="submit" class="btn btn-primary" style="margin-top: 20px; width: 100%;">Hyr</button>
    </form>
    <p id="msg" class="mt-2" role="alert"></p>
    <p class="mt-2" style="font-size: 14px; color: var(--color-text-muted);">
      Nuk keni llogari? <a href="<?php echo BASE_URL; ?>/register">Regjistrohu</a>
    </p>
  </div>
</section>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
  e.preventDefault();
  var msg = document.getElementById('msg');
  msg.textContent = '';
  msg.className = 'mt-2';
  var payload = {
    email: document.getElementById('email').value.trim(),
    password: document.getElementById('password').value
  };
  try {
    var res = await fetch('<?php echo BASE_URL; ?>/api/login.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    var data = await res.json().catch(function(){ return {}; });
    if (res.ok) {
      msg.textContent = 'Hyrje e suksesshme. Duke u ridrejtuar...';
      msg.className = 'msg success mt-2';
      // Redirect based on role: admin -> admin dashboard, doctor -> doctor dashboard, else patient
      var role = (data.user && data.user.role) ? String(data.user.role).toLowerCase() : '';
      if (role === 'admin' || role === 'administrator') {
        window.location = '<?php echo BASE_URL; ?>/admin/dashboard';
      } else if (role === 'doctor') {
        window.location = '<?php echo BASE_URL; ?>/doctor/dashboard';
      } else {
        window.location = '<?php echo BASE_URL; ?>/patient/dashboard';
      }
    } else {
      msg.textContent = data.error || 'Hyrja dështoi.';
      msg.className = 'msg error mt-2';
    }
  } catch (err) {
    msg.textContent = 'Gabim në lidhje. Provoni përsëri.';
    msg.className = 'msg error mt-2';
  }
});
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
