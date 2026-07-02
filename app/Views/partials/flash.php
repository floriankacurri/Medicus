<?php
// ...existing code...
// Flash partial — reads from session and displays a single flash message
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
if (!empty($_SESSION['flash'])):
  $flash = $_SESSION['flash'];
  $type = $flash['type'] ?? 'info';
  $message = $flash['message'] ?? '';
  // map to css class
  $class = 'alert-info';
  if ($type === 'success') $class = 'alert-success';
  if ($type === 'error' || $type === 'danger') $class = 'alert-error';
?>
  <div class="page-flash <?php echo $class; ?>" role="status" aria-live="polite">
    <div class="container">
      <div class="flash-inner">
        <span class="flash-icon">
          <?php if ($type === 'success'): ?>
            <i class="fas fa-check-circle"></i>
          <?php elseif ($type === 'error' || $type === 'danger'): ?>
            <i class="fas fa-exclamation-circle"></i>
          <?php else: ?>
            <i class="fas fa-info-circle"></i>
          <?php endif; ?>
        </span>
        <div class="flash-message"><?php echo htmlspecialchars($message); ?></div>
        <button class="flash-close" aria-label="Close" onclick="this.parentElement.parentElement.style.display='none'">&times;</button>
      </div>
    </div>
  </div>
<?php
  unset($_SESSION['flash']);
endif;
// ...existing code...

