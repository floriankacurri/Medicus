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
      <h1 class="section-title">Orari im javor</h1>
      <div class="form-card" style="max-width:900px;margin-bottom:16px;">
        <p>Vendos intervalet kohore që do të jeni i disponueshëm për çdo ditë të javës.</p>
        <p>Rezervimet e pacientëve do të kontrollojnë këtë orar.</p>
        <div id="scheduleList">Duke ngarkuar...</div>
        <hr>
        <!-- Trigger button for Add Interval modal -->
        <div style="display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
          <button id="openAddInterval" class="btn btn-primary">Shto interval</button>
        </div>
      </div>

      <!-- Add Interval Modal -->
      <div id="addIntervalModal" class="modal" aria-hidden="true">
        <div class="modal-content">
          <div class="modal-header">
            <h3 style="margin:0;">Shto interval orari</h3>
            <button class="modal-close" aria-label="Close" id="addModalClose">&times;</button>
          </div>
          <div style="margin-top:12px;">
            <form id="addIntervalForm" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
              <label for="day_of_week">Dita</label>
              <select id="day_of_week">
                <option value="1">E Hënë</option>
                <option value="2">E Martë</option>
                <option value="3">E Mërkurë</option>
                <option value="4">E Enjte</option>
                <option value="5">E Premte</option>
                <option value="6">E Shtunë</option>
                <option value="0">E Diel</option>
              </select>
              <label for="start_time">Nga</label>
              <input id="start_time" type="time" required>
              <label for="end_time">Deri</label>
              <input id="end_time" type="time" required>
              <div style="width:100%;display:flex;justify-content:flex-end;gap:8px;margin-top:8px;">
                <button class="btn btn-primary" type="submit">Shto</button>
                <button id="addModalCancel" type="button" class="btn btn-ghost">Mbyll</button>
              </div>
              <div id="addMsg" style="min-width:200px;"></div>
            </form>
          </div>
        </div>
      </div>

    </main>
  </div>
</section>

<script>
(async function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var listEl = document.getElementById('scheduleList');
  var area = document.getElementById('scheduleArea');

  async function loadSchedule(){
    listEl.innerText = 'Duke ngarkuar...';
    // diagnostic: check whoami
    try {
       var who = await fetch(BASE + '/api/whoami.php', { credentials:'same-origin' });
       if (who.ok) {
          var wu = await who.json();
          // show small note
          listEl.innerHTML = '<div style="margin-bottom:8px;color:var(--color-text-muted);font-size:0.95rem;">Hyrë si: ' + (wu.name || wu.email || wu.id) + ' (' + (wu.role || '') + ')</div>' + listEl.innerHTML;
       } else if (who.status === 401) {
          listEl.innerHTML = '<div style="margin-bottom:8px;color:var(--color-error);">Nuk jeni i identifikuar përmes sesionit (kontrolloni login)</div>' + listEl.innerHTML;
       }
     } catch (e) { /* ignore diag */ }
    try {
      var res = await fetch(BASE + '/api/doctor_schedule_list.php', { credentials: 'same-origin' });
      if (!res.ok) { if (res.status === 401) { listEl.innerHTML = '<div class="card-box">Ju lutem <a href="'+BASE+'/login">identifikohuni</a> për të aksesuar orarin.</div>'; return; } if (res.status === 403) { listEl.innerHTML = '<div class="card-box">Nuk keni leje për të parë këtë orar.</div>'; return; } var err = await res.json().catch(()=>({})); listEl.innerText = 'Dështoi: ' + (err.error || res.statusText); return; }
      var data = await res.json();
      renderList(data);

    } catch(e){ listEl.innerText = 'Gabim'; }
  }

  function renderList(data){
    if (!data || !data.length) { listEl.innerHTML = '<div class="card-box">Nuk ka intervale të orarit.</div>'; return; }
    var html = '<div class="cards-grid">';
    data.forEach(function(r){
      var days = ['E Diel','E Hënë','E Martë','E Mërkurë','E Enjte','E Premte','E Shtunë'];
      html += '<div class="card-box">';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
      html += '<div><strong>' + (days[r.day_of_week] || r.day_of_week) + '</strong><div style="color:var(--color-text-muted)">'+ (r.start_time || '') +' - '+ (r.end_time || '') +'</div></div>';
      html += '<div><button class="btn btn-danger" data-id="'+r.id+'">Fshij</button></div>';
      html += '</div></div>';
    });
    html += '</div>';
    listEl.innerHTML = html;
    listEl.querySelectorAll('button[data-id]').forEach(function(b){ b.addEventListener('click', onDeleteInterval); });
  }

  async function onDeleteInterval(e){
    if (!confirm('A jeni i sigurt që doni të fshini këtë interval?')) return;
    var id = e.currentTarget.getAttribute('data-id');
    try {
      var res = await fetch(BASE + '/api/doctor_schedule_delete.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id }) });
      var data = await res.json();
      if (!res.ok) return alert(data.error || 'Dështim');
      loadSchedule();
    } catch(err){ alert('Gabim komunikimi'); }
  }

  document.getElementById('addIntervalForm').addEventListener('submit', async function(e){
    e.preventDefault();
    var day = document.getElementById('day_of_week').value;
    var start = document.getElementById('start_time').value;
    var end = document.getElementById('end_time').value;
    var msg = document.getElementById('addMsg'); msg.textContent = '';
    try {
      var res = await fetch(BASE + '/api/doctor_schedule_add.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ day_of_week: day, start_time: start, end_time: end }) });
      var data = await res.json();
      if (!res.ok) { msg.textContent = data.error || 'Dështim'; msg.className='msg error'; return; }
      // success: refresh list, close modal, reset form, show toast
      loadSchedule();
      msg.textContent = 'Shtuar'; msg.className='msg success';
      // reset form
      document.getElementById('day_of_week').value = '1';
      document.getElementById('start_time').value = '';
      document.getElementById('end_time').value = '';
      // close modal (use 'open' class)
      var modalEl = document.getElementById('addIntervalModal');
      modalEl.classList.remove('open');
      setTimeout(function(){ modalEl.setAttribute('aria-hidden', 'true'); }, 200);
      // show toast
      if (typeof showToast === 'function') showToast('Intervali u shtua.', { type: 'success', durationMs: 3000 });
    } catch(err){ msg.textContent = 'Gabim'; msg.className='msg error'; }
  });

  // load weekly appointments into scheduleArea for quick view
  async function loadAppointments(){
    area.innerText = 'Duke ngarkuar...';
    try {
      var res = await fetch(BASE + '/api/my_appointments.php', { credentials:'same-origin' });
      if (!res.ok) { if (res.status === 401) { area.innerHTML = '<div class="card-box">Ju lutem <a href="'+BASE+'/login">identifikohuni</a> për të parë takimet.</div>'; return; } area.innerText = 'Dështoi.'; return; }
      var data = await res.json();
      if (!data || !data.length) { area.innerHTML = '<p>Nuk ka takime këtë javë.</p>'; return; }
      area.innerHTML = '<p>Keto jane takimet e javës së ardhshme. Mund t\'i tërhiqni për t\'i rivendosur (drag & drop).</p>';
      // render FullCalendar for doctor's appointments with drag-to-reschedule
      var events = data.map(function(a){
        return {
          id: String(a.id),
          title: (a.patient_name ? a.patient_name : 'Pacient'),
          start: a.appointment_date + 'T' + (a.appointment_time || '00:00:00'),
          extendedProps: { raw: a }
        };
      });
      // create calendar
      var calEl = document.getElementById('doctorCalendar');
      calEl.innerHTML = '';
      var cal = new FullCalendar.Calendar(calEl, {
        initialView: 'timeGridWeek',
        height: 600,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'timeGridWeek,timeGridDay' },
        events: events,
        editable: true,
        eventDrop: async function(info){
          var id = info.event.id;
          var start = info.event.start;
          if (!start) return;
          var date = start.toISOString().slice(0,10);
          var time = start.toTimeString().slice(0,8);
          try {
            var res = await fetch(BASE + '/api/reschedule_appointment.php', { method: 'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id, date: date, time: time }) });
            var d = await res.json().catch(()=>({}));
            if (!res.ok) { alert(d.error || 'Dështoi'); info.revert(); }
            else { alert('Takimi u rivendos.'); }
          } catch(e) { alert('Gabim komunikimi'); info.revert(); }
        }
      });
      cal.render();

    } catch(e){ area.innerText = 'Gabim'; }
  }

  loadSchedule(); loadAppointments();

  // Modal functionality
  var modal = document.getElementById('addIntervalModal');
  var openModalBtn = document.getElementById('openAddInterval');
  var closeModalBtn = document.getElementById('addModalClose');
  var cancelModalBtn = document.getElementById('addModalCancel');

  openModalBtn.addEventListener('click', function(){
    modal.setAttribute('aria-hidden', 'false');
    setTimeout(function(){ modal.classList.add('open'); }, 10);
  });

  closeModalBtn.addEventListener('click', function(){
    modal.classList.remove('open');
    setTimeout(function(){ modal.setAttribute('aria-hidden', 'true'); }, 200);
  });

  cancelModalBtn.addEventListener('click', function(){
    modal.classList.remove('open');
    setTimeout(function(){ modal.setAttribute('aria-hidden', 'true'); }, 200);
  });

  // Close modal on outside click
  window.addEventListener('click', function(event) {
    if (modal.classList.contains('open') && event.target === modal) {
      modal.classList.remove('open');
      setTimeout(function(){ modal.setAttribute('aria-hidden', 'true'); }, 200);
    }
  });

})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
