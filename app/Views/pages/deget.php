<?php
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Degët</div>
</section>

<div class="page-container">
  <div class="branches-container" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:24px;max-width:1000px;margin:0 auto;">
  <div class="card-box branch-card" style="padding:0;overflow:hidden;">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/Zen.jpg" alt="Spitali Medicus Zen" style="width:100%;height:180px;object-fit:cover;">
    <div style="padding:16px;">
    <h3 style="margin:0 0 8px;font-size:1.1rem;">Spitali Medicus Zen</h3>
    <p style="margin:0 0 8px;color:var(--color-text-muted);">Pranë S.U.T., Rruga Lord Bajron, Tiranë</p>
    <p style="margin:0 0 8px;"><i class="fas fa-map-marker-alt"></i> <a href="#">Shiko në hartë</a></p>
    <p style="margin:0;"><i class="fas fa-phone"></i> +355 (0) 42 35 75 35</p>
    </div>
  </div>

  <div class="card-box branch-card" style="padding:0;overflow:hidden;">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/vita.webp" alt="Spitali Medicus Vita" style="width:100%;height:180px;object-fit:cover;">
    <div style="padding:16px;">
    <h3 style="margin:0 0 8px;font-size:1.1rem;">Spitali Medicus Vita</h3>
    <p style="margin:0 0 8px;color:var(--color-text-muted);">Rruga e Dibrës, Tirana 1000</p>
    <p style="margin:0 0 8px;"><i class="fas fa-map-marker-alt"></i> <a href="#">Shiko në hartë</a></p>
    <p style="margin:0;"><i class="fas fa-phone"></i> +355 (0) 42 35 75 35</p>
    </div>
  </div>

  <div class="card-box branch-card" style="padding:0;overflow:hidden;">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/plus.jpg" alt="Spitali Medicus Plus" style="width:100%;height:180px;object-fit:cover;">
    <div style="padding:16px;">
    <h3 style="margin:0 0 8px;font-size:1.1rem;">Spitali Medicus Plus</h3>
    <p style="margin:0 0 8px;color:var(--color-text-muted);">Rruga Sabaudin Gabrani, Nr. 2, Tiranë</p>
    <p style="margin:0 0 8px;"><i class="fas fa-map-marker-alt"></i> <a href="#">Shiko në hartë</a></p>
    <p style="margin:0;"><i class="fas fa-phone"></i> +355 (0) 42 35 75 35</p>
    </div>
  </div>
  </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 
