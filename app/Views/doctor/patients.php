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
      <h1 class="section-title">Pacientët e mi</h1>
      <div class="form-card" style="min-width: max-content;">
        <div style="display:flex;gap:8px;align-items:center;margin-bottom:12px;">
          <input id="patientSearch" type="text" placeholder="Kërko emrin ose email" style="flex:1;padding:8px;border:1px solid var(--color-border);border-radius:6px;">
          <select id="pageSize" style="width:120px;padding:8px;border-radius:6px;border:1px solid var(--color-border);">
            <option value="10">10 / faqe</option>
            <option value="25">25 / faqe</option>
            <option value="50">50 / faqe</option>
          </select>
        </div>
        <div id="patientsList">Duke ngarkuar...</div>
        <div id="patientsPager" style="margin-top:12px;display:flex;gap:8px;align-items:center;justify-content:center;"></div>
      </div>
    </main>
  </div>
</section>

<script>
(function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var el = document.getElementById('patientsList');
  var pager = document.getElementById('patientsPager');
  var search = document.getElementById('patientSearch');
  var pageSizeSel = document.getElementById('pageSize');
  var dataAll = [];
  var page = 1;

  function renderPage() {
    var q = (search.value || '').toLowerCase().trim();
    var filtered = dataAll.filter(function(p){ return !q || (p.name||'').toLowerCase().indexOf(q) !== -1 || (p.email||'').toLowerCase().indexOf(q) !== -1; });
    var pageSize = parseInt(pageSizeSel.value, 10) || 10;
    var total = filtered.length; var pages = Math.max(1, Math.ceil(total / pageSize));
    if (page > pages) page = pages;
    var start = (page-1)*pageSize; var slice = filtered.slice(start, start+pageSize);
    if (!slice.length) { el.innerHTML = '<p>Nuk u gjetën pacientë.</p>'; pager.innerHTML = ''; return; }
    var html = '<table><thead><tr><th>Emri</th><th>Email</th><th>Gjinia</th><th>Data e lindjes</th><th>Veprime</th></tr></thead><tbody>';
    slice.forEach(function(p){ html += '<tr><td>' + (p.name||'') + '</td><td>' + (p.email||'') + '</td><td>' + (p.gender||'') + '</td><td>' + (p.date_of_birth||'') + '</td><td><a class="btn btn-ghost" href="' + BASE + '/healthcard?user_id=' + p.user_id + '">Shiko karta</a></td></tr>'; });
    html += '</tbody></table>';
    el.innerHTML = html;
    // pager
    var ph = '';
    ph += '<button class="btn btn-ghost" id="prev" ' + (page<=1?'disabled':'') + '>« Mbrapa</button>';
    ph += '<span style="padding:6px 8px;">Faqja ' + page + ' / ' + pages + '</span>';
    ph += '<button class="btn btn-ghost" id="next" ' + (page>=pages?'disabled':'') + '>Përpara »</button>';
    pager.innerHTML = ph;
    document.getElementById('prev').addEventListener('click', function(){ if (page>1) { page--; renderPage(); } });
    document.getElementById('next').addEventListener('click', function(){ if (page<pages) { page++; renderPage(); } });
  }

  function load() {
    fetch(BASE + '/api/patients.php').then(function(res){ if (!res.ok) { el.innerText = 'Dështoi ngarkimi.'; return; } return res.json(); }).then(function(data){ dataAll = data || []; page = 1; renderPage(); }).catch(function(){ el.innerText='Gabim'; });
  }

  search.addEventListener('input', function(){ page = 1; renderPage(); });
  pageSizeSel.addEventListener('change', function(){ page = 1; renderPage(); });
  load();
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
