<?php
$pageTitle = 'Rezervoni';
if (!defined('ASSETS_URL')) { define('ASSETS_URL', '/Medicus/public'); }
if (!defined('BASE_URL')) { define('BASE_URL', '/Medicus'); }
$loggedIn = isset($currentUser) && $currentUser;
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <section class="sherbimet-banner">
        <div class="sherbimet-title">Rezervoni një takim</div>
      </section>

      <div class="form-card" style="max-width: 640px; margin: 0 auto;">
        <?php if (!$loggedIn): ?>
          <p style="margin: 0 0 20px; padding: 16px; background: #fef3c7; border-radius: var(--radius); color: #92400e;">
            Për të rezervuar një takim duhet të <a href="<?php echo BASE_URL; ?>/login">hyni</a> ose të <a href="<?php echo BASE_URL; ?>/register">regjistroheni</a>.
          </p>
        <?php endif; ?>

        <div id="bookingRoot">
          <div id="stepper" style="display:flex;gap:8px;margin-bottom:16px;justify-content:space-between;">
            <div class="step active">1. Zgjidh Mjekun</div>
            <div class="step">2. Zgjidh Data & Ora</div>
            <div class="step">3. Konfirmo</div>
          </div>

          <div id="step-1" class="booking-step">
            <label for="doctorSelect">Zgjidh mjekun (i detyrueshëm)</label>
            <select id="doctorSelect" required>
              <option value="">Zgjidh mjekun</option>
            </select>
            <div id="doctorsLoading" style="margin-top:12px;color:var(--color-text-muted);">Duke ngarkuar mjekët...</div>
            <div style="margin-top:8px;color:var(--color-text-muted);font-size:0.9rem;">Zgjidhni një mjek për të parë oraret e disponueshme.</div>
            <div style="margin-top:16px;display:flex;gap:8px;">
              <button class="btn btn-primary" id="toStep2" <?php echo $loggedIn ? '' : 'disabled'; ?>>Vazhdoni</button>
              <a href="<?php echo BASE_URL; ?>/" class="btn btn-ghost">Anulo</a>
            </div>
          </div>

          <div id="step-2" class="booking-step" style="display:none;">
            <label for="date">Data</label>
            <input type="date" id="date" required>

            <label for="time" style="margin-top:12px;">Ora</label>
            <select id="time" required>
              <option value="">Zgjidh datën për të parë oraret</option>
            </select>
            <div id="slotsLoading" style="color:var(--color-text-muted);margin-top:8px;display:none">Duke ngarkuar oraret...</div>

            <p style="margin-top:12px;color:var(--color-text-muted);">Kohëzgjatja e takimit: 30 minuta (përcaktohet nga sistemi)</p>

            <label for="reason" style="margin-top:12px;">Arsyeja (opsionale)</label>
            <textarea id="reason" placeholder="Shënime për mjekun (opsional)" style="min-height:80px"></textarea>
            <div style="margin-top:16px;display:flex;gap:8px;">
              <button class="btn btn-ghost" id="backTo1">Mbrapa</button>
              <button class="btn btn-primary" id="toStep3">Vazhdoni</button>
            </div>
          </div>

          <div id="step-3" class="booking-step" style="display:none;">
            <h3>Konfirmimi</h3>
            <div id="review" style="margin-bottom:12px;color:var(--color-text-muted);"></div>
            <div style="display:flex;gap:8px;">
              <button class="btn btn-ghost" id="backTo2">Mbrapa</button>
              <button class="btn btn-primary" id="submitBooking">Dërgo kërkesën</button>
            </div>
          </div>

          <p id="bookingStatus" class="mt-2" role="status" aria-live="polite"></p>
        </div>
      </div>
    </main>
  </div>
</section>

<script src="<?php echo ASSETS_URL; ?>/assets/js/booking.js"></script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
