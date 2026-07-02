<?php
$pageTitle = 'Shërbimet Mjekësore';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Shërbimet Mjekësore</div>
</section>

<div class="service-grid">
  <a href="<?php echo BASE_URL; ?>/alergologji" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/alergologji.jpg" alt="Alergologji">
    <div class="service-name">Alergologji</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/Anatomopatologji-1.jpg" alt="Anatomopatologji">
    <div class="service-name">Anatomopatologji</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/anestezi.jpg" alt="Anesteziologji">
    <div class="service-name">Anesteziologji</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/Checkup1.jpg" alt="Check Up">
    <div class="service-name">Check Up</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/dermatologji.jpg" alt="Dermatologji">
    <div class="service-name">Dermatologji</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/dializa.jpg" alt="Dializë">
    <div class="service-name">Qendrat e Dializës</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/Endokrinologji.jpg" alt="Endokrinologji">
    <div class="service-name">Endokrinologji</div>
  </a>
  <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-card">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/gastrologji.jpg" alt="Gastroenterologji">
    <div class="service-name">Gastroenterologji</div>
  </a>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
