<?php
if (!isset($currentUser) || $currentUser['role'] !== 'doctor') { header('Location: ' . BASE_URL . '/'); exit; }
include_once __DIR__ . '/../layouts/header.php';
?>
<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <h1 class="section-title">Kërkesat për takime</h1>
      <div id="requestsArea" class="form-card">Duke ngarkuar...</div>
    </main>
  </div>
</section>

<script>
(function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var el = document.getElementById('requestsArea');
  function load(){
    fetch(BASE + '/api/appointment_requests.php').then(function(res){ if (!res.ok) { el.innerText = 'Dështoi ngarkimi.'; return; } return res.json(); }).then(function(data){
      if (!data || !data.length) { el.innerHTML = '<p>Nuk ka kërkesa.</p>'; return; }
      var html = '';
      data.forEach(function(r){
        var when = (r.appointment_date||'') + ' ' + (r.appointment_time||'');
        html += '<div class="card-box" style="margin-bottom:12px;">';
        html += '<div style="display:flex;justify-content:space-between;align-items:center;"><div><strong>' + (r.patient_name||'Pacient') + '</strong><div style="color:var(--color-text-muted)">' + when + '</div></div>';
        html += '<div><button class="btn btn-primary ap-action" data-id="' + r.id + '" data-status="approved">Aprovo</button> <button class="btn btn-ghost ap-action" data-id="' + r.id + '" data-status="cancelled">Anulo</button></div></div>';
        if (r.reason) html += '<div style="margin-top:8px;color:var(--color-text-muted);">' + (r.reason||'') + '</div>';
        html += '</div>';
      });
      el.innerHTML = html;
      document.querySelectorAll('.ap-action').forEach(function(b){ b.addEventListener('click', function(){ var id=b.getAttribute('data-id'); var status=b.getAttribute('data-status'); fetch(BASE + '/api/update_appointment_status.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({id:parseInt(id,10), status:status})}).then(function(res){ return res.json().catch(function(){ return {}; }); }).then(function(d){ if (d && d.success) load(); else alert(d.error||'Dështoi'); }); }); });
    }).catch(function(){ el.innerText = 'Gabim'; });
  }
  load();
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
