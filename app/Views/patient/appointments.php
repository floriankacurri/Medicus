<?php
if (!isset($currentUser) || $currentUser['role'] !== 'patient') {
    header('Location: ' . BASE_URL . '/');
    exit;
}
include_once __DIR__ . '/../layouts/header.php';
$BASE = defined('BASE_URL') ? BASE_URL : '/Medicus';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <h1 class="section-title">Takimet e Mia</h1>
      <p style="color:var(--color-text-muted);">Këtu mund të shikosh takimet e tua të planifikuara dhe kalendarin.</p>

      <!-- View toggle -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;gap:12px;">
        <div>
          <button id="viewToggleList" class="btn btn-ghost active">Listë</button>
          <button id="viewToggleCalendar" class="btn btn-ghost">Kalendari</button>
        </div>
        <div>
          <select id="filterStatus" style="padding:6px;border-radius:6px;border:1px solid var(--color-border);">
            <option value="">Të gjitha</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <div id="patientViews" style="margin-top:16px;">
        <!-- List View -->
        <div id="patientListView">
          <div class="form-card" style="min-width:-webkit-fill-available;"> 
            <h3 style="margin-top:0">Lista e Takimeve</h3>
            <div id="apptList">Duke ngarkuar...</div>
          </div>
        </div>

        <!-- Calendar View -->
        <div id="patientCalendarView" style="display:none;">
          <div class="form-card" style="min-width:-webkit-fill-available;">
            <h3 style="margin-top:0">Kalendari</h3>
            <div id="patientCalendar"></div>
          </div>
        </div>
      </div>

      <!-- Edit/Reschedule Modal (only for pending/refused) -->
      <div id="patientEditModal" class="modal" aria-hidden="true">
        <div class="modal-content" style="max-width:520px">
          <div class="modal-header">
            <h3 style="margin:0">Ndrysho Takimin</h3>
            <button class="modal-close" id="ptModalClose">&times;</button>
          </div>
          <form id="ptEditForm" style="padding:16px;">
            <input type="hidden" id="pt_id">
            <label>Data</label>
            <input id="pt_date" type="date" required style="width:100%;padding:8px;margin-bottom:8px;">

            <label>Ora</label>
            <select id="pt_time" required style="width:100%;padding:8px;margin-bottom:8px;"></select>
            <div id="pt_slots_msg" style="color:var(--color-text-muted);margin-bottom:8px;display:none;"></div>

            <label>Kohëzgjatja (min)</label>
        <?php if (($currentUser['role'] ?? '') === 'patient'): ?>
            <input id="pt_duration" type="number" min="5" step="5" value="30" disabled style="width:100%;padding:8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:1rem;background-color:var(--color-bg-light);color:var(--color-text-muted);cursor:not-allowed;">
            <div style="color:var(--color-text-muted);font-size:0.85rem;margin-top:6px;font-style:italic;">Vetëm mjeku mund të ndryshojë kohëzgjatjen.</div>

        <?php else: ?>
            <input id="pt_duration" type="number" min="5" step="5" value="30" style="width:100%;padding:8px;margin-bottom:8px;">
        <?php endif; ?>

            <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:20px;">
              <button class="btn btn-ghost" type="button" id="pt_cancel_btn">Mbyll</button>
              <button class="btn btn-primary" type="submit">Ruaj</button>
            </div>

            <div id="pt_msg" style="margin-top:10px;display:none;"></div>
          </form>
        </div>
      </div>

    </main>
  </div>
</section>

<!-- Custom Confirmation Modal -->
<div id="confirmOverlay" style="
  display:none;
  position:fixed;
  inset:0;
  background:rgba(0,0,0,0.5);
  z-index:10000;
  justify-content:center;
  align-items:center;
">
  <div style="
    background:white;
    padding:20px 24px;
    border-radius:8px;
    width:90%;
    max-width:380px;
    box-shadow:0 4px 18px rgba(0,0,0,0.25);
    text-align:center;
    font-family:Arial, sans-serif;
  ">
    <div id="confirmText" style="
      font-size:16px;
      margin-bottom:16px;
      color:#333;
    ">Are you sure?</div>
    <div style="display:flex; justify-content:center; gap:12px;">
      <button id="confirmYes" style="
        background:#d9534f;
        color:white;
        border:none;
        padding:8px 14px;
        border-radius:5px;
        cursor:pointer;
        font-size:14px;
      ">Po</button>
      <button id="confirmNo" style="
        background:#6c757d;
        color:white;
        border:none;
        padding:8px 14px;
        border-radius:5px;
        cursor:pointer;
        font-size:14px;
      ">Jo</button>
    </div>
  </div>
</div>

<script>
(async function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var apptListEl = document.getElementById('apptList');
  var calendarContainer = document.getElementById('patientCalendar');
  var filterStatus = document.getElementById('filterStatus');
  var viewListBtn = document.getElementById('viewToggleList');
  var viewCalBtn = document.getElementById('viewToggleCalendar');
  var listView = document.getElementById('patientListView');
  var calView = document.getElementById('patientCalendarView');

  var eventsCache = [];
  var currentView = 'list';

  function setActiveView(v) {
    currentView = v;
    if (v === 'list') {
      listView.style.display = '';
      calView.style.display = 'none';
      viewListBtn.classList.add('active');
      viewCalBtn.classList.remove('active');
      renderList();
    } else {
      listView.style.display = 'none';
      calView.style.display = '';
      viewListBtn.classList.remove('active');
      viewCalBtn.classList.add('active');
      renderCalendar();
    }
  }

  viewListBtn.addEventListener('click', () => setActiveView('list'));
  viewCalBtn.addEventListener('click', () => setActiveView('calendar'));

  async function loadAppointments(){
    apptListEl.innerText = 'Duke ngarkuar...';
    try {
      var res = await fetch(BASE + '/api/my_appointments.php', { credentials:'same-origin' });
      if (!res.ok) { apptListEl.innerText = 'Dështoi ngarkimi.'; return; }
      eventsCache = await res.json() || [];
      if (currentView === 'list') renderList(); else renderCalendar();
    } catch(e) {
      apptListEl.innerText = 'Gabim';
    }
  }

  function renderList(){
    var status = filterStatus.value;
    var rows = eventsCache.filter(a => (!status || (a.status||'')===status));

    if (!rows.length) {
      apptListEl.innerHTML = '<div class="card-box">Nuk ka takime.</div>';
      return;
    }

    var html = '';
    rows.forEach(function(r){
      const st = (r.status || '').toLowerCase();
      var color = st === 'approved'
        ? 'var(--color-success)'
        : (st === 'pending'
				? 'var(--color-warning)'
				: (st === 'cancelled' || st === 'refused' 
				? 'var(--color-error)' : 'var(--color-accent)'));

      html += '<div class="card-box" style="margin-bottom:10px;">';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
      html += '<div><strong>' + (r.doctor_name ? r.doctor_name : 'Mjek') + '</strong><div style="color:var(--color-text-muted)">' 
              + (r.appointment_date||'') + ' ' + (r.appointment_time||'') + ' • ' + (r.duration_minutes||30) + 'm</div></div>';
      html += '<div style="text-align:right"><span class="badge" style="background:' + color + ';">'
              + (r.status||'') + '</span><div style="margin-top:8px;">#' + (r.id||'') + '</div></div>';
      html += '</div>';

      html += '<div style="margin-top:8px;display:flex;gap:8px;justify-content:flex-end;">';

			if (r.status !== 'cancelled' && r.status !== 'refused') {
			html += '<button class="btn btn-danger btn-cancel-appt" data-id="'+r.id+'">Anulo</button>';
			}
      // Reschedule / Edit if still not approved
			if (['pending','refused','cancelled'].includes(r.status)) {
				html += '<button class="btn btn-ghost" data-action="edit" data-id="'+r.id+'">Ndrysho</button>';
			}
      html += '</div></div>';
    });

    apptListEl.innerHTML = html;

    // bind cancel buttons
    apptListEl.querySelectorAll('.ap-action').forEach(b => b.addEventListener('click', onStatusClick));
    // bind edit buttons
    apptListEl.querySelectorAll('[data-action="edit"]').forEach(b => b.addEventListener('click', onEditClick));
  }

  function renderCalendar(){
    var status = filterStatus.value;
    var rows = eventsCache.filter(a => (!status || (a.status||'')===status));

    var events = rows.map(function(a){
      var dur = a.duration_minutes || 30;
      var startIso = a.appointment_date + 'T' + (a.appointment_time || '00:00:00');
      var startDate = new Date(startIso+'+00:00');
      var endDate = new Date(startDate.getTime() + (dur * 60 * 1000));
      return {
        id: String(a.id),
        title: (a.doctor_name ? a.doctor_name : 'Takim'),
        start: startIso,
        end: endDate.toISOString().slice(0,19),
        extendedProps: { raw: a }
      };
    });

    calendarContainer.innerHTML = '';
    var calendar = new FullCalendar.Calendar(calendarContainer, {
      initialView:'timeGridWeek',
      height:600,
      events:events,
      headerToolbar:{
        left:'prev,next today',
        center:'title',
        right:'timeGridDay,timeGridWeek,dayGridMonth'
      },
      eventClick: function(info) {
        var appt = info.event.extendedProps.raw;
        if (appt.status === 'pending' || appt.status === 'refused') {
          showPtModal(true);
          pt_id.value = appt.id;
          pt_date.value = appt.appointment_date;
          pt_duration.value = appt.duration_minutes;
          loadPtSlots(appt);
        }
      }
    });
    calendar.render();
  }

  function onStatusClick(e){
    var id = e.currentTarget.getAttribute('data-id');
    var status = e.currentTarget.getAttribute('data-status');
    fetch(BASE + '/api/update_appointment_status.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body: JSON.stringify({ id:parseInt(id,10), status:status })
    }).then(r => r.json()).then(d => { if (d.success) loadAppointments(); else alert(d.error||'Dështoi'); });
  }

  // Patient Edit Modal
  var ptModal = document.getElementById('patientEditModal');
  var pt_id = document.getElementById('pt_id');
  var pt_date = document.getElementById('pt_date');
  var pt_time = document.getElementById('pt_time');
  var pt_duration = document.getElementById('pt_duration');
  var pt_msg = document.getElementById('pt_msg');
  var pt_slots_msg = document.getElementById('pt_slots_msg');

  function showPtModal(show){ 
    ptModal.classList.toggle('open', !!show); 
    ptModal.setAttribute('aria-hidden', show ? 'false' : 'true'); 
    if (!show) pt_msg.style.display = 'none'; 
  }
  document.getElementById('ptModalClose').addEventListener('click', () => showPtModal(false));
  document.getElementById('pt_cancel_btn').addEventListener('click', () => showPtModal(false));

  function onEditClick(e){
    var id = e.currentTarget.getAttribute('data-id');
    var appt = eventsCache.find(x => String(x.id) === String(id));
    if (!appt) return alert('Takim jo i gjetur');
    pt_id.value = appt.id;
    pt_date.value = appt.appointment_date;
    pt_duration.value = appt.duration_minutes;
    showPtModal(true);
    loadPtSlots(appt);
  }

  function loadPtSlots(appt) {
    pt_time.innerHTML = '';
    var placeholder = document.createElement('option'); 
    placeholder.value=''; 
    placeholder.textContent = pt_date.value ? 'Duke ngarkuar...' : 'Zgjidh datën për të parë oraret'; 
    pt_time.appendChild(placeholder);
    if (!pt_date.value) return;
    pt_slots_msg.style.display='none';
    var q = new URLSearchParams({ doctor_id: appt.doctor_id, date: pt_date.value, duration: pt_duration.value||30 });
    fetch(BASE + '/api/available_slots.php?' + q.toString())
      .then(res => res.json())
      .then(data => {
        pt_time.innerHTML = '';
        var slots = Array.isArray(data) ? data : (data && data.slots ? data.slots : []);
        var message = data && data.message ? data.message : '';
        if (!slots.length) {
          var opt = document.createElement('option'); 
          opt.value=''; opt.textContent = message||'Nuk ka orare'; 
          pt_time.appendChild(opt); 
          pt_slots_msg.textContent = message; 
          pt_slots_msg.style.display='block';
        } else { 
          var opt0 = document.createElement('option'); 
          opt0.value=''; opt0.textContent='Zgjidh orën'; 
          pt_time.appendChild(opt0);
          slots.forEach(s => {
            var o = document.createElement('option'); 
            o.value = s.start; 
            o.textContent = s.start + ' – ' + s.end; 
            pt_time.appendChild(o);
          });
        }
      });
  }

  document.getElementById('ptEditForm').addEventListener('submit', function(e){
    e.preventDefault();
    pt_msg.style.display='none';
    var payload = { id:pt_id.value, date:pt_date.value, time:pt_time.value, duration:parseInt(pt_duration.value||30,10) };
    fetch(BASE + '/api/reschedule_appointment.php', {
      method:'POST',
      headers:{'Content-Type':'application/json'},
      body:JSON.stringify(payload)
    }).then(res => res.json())
      .then(d => {
        if (d.success) { showPtModal(false); loadAppointments(); }
        else { pt_msg.textContent = d.error||'Dështoi'; pt_msg.style.display='block'; }
      });
  });

  filterStatus.addEventListener('change', function(){ 
    if (currentView==='list') renderList(); 
    else loadAppointments(); 
  });

  pt_date.addEventListener('change', function(){
    var appt = eventsCache.find(function(x){ return String(x.id) === String(pt_id.value); });
    if (appt) loadPtSlots(appt);
  });

// custom confirm logic
var confirmOverlay = document.getElementById('confirmOverlay');
var confirmYesBtn = document.getElementById('confirmYes');
var confirmNoBtn  = document.getElementById('confirmNo');
var appointmentToCancel = null;

function showConfirm(message, callback) {
  document.getElementById('confirmText').textContent = message;
  confirmOverlay.style.display = 'flex';

  // yes handler
  confirmYesBtn.onclick = function() {
    confirmOverlay.style.display = 'none';
    callback(true);
  };

  // no handler
  confirmNoBtn.onclick = function() {
    confirmOverlay.style.display = 'none';
    callback(false);
  };
}

document.addEventListener('click', function(e){
  if (e.target.classList.contains('btn-cancel-appt')) {
    appointmentToCancel = e.target.getAttribute('data-id');
    showConfirm('A jeni i sigurt që dëshironi të anuloni këtë takim?', function(confirmed) {
      if (!confirmed) return;
      fetch(BASE + '/api/update_appointment_status.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id: parseInt(appointmentToCancel,10), status:'cancelled' })
      })
      .then(res => res.json())
      .then(d => {
        if (d && d.success) loadAppointments();
        else alert(d.error || 'Dështoi anulimi');
      })
      .catch(() => alert('Gabim lidhjeje'));
    });
  }
});

  setActiveView('list');
  loadAppointments();

})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>