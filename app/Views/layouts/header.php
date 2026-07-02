<?php
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
$u = $currentUser ?? null;
$pageTitle = $pageTitle ?? 'Medicus Hospital';
?>
<!DOCTYPE html>
<html lang="sq">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($pageTitle); ?> – Medicus</title>
  <link rel="stylesheet" href="<?php echo ASSETS_URL; ?>/assets/css/app.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <!-- FullCalendar JS -->
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
</head>
<body>
  <script>
    window.BASE = '<?php echo rtrim(BASE_URL, "/"); ?>';
    window.ASSETS = '<?php echo rtrim(ASSETS_URL, "/"); ?>';
  </script>
  <div class="top-header">
    <div class="container">
      <div class="left">
        <span><i class="fas fa-phone"></i> Tel. +355 (0) 42 35 75 35</span>

        <div class="social-icons">
          <a href="https://www.facebook.com" target="_blank" rel="noopener" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
          <a href="https://www.instagram.com" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
          <a href="https://www.youtube.com" target="_blank" rel="noopener" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
          <a href="https://www.twitter.com" target="_blank" rel="noopener" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
        </div>
      </div>
      <div class="right">
        <?php if ($u): ?>
          <span class="user-info">
            <i class="fas fa-user-circle"></i>
            <strong><?php echo htmlspecialchars($u['name'] ?? $u['email'] ?? $u['username'] ?? 'User'); ?></strong>
            <?php if (isset($u['role'])): ?>
              <span class="badge"><?php echo htmlspecialchars(ucfirst($u['role'])); ?></span>
            <?php endif; ?>
          </span>
        <?php endif; ?>
        <!-- <input type="text" placeholder="Kërko..." class="search-bar" aria-label="Kërko"> -->
      </div>
    </div>
  </div>

  <header class="main-header">
    <div class="container">
      <a href="<?php echo BASE_URL; ?>/" class="logo">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/logo1.png" alt="Medicus">
        <span>Medicus Hospital</span>
      </a>

      <nav class="navbar">
        <a href="<?php echo BASE_URL; ?>/"><i class="fas fa-home"></i> Kryefaqja</a>
        <a href="<?php echo BASE_URL; ?>/sherbimet">Shërbimet</a>
        <a href="<?php echo BASE_URL; ?>/tereja">Të Reja</a>
        <div class="dropdown">
          <a href="#" class="dropbtn">Rreth Nesh <i class="fas fa-caret-down"></i></a>
          <div class="dropdown-content">
            <a href="<?php echo BASE_URL; ?>/rrethnesh">Rreth nesh</a>
            <a href="<?php echo BASE_URL; ?>/stafimjekesor">Stafi mjekësor</a>
            <a href="<?php echo BASE_URL; ?>/deget">Degët</a>
          </div>
        </div>
        <?php if ($u): ?>
          <?php if ($u['role'] === 'doctor'): ?>
            <a href="<?php echo BASE_URL; ?>/doctor/dashboard" class="cta">Paneli i Mjekut</a>
          <?php elseif ($u['role'] === 'patient'): ?>
            <a href="<?php echo BASE_URL; ?>/patient/dashboard" class="cta">Paneli im</a>
          <?php endif; ?>
          <a href="<?php echo BASE_URL; ?>/logout">Dil</a>
        <?php else: ?>
          <a href="<?php echo BASE_URL; ?>/login">Hyr</a>
          <a href="<?php echo BASE_URL; ?>/register" class="cta">Regjistrohu</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

  <main>
    <?php // include flash messages if any ?>
    <?php include __DIR__ . '/../partials/flash.php'; ?>
  <script>
    // Highlight active sidebar link and enable smooth scroll for same-page anchors
    (function(){
      function initSidebarHelpers() {
        try {
          var path = window.location.pathname.replace(/\/$/, '');
          var links = document.querySelectorAll('.sidebar-link, .sidebar-btn');
          links.forEach(function(a){
            a.classList.remove('active');
          });

          // highlight best match
          var best = null; var bestLen = 0;
          links.forEach(function(a){
            var href = a.getAttribute('href') || '';
            // ignore empty and external
            if (!href || href.indexOf('http') === 0) return;
            // get path only, strip query and fragment
            var u = href.split('?')[0].split('#')[0].replace(/\/$/, '');
            // If href contains full origin, strip it
            u = u.replace(window.location.origin, '');
            if (u === path) { best = a; bestLen = u.length; }
            else if (path.indexOf(u) === 0 && u.length > bestLen) { best = a; bestLen = u.length; }
          });
          if (best) best.classList.add('active');

          // smooth scroll for same-page anchors and click active update
          links.forEach(function(a){
            var href = a.getAttribute('href') || '';
            if (href.indexOf('#') !== -1 && href.indexOf(window.location.pathname) !== -1) {
              a.addEventListener('click', function(e){
                e.preventDefault();
                var id = href.split('#')[1];
                var el = document.getElementById(id);
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.querySelectorAll('.sidebar-link.active, .sidebar-btn.active').forEach(function(x){ x.classList.remove('active'); });
                a.classList.add('active');
                history.pushState(null, '', href);
              });
            } else {
              // update active class on navigation click
              a.addEventListener('click', function(){
                document.querySelectorAll('.sidebar-link.active, .sidebar-btn.active').forEach(function(x){ x.classList.remove('active'); });
                a.classList.add('active');
              });
            }
          });
        } catch (e) { console.error(e); }
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebarHelpers);
      } else {
        initSidebarHelpers();
      }
      window.addEventListener('popstate', initSidebarHelpers);
    })();
  </script>
