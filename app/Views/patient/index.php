<?php
if (!isset($currentUser)) { header('Location: ' . BASE_URL . '/'); exit; }
$userName = htmlspecialchars($currentUser['name']);
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <h1 class="section-title">Paneli i pacientit</h1>
      <p style="text-align: center; color: var(--color-text-muted);">Mirë se vini, <?php echo $userName; ?>!</p>

      <div id="summary" class="form-card" style="max-width: 100%;">
        <p style="margin: 0;">Duke ngarkuar përmbledhjen e takimeve...</p>
      </div>

      <div id="appointmentsList" style="display: none; margin-top: 24px;">
        <h2 style="font-size: 1.25rem; margin-bottom: 12px;">Takimet tuaja</h2>
        <div id="appts" class="cards-grid"></div>
      </div>
    </main>
  </div>
</section>

<script>
(function(){
  var BASE = '<?php echo BASE_URL; ?>';

  async function loadSummary() {
    var res = await fetch(BASE + '/api/my_appointments.php');
    var summary = document.getElementById('summary');
    var apptsDiv = document.getElementById('appts');
    if (!res.ok) { summary.innerHTML = '<p style="margin:0;color:var(--color-error);">Dështoi ngarkimi i takimeve.</p>'; return; }
    var data = await res.json();
    if (!data) { summary.innerHTML = '<p style="margin:0;">Nuk ka të dhëna.</p>'; return; }
    var counts = { pending: 0, approved: 0, cancelled: 0 };
    var next = null;
    data.forEach(function(a){
      var st = a.status || 'pending';
      counts[st] = (counts[st] || 0) + 1;
      var dt = new Date(a.appointment_date + 'T' + a.appointment_time);
      if ((!next || dt < next) && st === 'approved') next = dt;
    });
    summary.innerHTML = '<p style="margin:0;"><strong>Takime:</strong> Në pritje: ' + (counts.pending||0) + ', Aprovuar: ' + (counts.approved||0) + ', Anuluar: ' + (counts.cancelled||0) + '</p>' +
      (next ? '<p style="margin:8px 0 0;"><strong>Takimi i ardhshëm:</strong> ' + next.toLocaleString('sq-AL') + '</p>' : '<p style="margin:8px 0 0;">Nuk keni takime të ardhshme.</p>');

    // render appointment cards
    if (!data.length) {
      apptsDiv.innerHTML = '<div class="card-box" style="padding:16px;">Nuk keni takime.</div>';
    } else {
      var html = '';
      data.forEach(function(r){
        var status = (r.status || '').toLowerCase();
        var statusLabel = status.charAt(0).toUpperCase() + status.slice(1);
        var statusColor = 'var(--color-accent)';
        if (status === 'approved' || status === 'confirmed') statusColor = 'var(--color-success)';
        if (status === 'cancelled' || status === 'refused' || status === 'rejected') statusColor = 'var(--color-error)';
        if (status === 'pending') statusColor = 'var(--color-warning)';
        var doctor = r.doctor_name || r.doctor_id || r.doctor || '–';
        var dt = new Date((r.appointment_date || '') + 'T' + (r.appointment_time || ''));
        var when = r.appointment_date + ' ' + (r.appointment_time || '');
        html += '<div class="card-box" style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">';
        html += '<div style="flex:1;min-width:0;">';
        html += '<div style="font-weight:700;margin-bottom:6px;">' + (doctor ? 'Dr. ' + escapeHtml(doctor) : 'Takim') + ' <span style="font-weight:400;color:var(--color-text-muted);font-size:0.95rem;margin-left:8px;">#' + (r.id||'') + '</span></div>';
        html += '<div style="color:var(--color-text-muted);">' + escapeHtml(when) + '</div>';
        if (r.reason) html += '<div style="margin-top:6px;color:var(--color-text-muted);">' + escapeHtml(r.reason) + '</div>';
        html += '</div>';
        html += '<div style="margin-left:12px;display:flex;flex-direction:column;align-items:flex-end;gap:8px;">';
        html += '<span class="badge" style="background:' + statusColor + ';;">' + escapeHtml(statusLabel) + '</span>';
        html += '</div>';
        html += '</div>';
      });
      apptsDiv.innerHTML = html;
    }
  }
  function escapeHtml(s){ if (s===null || s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  document.getElementById('btnStatus') && document.getElementById('btnStatus').addEventListener('click', function(){
    var el = document.getElementById('appointmentsList');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
    if (el.style.display === 'block') loadSummary();
  });
  loadSummary();
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
