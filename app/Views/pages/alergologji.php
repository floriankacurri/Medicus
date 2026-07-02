<?php
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
include_once __DIR__ . '/../layouts/header.php';
?>

<div class="page-container">
<div class="section-alergologji" style="max-width:800px;margin:0 auto;">
  <section class="sherbimet-banner"><div class="sherbimet-title">Alergologji</div></section>
  <img src="<?php echo ASSETS_URL; ?>/assets/img/alergologji.jpg" alt="Alergologji" style="width:100%;max-height:320px;object-fit:cover;border-radius:var(--radius);margin:24px 0;">

  <div class="alergologji-body">
    <p>
      Alergologjia është një specialitet që merret me parandalimin, diagnostikimin dhe trajtimin e sëmundjeve alergjike, patologji këto me ndërmjetësi imunologjike me shkak hipersensibilitetin ndaj alergenëve ajrorë, ushqimorë, medikamentozë dhe të kontaktit, ku përfshihen:
    </p>
    <ul>
      <li>Astma Bronkiale</li>
      <li>Riniti Alergjik</li>
      <li>Anafilaksia</li>
      <li>Urtikaria</li>
      <li>Angioedema</li>
      <li>Dermatiti Atopik</li>
      <li>Dermatiti i Kontaktit</li>
      <li>Alergjia Ushqimore</li>
      <li>Reaksionet Medikamentoze etj.</li>
    </ul>

    <p>Shërbimet e departamentit të alergologjisë të ofruara pranë Spitalit Amerikan janë gjithëpërfshirëse dhe çojnë në vendosjen e diagnozës dhe përcaktimin e trajtimit mbajtës dhe përfundimtar të sëmundjeve alergjike:</p>

    <ol>
      <li><strong>Konsulta:</strong> me mjekun alergolog pas marrjes së një historiku të hollësishëm dhe ekzaminimit fizik.</li>
      <li><strong>Ekzaminimet laboratorike:</strong> IgE specifike për alergenët ajrorë, ushqimorë, medikamentozë dhe për helmin e insekteve si bleta dhe grerëza.</li>
      <li><strong>Ekzaminimet në lëkurë:</strong> Prick test, Patch test, Intradermal test për medikamente të ndryshme.</li>
      <li><strong>Spirometria:</strong> për të evidentuar prekjen e funksionit pulmonar.</li>
      <li><strong>Trajtimi:</strong>
        <ul>
          <li>Edukimi për evitimin e agjentit shkaktar</li>
          <li>Mjekimi medikamentoz dhe përdorimi i saktë i tij</li>
          <li>Imunoterapia specifike (vaksinimi) për ndalimin dhe parandalimin e simptomave</li>
        </ul>
      </li>
    </ol>
  </div>
</div>
</div>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?> 
