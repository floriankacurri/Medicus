<?php
$pageTitle = 'Të Reja';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Të Reja</div>
</section>

<div class="page-container">
  <h2 class="section-title">Të Reja, Informacione Mjekësore</h2>
  <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-bottom: 32px;">
    <a href="#" class="card-box" style="padding: 0; overflow: hidden;">
      <img src="<?php echo ASSETS_URL; ?>/assets/img/caj.jpg" alt="" style="width: 100%; height: 160px; object-fit: cover;">
      <div style="padding: 16px;">
        <div style="font-weight: 600;">Çaji me Flavonoide – Fuqi natyrale për zemrën</div>
        <div class="desc">Të Reja</div>
      </div>
    </a>
    <a href="#" class="card-box" style="padding: 0; overflow: hidden;">
      <img src="<?php echo ASSETS_URL; ?>/assets/img/ozempic.jpg" alt="" style="width: 100%; height: 160px; object-fit: cover;">
      <div style="padding: 16px;">
        <div style="font-weight: 600;">Ozempic: Inovacioni në trajtimin e diabetit</div>
        <div class="desc">Të Reja</div>
      </div>
    </a>
    <a href="#" class="card-box" style="padding: 0; overflow: hidden;">
      <img src="<?php echo ASSETS_URL; ?>/assets/img/3d.webp" alt="" style="width: 100%; height: 160px; object-fit: cover;">
      <div style="padding: 16px;">
        <div style="font-weight: 600;">Printim 3D për shpërndarje precize të barnave</div>
        <div class="desc">Të Reja</div>
      </div>
    </a>
  </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
