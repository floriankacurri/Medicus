<?php
$pageTitle = 'Rreth Nesh';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="sherbimet-banner">
  <div class="sherbimet-title">Rreth Nesh</div>
</section>

<div class="page-container">
  <div style="max-width: 800px; margin: 0 auto;">
    <div style="margin-bottom: 32px; border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow); background: var(--color-surface);">
      <video controls style="width: 100%; display: block;" poster="<?php echo ASSETS_URL; ?>/assets/img/spital1.jpg">
        <source src="<?php echo ASSETS_URL; ?>/assets/img/spitalvideo.mp4" type="video/mp4">
        Shfletuesi juaj nuk e mbështet videon.
      </video>
    </div>

    <div class="form-card" style="max-width: 100%;">
      <h2 style="margin: 0 0 16px; font-size: 1.5rem;">Spitali Medicus</h2>
      <p style="color: var(--color-text-muted); margin: 0 0 24px; line-height: 1.7;">
        Spitali Medicus është një nga institucionet më të avancuara shëndetësore në vend, i përkushtuar për të ofruar kujdes shëndetësor cilësor dhe të personalizuar për çdo pacient. Me një përvojë mbi 20-vjeçare në shërbimin mjekësor, ne sjellim profesionalizëm, teknologji moderne dhe përkujdesje të veçantë për pacientët tanë.
      </p>
      <h3 style="margin: 0 0 8px; font-size: 1.15rem; color: var(--color-primary);">Misioni Ynë</h3>
      <p style="color: var(--color-text-muted); margin: 0 0 24px; line-height: 1.7;">
        Misioni i Spitalit Medicus është të ofrojë shërbime shëndetësore të sigurta, efikase dhe të bazuara në evidencë, duke respektuar dinjitetin e çdo individi. Ne besojmë në përmirësimin e vazhdueshëm të cilësisë dhe zhvillimin e stafit profesional.
      </p>
      <h3 style="margin: 0 0 8px; font-size: 1.15rem; color: var(--color-primary);">Stafi Ynë</h3>
      <p style="color: var(--color-text-muted); margin: 0; line-height: 1.7;">
        Ne përbëhemi nga një ekip i përkushtuar mjekësh specialistë, infermierësh të trajnuar dhe personel mbështetës që punojnë së bashku për të garantuar kujdesin më të mirë të mundshëm.
      </p>
    </div>
  </div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
