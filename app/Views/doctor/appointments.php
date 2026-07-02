<?php
if (!isset($currentUser) || $currentUser['role'] !== 'doctor') { header('Location: ' . BASE_URL . '/'); exit; }
include_once __DIR__ . '/../layouts/header.php';
$BASE = defined('BASE_URL') ? BASE_URL : '/Medicus';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <h1 class="section-title">Takimet</h1>
      <p style="color:var(--color-text-muted);">Lista e takimeve tuaja dhe kalendari. Mund t'i pranoni, anuloni ose të rivendosni takimet këtu.</p>

      <!-- View toggle -->
      <div style="display:flex;justify-content:space-between;align-items:center;margin-top:16px;gap:12px;">
        <div>
          <button id="viewToggleList" class="btn btn-ghost active">Listë</button>
          <button id="viewToggleCalendar" class="btn btn-ghost">Kalendari</button>
        </div>
        <div>
          <select id="filterStatus" style="padding:6px;border-radius:6px;border:1px solid var(--color-border);">
            <option value="">Gjithë</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
      </div>

      <!-- Views container: only one view visible at a time -->
      <div id="doctorViews" style="margin-top:16px;">
        <div id="doctorListView">
          <div class="form-card" style="min-width:-webkit-fill-available;">
            <h3 style="margin-top:0">Lista e Takimeve</h3>
            <div id="apptList">Duke ngarkuar...</div>
          </div>
        </div>

        <div id="doctorCalendarView" style="display:none;">
          <div class="form-card" style="min-width:-webkit-fill-available;">
            <h3 style="margin-top:0">Kalendari</h3>
            <div id="doctorCalendar"></div>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div id="doctorEditModal" class="modal" aria-hidden="true">
        <div class="modal-content" style="max-width:520px">
          <div class="modal-header">
            <h3 style="margin:0">Ndrysho/ Rivendos Takimin</h3>
            <button class="modal-close" id="docModalClose">&times;</button>
          </div>
          <form id="docEditForm" style="padding:16px;">
            <input type="hidden" id="d_id">
            <label>Data</label>
            <input id="d_date" type="date" required style="width:100%;padding:8px;margin-bottom:8px;">
            <label>Ora</label>
            <select id="d_time" required style="width:100%;padding:8px;margin-bottom:8px;"></select>
            <div id="d_slots_msg" style="color:var(--color-text-muted);margin-bottom:8px;display:none;"></div>
            <label>Kohëzgjatja (min)</label>
            <input id="d_duration" type="number" min="5" step="5" value="30" style="width:100%;padding:8px;margin-bottom:8px;">
            <div style="display:flex;gap:8px;justify-content:flex-end; margin-top:20px;">
              <button class="btn btn-ghost" type="button" id="d_cancel_btn">Mbyll</button>
              <button class="btn btn-primary" type="submit">Ruaj</button>
            </div>
            <div id="d_msg" style="margin-top:10px;display:none;"></div>
          </form>
        </div>
      </div>

    </main>
  </div>
</section>

<script>
(async function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var apptListEl = document.getElementById('apptList');
  var calendarContainer = document.getElementById('doctorCalendar');
  var filterStatus = document.getElementById('filterStatus');
  var viewListBtn = document.getElementById('viewToggleList');
  var viewCalBtn = document.getElementById('viewToggleCalendar');
  var listView = document.getElementById('doctorListView');
  var calView = document.getElementById('doctorCalendarView');

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

  viewListBtn.addEventListener('click', function(){ setActiveView('list'); });
  viewCalBtn.addEventListener('click', function(){ setActiveView('calendar'); });

  async function loadAppointments(){
    apptListEl.innerText = 'Duke ngarkuar...';
    try{
      var res = await fetch(BASE + '/api/my_appointments.php', { credentials: 'same-origin' });
      if (!res.ok) { apptListEl.innerText = 'Dështoi ngarkimi.'; return; }
      var data = await res.json();
      eventsCache = data || [];
      // render only the active view
      if (currentView === 'list') renderList(); else renderCalendar();
    }catch(e){ apptListEl.innerText = 'Gabim'; }
  }

  function renderList(){
    var status = filterStatus.value;
    var rows = eventsCache.filter(function(a){ return (!status || (a.status||'')===status); });
    if (!rows.length) { apptListEl.innerHTML = '<div class="card-box">Nuk ka takime.</div>'; return; }
    var html = '';

    rows.forEach(function(r){
      const status = (r.status || '').toLowerCase();
      var color = status === 'approved' ? 'var(--color-success)' : (status === 'pending' ? 'var(--color-warning)' : (status === 'cancelled' || status === 'refused' ? 'var(--color-error)' : 'var(--color-accent)'));

      html += '<div class="card-box" style="margin-bottom:10px;">';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
      html += '<div><strong>' + (r.patient_name ? escapeHtml(r.patient_name) : 'Pacient') + '</strong><div style="color:var(--color-text-muted)">' + (r.appointment_date||'') + ' ' + (r.appointment_time||'') + ' • ' + (r.duration_minutes||30) + 'm</div></div>';
      html += '<div style="text-align:right"><span class="badge" style="background:' + color + ';;">' + escapeHtml(r.status||'') + '</span><div style="margin-top:8px;">#' + (r.id||'') + '</div></div>';
      html += '</div>';
      if (r.reason) html += '<div style="margin-top:8px;color:var(--color-text-muted);">' + escapeHtml(r.reason) + '</div>';
      html += '<div style="margin-top:8px;display:flex;gap:8px;justify-content:flex-end;">';
      html += '<button class="btn btn-ghost" data-action="edit" data-id="'+r.id+'">Ndrysho</button>';
      if (r.status !== 'approved') html += '<button class="btn btn-primary ap-action" data-id="'+r.id+'" data-status="approved">Aprovo</button>';
      if (!['cancelled', 'refused'].includes(r.status)) html += '<button class="btn btn-danger ap-action" data-id="'+r.id+'" data-status="refused">Anulo</button>';
      html += '</div>';
      html += '</div>';
    });
    apptListEl.innerHTML = html;
    apptListEl.querySelectorAll('[data-action="edit"]').forEach(function(b){ b.addEventListener('click', onEditClick); });
    apptListEl.querySelectorAll('.ap-action').forEach(function(b){ b.addEventListener('click', onStatusClick); });
  }

  function renderCalendar(){
    var status = filterStatus.value;
    var rows = eventsCache.filter(function(a){ return (!status || (a.status||'')===status); });

    var events = rows.map(function(a){
      var dur = a.duration_minutes || 30;
      var startIso = a.appointment_date + 'T' + (a.appointment_time || '00:00:00');
      var startDate = new Date(startIso+'+00:00');
      var endDate = new Date(startDate.getTime() + (dur * 60 * 1000));
      console.log(startIso);
      console.log(endDate.toISOString().slice(0,19));
      return { id: String(a.id), title: (a.patient_name ? a.patient_name : 'Takim'), start: startIso, end: endDate.toISOString().slice(0,19), extendedProps: { raw: a } };
    });
    // clear any previous calendar
    calendarContainer.innerHTML = '';
    var calendar = new FullCalendar.Calendar(calendarContainer, {
      initialView: 'timeGridWeek',
      height: 600,
      eventMinHeight: 15,
      eventShortHeight: 20,
      slotDuration: '00:15:00',
      headerToolbar: {
        left:'prev,next today',
        center:'title',
        right:'timeGridDay,timeGridWeek,dayGridMonth'
      },
      events: events,
      eventDidMount: function(info) {
        // add a tooltip using the browser title attribute
        const text = `${info.event.title}\n${info.event.start.toLocaleTimeString()} (${info.event.extendedProps.raw.duration_minutes}m)`;
        info.el.setAttribute('title', text);
      },
      editable: true,
      eventDrop: async function(info){
        var id = info.event.id;
        var start = info.event.start; if (!start) return; var date = start.toISOString().slice(0,10); var time = start.toTimeString().slice(0,8);
        try{
          var r = await fetch(BASE + '/api/reschedule_appointment.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id, date: date, time: time }) });
          var d = await r.json().catch(()=>({})); if (!r.ok) { alert(d.error||'Dështoi'); info.revert(); } else { alert('Takimi u rivendos.'); loadAppointments(); }
        }catch(e){ alert('Gabim komunikimi'); info.revert(); }
      },
      eventClick: function(info) {
        var id = info.event.id; // appointment id
        var appt = eventsCache.find(function(a){ return String(a.id) === String(id); });
        if (!appt) return;

        // Populate edit form
        d_id.value = appt.id;
        d_date.value = appt.appointment_date;
        d_duration.value = appt.duration_minutes;

        // open the modal
        showDocModal(true);

        // load the slots
        loadDocSlots(appt);
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
      body: JSON.stringify({ id: parseInt(id,10), status: status }) 
    })
      .then(function(res){ return res.json().catch(()=>({})); })
      .then(function(d){ if (d && d.success) loadAppointments(); else alert(d.error||'Dështoi'); })
      .catch(function(){ alert('Gabim'); });
  }

  // Edit modal logic
  var docModal = document.getElementById('doctorEditModal');
  var docForm = document.getElementById('docEditForm');
  var d_id = document.getElementById('d_id');
  var d_date = document.getElementById('d_date');
  var d_time = document.getElementById('d_time');
  var d_duration = document.getElementById('d_duration');
  var d_msg = document.getElementById('d_msg');
  var d_slots_msg = document.getElementById('d_slots_msg');

  function showDocModal(show){ docModal.classList.toggle('open', !!show); docModal.setAttribute('aria-hidden', show ? 'false' : 'true'); if (!show) d_msg.style.display='none'; }
  document.getElementById('docModalClose').addEventListener('click', function(){ showDocModal(false); });
  document.getElementById('d_cancel_btn').addEventListener('click', function(){ showDocModal(false); });

  function onEditClick(e){
    var id = e.currentTarget.getAttribute('data-id');
    var appt = eventsCache.find(function(x){ return String(x.id) === String(id); });
    if (!appt) return alert('Takim jo i gjetur');
    d_id.value = appt.id;
    d_date.value = appt.appointment_date || '';
    d_duration.value = appt.duration_minutes || 30;
    showDocModal(true);
    // load slots for this doctor & date
    loadDocSlots(appt);
  }

  function loadDocSlots(appt){
    d_time.innerHTML = '';
    var placeholder = document.createElement('option'); placeholder.value=''; placeholder.textContent = d_date.value ? 'Duke ngarkuar...' : 'Zgjidh datën për të parë oraret'; d_time.appendChild(placeholder);
    if (!d_date.value || !appt.doctor_id) return;
    d_slots_msg.style.display='none';
    var q = new URLSearchParams({ doctor_id: appt.doctor_id, date: d_date.value, duration: d_duration.value || 30 });
    fetch(BASE + '/api/available_slots.php?' + q.toString()).then(function(res){ return res.json(); }).then(function(data){ var slots = Array.isArray(data) ? data : (data && data.slots? data.slots : []); var message = data && data.message? data.message : ''; d_time.innerHTML = ''; if (!slots.length) { var opt = document.createElement('option'); opt.value=''; opt.textContent = message || 'Nuk ka orare'; d_time.appendChild(opt); d_slots_msg.textContent = message; d_slots_msg.style.display = 'block'; } else { var opt0 = document.createElement('option'); opt0.value=''; opt0.textContent='Zgjidh orën'; d_time.appendChild(opt0); slots.forEach(function(s){ var o = document.createElement('option'); o.value = s.start; o.textContent = s.start + ' – ' + s.end; d_time.appendChild(o); }); d_slots_msg.style.display='none'; } }).catch(function(){ d_time.innerHTML=''; var opt = document.createElement('option'); opt.value=''; opt.textContent='Dështoi ngarkimi'; d_time.appendChild(opt); d_slots_msg.textContent='Gabim'; d_slots_msg.style.display='block'; });
  }

  docForm.addEventListener('submit', function(e){ e.preventDefault(); d_msg.style.display='none'; var payload = { id: d_id.value, date: d_date.value, time: d_time.value, duration: parseInt(d_duration.value||30,10) }; fetch(BASE + '/api/reschedule_appointment.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify(payload) }).then(function(res){ return res.json().then(function(d){ return { ok: res.ok, data: d, status: res.status }; }).catch(function(){ return { ok: res.ok, data: {}, status: res.status }; }); }).then(function(r){ if (r.ok) { d_msg.textContent='Ruajtur me sukses.'; d_msg.style.display='block'; setTimeout(function(){ showDocModal(false); loadAppointments(); },900); } else { d_msg.textContent = r.data && r.data.error ? r.data.error : 'Dështoi'; d_msg.style.display='block'; } }).catch(function(){ d_msg.textContent='Gabim lidhjeje'; d_msg.style.display='block'; }); });

  filterStatus.addEventListener('change', function(){ if (currentView === 'list') renderList(); else loadAppointments(); });

  d_date.addEventListener('change', function(){
    var appt = eventsCache.find(function(x){ return String(x.id) === String(d_id.value); });
    if (appt) loadDocSlots(appt);
  });

  d_duration.addEventListener('change', function(){
    var appt = eventsCache.find(function(x){ return String(x.id) === String(d_id.value); });
    if (appt) loadDocSlots(appt);
  });

  d_duration.addEventListener('input', function(){
    var appt = eventsCache.find(function(x){ return String(x.id) === String(d_id.value); });
    if (appt) loadDocSlots(appt);
  });

  // init
  setActiveView('list');
  loadAppointments();

  function escapeHtml(s){ if (s===null || s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
