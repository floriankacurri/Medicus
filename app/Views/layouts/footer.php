<?php
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
?>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <div class="footer-column">
        <img src="<?php echo ASSETS_URL; ?>/assets/img/logo1.png" alt="Medicus" class="footer-logo">
        <ul class="social-icons">
          <li><i class="fab fa-facebook"></i> spitalimedicus</li>
          <li><i class="fab fa-instagram"></i> spitalimedicus</li>
          <li><i class="fab fa-linkedin"></i> spitalimedicus</li>
        </ul>
      </div>
      <div class="footer-column">
        <h4>Spitali Ynë</h4>
        <hr>
        <ul>
          <li><a href="<?php echo BASE_URL; ?>/rrethnesh">Rreth nesh</a></li>
          <li><a href="<?php echo BASE_URL; ?>/stafimjekesor">Mjekët</a></li>
          <li><a href="<?php echo BASE_URL; ?>/sherbimet">Shërbimet</a></li>
          <li><a href="<?php echo BASE_URL; ?>/blog">Blog</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h4>Shërbime</h4>
        <hr>
        <ul>
          <li><a href="<?php echo BASE_URL; ?>/alergologji">Alergologji</a></li>
          <li><a href="<?php echo BASE_URL; ?>/sherbimet">Check Up</a></li>
          <li><a href="<?php echo BASE_URL; ?>/rezervoni">Rezervo takim</a></li>
        </ul>
      </div>
      <div class="footer-column">
        <h4>Vendndodhja</h4>
        <hr>
        <div class="map-container">
          <iframe src="https://www.google.com/maps/embed?pb=!1m16!1m12!1m3!1d23971.826447010943!2d19.746481774316415!3d41.32021150000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!2m1!1sspitali%20amerikan!5e0!3m2!1sen!2s!4v1747732708995!5m2!1sen!2s" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Harta"></iframe>
        </div>
      </div>
    </div>
    <div class="footer-bottom">© Medicus Hospital. Të gjitha të drejtat e rezervuara.</div>
  </footer>

  <!-- Global toast container -->
  <div id="globalToast" role="status" aria-live="polite" class=""></div>

  <script>
    // showToast(message, { type: 'success'|'error', durationMs: 4000 })
    function showToast(message, opts) {
      opts = opts || {};
      var type = opts.type || 'success';
      var duration = opts.durationMs || 4000;
      var el = document.getElementById('globalToast');
      if (!el) return;
      el.className = '';
      el.classList.add(type === 'error' ? 'error' : 'success');
      el.innerHTML = '<button class="toast-close" aria-label="Close" onclick="(function(){ var e=document.getElementById(\'globalToast\'); e.classList.remove(\'show\');})(); return false;">&times;</button>' + String(message);
      setTimeout(function(){ el.classList.add('show'); }, 10);
      if (duration > 0) {
        setTimeout(function(){ el.classList.remove('show'); }, duration + 20);
      }
    }
  </script>

  <script src="<?php echo ASSETS_URL; ?>/assets/js/app.js"></script>
</body>
</html>
