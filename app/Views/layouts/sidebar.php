<?php
// Sidebar partial — expects $currentUser to be available
$role = $currentUser['role'] ?? 'guest';
?>
<div class="sidebar-inner" role="navigation" aria-label="Main sidebar">
  <div class="sidebar-brand">
    <img src="<?php echo ASSETS_URL; ?>/assets/img/logo1.png" alt="Medicus" style="max-height:44px;">
  </div>

  <!-- Main navigation (Dashboard first) -->
  <nav class="sidebar-nav">
    <?php if ($role === 'patient'): ?>
      <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="sidebar-link"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a>
      <a href="<?php echo BASE_URL; ?>/patient/appointments" class="sidebar-link"><i class="fa fa-calendar-check"></i> <span>Takimet e mia</span></a>
      <a href="<?php echo BASE_URL; ?>/rezervoni" class="sidebar-link"><i class="fa fa-calendar-plus"></i> <span>Rezervo takim</span></a>
      <a href="<?php echo BASE_URL; ?>/healthcard" class="sidebar-link"><i class="fa fa-folder-open"></i> <span>Karta e shëndetit</span></a>
    <?php elseif ($role === 'doctor'): ?>
      <a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="sidebar-link"><i class="fa fa-tachometer-alt"></i> <span>Dashboard</span></a>
      <a href="<?php echo BASE_URL; ?>/doctor/appointments" class="sidebar-link"><i class="fa fa-calendar-check"></i> <span>Takimet</span></a>
      <a href="<?php echo BASE_URL; ?>/doctor/patients" class="sidebar-link"><i class="fa fa-user-injured"></i> <span>Pacientët</span></a>
      <a href="<?php echo BASE_URL; ?>/doctor/requests" class="sidebar-link"><i class="fa fa-inbox"></i> <span>Kërkesat</span></a>
      <a href="<?php echo BASE_URL; ?>/doctor/schedule" class="sidebar-link"><i class="fa fa-calendar-alt"></i> <span>Orari</span></a>
    <?php else: ?>
      <a href="<?php echo BASE_URL; ?>/" class="sidebar-link"><i class="fa fa-home"></i> <span>Kryefaqja</span></a>
    <?php endif; ?>
  </nav>

  <!-- Separator -->
  <div class="sidebar-sep" aria-hidden="true"></div>

  <!-- Role-specific quick actions (prominent buttons) -->
  <div class="sidebar-actions" style="margin-top:12px;">
    <?php if ($role === 'patient'): ?>
      <a href="<?php echo BASE_URL; ?>/profile" class="btn btn-ghost sidebar-btn"><i class="fa fa-user"></i> <span>Ndrysho profilin</span></a>
      <a href="<?php echo BASE_URL; ?>/logout" class="btn btn-ghost sidebar-btn"><i class="fa fa-sign-out-alt"></i> <span>Dil</span></a>
    <?php elseif ($role === 'doctor'): ?>
      <a href="<?php echo BASE_URL; ?>/profile" class="btn btn-ghost sidebar-btn"><i class="fa fa-user"></i> <span>Ndrysho profilin</span></a>
      <a href="<?php echo BASE_URL; ?>/logout" class="btn btn-ghost sidebar-btn"><i class="fa fa-sign-out-alt"></i> <span>Dil</span></a>
    <?php else: ?>
      <a href="<?php echo BASE_URL; ?>/login" class="btn btn-primary sidebar-btn"><i class="fa fa-sign-in-alt"></i> <span>Hyr</span></a>
    <?php endif; ?>
  </div>
</div>
