<?php
$pageTitle = 'Blog';
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner"><div class="sherbimet-title">Blog Mjekesor</div></section>
<section class="page-container">
  <div class="blog-lista" style="display:grid;gap:20px;max-width:800px;margin:0 auto;">

    <div class="card-box blog-artikull">
      <h3 style="margin:0 0 8px;font-size:1.1rem;">Si ta kuptoni presionin e lartë të gjakut</h3>
      <p>Shenjat, rreziqet dhe kur duhet të bëni kontrollin mjekësor...</p>
      <a href="#">Lexo më shumë</a>
    </div>

    <div class="card-box blog-artikull">
      <h3 style="margin:0 0 8px;font-size:1.1rem;">Analizat bazë që çdo njeri duhet të bëjë çdo vit</h3>
      <p>Këshilla për të ruajtur shëndetin përmes analizave të rregullta.</p>
      <a href="#">Lexo më shumë</a>
    </div>

    <div class="card-box blog-artikull">
      <h3 style="margin:0 0 8px;font-size:1.1rem;">Stresi dhe ndikimi i tij në organizëm</h3>
      <p>Si ndikon stresi në zemër, sistemin imunitar dhe në gjumë...</p>
      <a href="#">Lexo më shumë</a>
    </div>

  </div>
</section>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 
