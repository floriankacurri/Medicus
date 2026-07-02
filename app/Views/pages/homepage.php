<?php
$pageTitle = 'Kryefaqja';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
include_once __DIR__ . '/../layouts/header.php';
?>

  <section class="hero">
    <div class="left">
      <div class="slider-container">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/spital11.webp" class="active" alt="Spital Medicus">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/spital1.jpg" alt="Spital Medicus">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/spital3.jpg" alt="Spital Medicus">
      </div>
    </div>
    <div class="right">
      <a href="<?php echo BASE_URL; ?>/rezervoni" class="service-box">
        <i class="bi bi-calendar-check"></i>
        Rezervoni
        <div class="desc">Caktoni një takim online me mjekun tuaj</div>
      </a>
      <a href="<?php echo BASE_URL; ?>/sherbimet" class="service-box">
        <i class="bi bi-bandaid"></i>
        Shërbimet
        <div class="desc">Zbuloni gamën tonë të shërbimeve mjekësore</div>
      </a>
      <a href="<?php echo BASE_URL; ?>/stafimjekesor" class="service-box">
        <i class="bi bi-person-vcard"></i>
        Mjekët
        <div class="desc">Njihuni me stafin tonë të kualifikuar</div>
      </a>
    </div>
  </section>

  <div class="page-container">
    <div class="carousel-container" style="margin: 32px 0;">
      <h2 class="section-title">Të Reja, Informacione Mjekësore</h2>
      <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px;">
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

    <div class="health-card-section" style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center; margin: 48px 0; padding: 24px; background: var(--color-surface); border-radius: var(--radius-lg); border: 1px solid var(--color-border);">
      <div class="card-image" style="flex: 0 0 280px;">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/Checkup1.jpg" alt="Kartë Shëndeti" style="width: 100%; border-radius: var(--radius);">
      </div>
      <div class="card-info" style="flex: 1; min-width: 260px;">
        <h2 style="margin: 0 0 12px; font-size: 1.35rem;">Kartat e shëndetit Medicus</h2>
        <p style="color: var(--color-text-muted); margin: 0 0 16px; font-size: 15px;">
          Për të qenë më pranë pacientëve, Spitali Medicus Tiranë ofron Kartat e Shëndetit – me qëllim që të jenë sa më pranë kërkesave të çdo pacienti. Kartat kanë vlefshmëri 1-vjeçare dhe ofrojnë përfitime mjekësore dhe financiare.
        </p>
        <a href="<?php echo BASE_URL; ?>/healthcard" class="btn btn-primary">Më shumë</a>
      </div>
    </div>

    <section class="section-services" style="margin: 48px 0;">
      <h1 class="services-title">Shërbimi më i mirë për ju!</h1>
      <p class="services-subtitle">Në Spitalin Medicus do të gjeni shërbimin më të mirë.</p>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 24px;">
        <div class="card-box">
          <div style="font-size: 1.5rem; color: var(--color-primary); margin-bottom: 8px;"><i class="bi bi-truck"></i></div>
          <h3 style="margin: 0 0 8px; font-size: 1.1rem;">Urgjenca</h3>
          <p class="desc" style="margin: 0;">Urgjenca e SPITALIT MEDICUS ofron shërbim të specializuar dhe në kohën e duhur, duke menaxhuar me sukses të gjitha rastet me një numër mesatar prej 500 rastesh në muaj.</p>
        </div>
        <div class="card-box">
          <div style="font-size: 1.5rem; color: var(--color-primary); margin-bottom: 8px;"><i class="bi bi-person-heart"></i></div>
          <h3 style="margin: 0 0 8px; font-size: 1.1rem;">Stafi mjekësor</h3>
          <p class="desc" style="margin: 0;">Stafi përbëhet nga mjekë shqiptarë dhe të huaj me edukim në universitete të njohura dhe eksperiencë shumëvjeçare. Profesionalizmi dhe përkushtimi janë garanci për trajtim të standardeve të larta.</p>
        </div>
        <div class="card-box">
          <div style="font-size: 1.5rem; color: var(--color-primary); margin-bottom: 8px;"><i class="bi bi-clipboard2-pulse"></i></div>
          <h3 style="margin: 0 0 8px; font-size: 1.1rem;">Check up</h3>
          <p class="desc" style="margin: 0;">CHECK-UP-i është një kontroll i përgjithshëm i shëndetit për të zbuluar sëmundje në kohë. Ofrojmë pako të personalizuara për çdo pacient.</p>
        </div>
      </div>
    </section>
  </div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
