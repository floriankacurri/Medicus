<?php
if (!isset($currentUser) || $currentUser['role'] !== 'doctor') { header('Location: ' . (defined('BASE_URL') ? BASE_URL : '/Medicus') . '/'); exit; }
$userName = htmlspecialchars($currentUser['name']);
include_once __DIR__ . '/../layouts/header.php';
$BASE = defined('BASE_URL') ? BASE_URL : '/Medicus';
// patientsCount and pendingRequestsCount are set by controller (defaults to 0 if missing)
$patientsCount = $patientsCount ?? 0;
$pendingRequestsCount = $pendingRequestsCount ?? 0;
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <h1 class="section-title">Paneli i mjekut</h1>
      <p style="text-align: center; color: var(--color-text-muted);">Mirë se vini, Dr. <?php echo $userName; ?>!</p>

      <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-top:20px;">
        <a href="<?php echo $BASE; ?>/doctor/patients" class="card-box" style="text-decoration:none;color:inherit;position:relative;">
          <?php if ($patientsCount): ?><span class="badge" style="position:absolute;top:12px;right:12px"><?php echo $patientsCount; ?></span><?php endif; ?>
          <h3>Pacientët</h3>
          <p style="color:var(--color-text-muted);margin-top:6px;">Shikoni dhe menaxhoni pacientët tuaj.</p>
        </a>
        <a href="<?php echo $BASE; ?>/doctor/requests" class="card-box" style="text-decoration:none;color:inherit;position:relative;">
          <?php if ($pendingRequestsCount): ?><span class="badge" style="position:absolute;top:12px;right:12px;background:var(--color-warning);color:#000"><?php echo $pendingRequestsCount; ?></span><?php endif; ?>
          <h3>Kërkesat për takime</h3>
          <p style="color:var(--color-text-muted);margin-top:6px;">Aprovo ose refuzo kërkesat e reja.</p>
        </a>
        <a href="<?php echo $BASE; ?>/doctor/schedule" class="card-box" style="text-decoration:none;color:inherit;position:relative;">
          <h3>Orari</h3>
          <p style="color:var(--color-text-muted);margin-top:6px;">Caktoni oraret në të cilat jeni i disponueshëm.</p>
        </a>
      </div>

      <p style="margin-top: 24px; text-align: center;"><a href="<?php echo $BASE; ?>/logout" class="btn btn-ghost">Dil</a></p>
    </main>
  </div>
</section>

<script>
// optional quick loads: could load small counts via APIs later
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
