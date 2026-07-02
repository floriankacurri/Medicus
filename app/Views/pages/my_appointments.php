<?php
if (!isset($currentUser)) { header('Location: ' . BASE_URL . '/login'); exit; }
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <div class="form-card" style="max-width:1000px; margin: 24px auto;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
          <h2 style="margin:0;">Takimet e mia</h2>
          <div>
            <button id="viewList" class="btn btn-ghost">Listë</button>
            <button id="viewCalendar" class="btn btn-ghost">Kalendari</button>
          </div>
        </div>

        <div id="apptsArea">Duke ngarkuar...</div>
      </div>
    </main>
  </div>
</section>


<!-- Edit modal (improved) -->
<div id="editModal" class="modal" aria-hidden="true">
  <div class="modal-content" style="max-width:500px;">
    <div class="modal-header">
      <h3 style="margin:0;">Ndrysho Takimin</h3>
      <button class="modal-close" aria-label="Close" id="modalClose">&times;</button>
    </div>
    <form id="editForm" style="padding:0 20px 20px;">
      <input type="hidden" id="e_id">

      <div style="margin-bottom:16px;">
        <label for="e_date" style="display:block;margin-bottom:6px;font-weight:500;">Data</label>
        <input id="e_date" type="date" required style="width:100%;padding:8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:1rem;">
      </div>

      <div style="margin-bottom:16px;">
        <label for="e_time" style="display:block;margin-bottom:6px;font-weight:500;">Ora</label>
        <select id="e_time" required style="width:100%;padding:8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:1rem;">
          <option value="">Zgjidh datën për të parë oraret</option>
        </select>
        <div id="slotsMsg" style="color:var(--color-text-muted);font-size:0.85rem;margin-top:6px;display:none;"></div>
      </div>

      <?php if (($currentUser['role'] ?? '') === 'patient'): ?>
        <div style="margin-bottom:16px;">
          <label for="e_duration" style="display:block;margin-bottom:6px;font-weight:500;">Kohëzgjatja (min)</label>
          <input id="e_duration" type="number" min="5" step="5" value="30" disabled style="width:100%;padding:8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:1rem;background-color:var(--color-bg-light);color:var(--color-text-muted);cursor:not-allowed;">
          <div style="color:var(--color-text-muted);font-size:0.85rem;margin-top:6px;font-style:italic;">Vetëm mjeku mund të ndryshojë kohëzgjatjen.</div>
        </div>
      <?php else: ?>
        <div style="margin-bottom:16px;">
          <label for="e_duration" style="display:block;margin-bottom:6px;font-weight:500;">Kohëzgjatja (min)</label>
          <input id="e_duration" type="number" min="5" step="5" value="30" style="width:100%;padding:8px;border:1px solid var(--color-border);border-radius:var(--radius);font-size:1rem;">
        </div>
      <?php endif; ?>

      <div style="display:flex;gap:8px;margin-bottom:12px;">
        <button class="btn btn-primary" type="submit" style="flex:1;">Rivendos</button>
        <button id="e_cancel" type="button" class="btn btn-ghost" style="flex:1;">Anulo</button>
      </div>

      <div id="editMsg" class="mt-2" role="status" style="padding:8px;border-radius:var(--radius);display:none;"></div>
    </form>
  </div>
</div>

<script>
(async function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var area = document.getElementById('apptsArea');
  var view = 'list';
  document.getElementById('viewList').addEventListener('click', function(){ view = 'list'; render(lastData); });
  document.getElementById('viewCalendar').addEventListener('click', function(){ view = 'calendar'; render(lastData); });

  var lastData = null;
  try {
    var res = await fetch(BASE + '/api/my_appointments.php', { credentials: 'same-origin' });
    if (!res.ok) { var err = await res.json().catch(()=>({})); area.innerText = 'Dështoi ngarkimi i takimeve: ' + (err.error || res.statusText); return; }
    var data = await res.json();
    lastData = data || [];
    render(lastData);
  } catch (e) { area.innerText = 'Gabim komunikimi.'; }

  function render(data) {
    if (!data || !data.length) { area.innerHTML = '<div class="card-box">Nuk ka takime.</div>'; return; }
    if (view === 'list') return renderList(data);
    renderCalendar(data);
  }

  function renderList(data) {
    var html = '<div class="cards-grid">';
    data.forEach(function(r){
      var status = (r.status||'').toLowerCase();
      var color = status === 'approved' ? 'var(--color-success)' : (status === 'pending' ? 'var(--color-warning)' : (status === 'cancelled' || status === 'refused' ? 'var(--color-error)' : 'var(--color-accent)'));
      var doctor = r.doctor_name || r.doctor_id || r.doctor || '–';
      var duration = r.duration_minutes || 30;
      html += '<div class="card-box">';
      html += '<div style="display:flex;justify-content:space-between;align-items:center;">';
      html += '<div><strong>' + (doctor ? 'Dr. ' + escapeHtml(doctor) : 'Takim') + ' <span class="badge duration">' + duration + 'm</span></strong><div style="color:var(--color-text-muted)">' + (r.appointment_date||'') + ' ' + (r.appointment_time||'') + '</div></div>';
      html += '<div style="text-align:right"><span class="badge" style="background:' + color + ';;">' + escapeHtml(r.status||'') + '</span><div style="margin-top:8px;">#' + (r.id||'') + '</div></div>';
      html += '</div>';
      if (r.reason) html += '<div style="margin-top:8px;color:var(--color-text-muted);">' + escapeHtml(r.reason) + '</div>';
      html += '<div style="margin-top:10px;display:flex;gap:8px;justify-content:flex-end;">';
      html += '<button class="btn btn-ghost" data-action="edit" data-id="'+r.id+'">Ndrysho</button>';
      html += '<button class="btn btn-danger" data-action="cancel" data-id="'+r.id+'">Anulo</button>';
      html += '</div>';
      html += '</div>';
    });
    html += '</div>';
    area.innerHTML = html;

    // attach handlers
    area.querySelectorAll('[data-action="edit"]').forEach(function(b){ b.addEventListener('click', onEditClick); });
    area.querySelectorAll('[data-action="cancel"]').forEach(function(b){ b.addEventListener('click', onCancelClick); });
  }

  function onEditClick(e){
    var id = e.currentTarget.getAttribute('data-id');
    var appt = lastData.find(function(x){ return x.id == id; });
    if (!appt) return alert('Takim jo i gjetur');
    document.getElementById('e_id').value = appt.id;
    document.getElementById('e_date').value = appt.appointment_date || '';
    document.getElementById('e_duration').value = appt.duration_minutes || 30;
    showModal(true);
    // Load available slots after modal is shown
    setTimeout(function(){ loadSlots(appt); }, 100);
  }

  function loadSlots(appt){
    var dateInput = document.getElementById('e_date');
    var timeSelect = document.getElementById('e_time');
    var date = dateInput.value;
    var slotsMsg = document.getElementById('slotsMsg');
    var duration = 30; // default duration

    timeSelect.innerHTML = '';
    var placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = date ? 'Duke ngarkuar oraret...' : 'Zgjidh datën për të parë oraret';
    timeSelect.appendChild(placeholder);

    if (!date || !appt.doctor_id) return;

    var q = new URLSearchParams({ doctor_id: appt.doctor_id, date: date, duration: duration, step: 15 });
    fetch(BASE + '/api/available_slots.php?' + q.toString())
      .then(function(res){ return res.json(); })
      .then(function(data){
        var slots = Array.isArray(data) ? data : (data && data.slots ? data.slots : []);
        var message = data && data.message ? data.message : '';

        timeSelect.innerHTML = '';
        if (!Array.isArray(slots) || !slots.length) {
          var opt = document.createElement('option');
          opt.value = '';
          opt.textContent = message || 'Nuk ka orare të disponueshme';
          timeSelect.appendChild(opt);
          slotsMsg.textContent = message;
          slotsMsg.style.display = 'block';
        } else {
          var opt0 = document.createElement('option');
          opt0.value = '';
          opt0.textContent = 'Zgjidh orën';
          timeSelect.appendChild(opt0);
          slots.forEach(function(s){
            var opt = document.createElement('option');
            opt.value = s.start;
            opt.textContent = s.start + ' – ' + s.end;
            timeSelect.appendChild(opt);
          });
          slotsMsg.style.display = 'none';
        }
      })
      .catch(function(err){
        console.error('Failed to load slots', err);
        timeSelect.innerHTML = '';
        var opt = document.createElement('option');
        opt.value = '';
        opt.textContent = 'Dështoi ngarkimi i orareve';
        timeSelect.appendChild(opt);
      });
  }

  async function onCancelClick(e){
    if (!confirm('A jeni i sigurt që doni të anuloni këtë takim?')) return;
    var id = e.currentTarget.getAttribute('data-id');
    try {
      var res = await fetch(BASE + '/api/cancel_appointment.php', { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id }) });
      var data = await res.json();
      if (!res.ok) return alert(data.error || 'Dështim');
      alert('Takimi u anulua');
      location.reload();
    } catch (err) { alert('Gabim komunikimi'); }
  }

  function renderCalendar(data) {
    // Use FullCalendar for richer UI
    var calContainerId = 'fcCalendar';
    area.innerHTML = '<div id="'+calContainerId+'"></div>';
    var events = data.map(function(a){
      var status = (a.status||'').toLowerCase();
      var dur = a.duration_minutes || 30;
      var startIso = a.appointment_date + 'T' + (a.appointment_time || '00:00:00');
      var startDate = new Date(startIso+'+00:00');
      var endDate = new Date(startDate.getTime() + (dur * 60 * 1000));
      return {
        id: String(a.id),
        title: (a.doctor_name ? 'Dr. ' + a.doctor_name : 'Takim'),
        start: startIso,
        end: endDate.toISOString().slice(0,19),
        extendedProps: { raw: a }
      };
    });

    var calendarEl = document.getElementById(calContainerId);
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'dayGridMonth',
      height: 600,
      eventMinHeight: 15,
      eventShortHeight: 20,
      slotDuration: '00:15:00',
      headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
      events: events,
      editable: true,
      eventDidMount: function(info) {
        // add a tooltip using the browser title attribute
        const text = `${info.event.title}\n${info.event.start.toLocaleTimeString()} (${info.event.extendedProps.raw.duration_minutes}m)`;
        info.el.setAttribute('title', text);
      },
      eventDrop: async function(info){
        // when user drags an event to new date/time
        var id = info.event.id;
        var start = info.event.start;
        if (!start) return;
        var date = start.toISOString().slice(0,10);
        var time = start.toTimeString().slice(0,8);
        var dur = info.event.extendedProps && info.event.extendedProps.raw && info.event.extendedProps.raw.duration_minutes ? info.event.extendedProps.raw.duration_minutes : 30;
        try {
          var res = await fetch(BASE + '/api/reschedule_appointment.php', { method: 'POST', credentials:'same-origin', headers:{'Content-Type':'application/json'}, body: JSON.stringify({ id: id, date: date, time: time, duration: dur }) });
          var d = await res.json().catch(()=>({}));
          if (!res.ok) { alert(d.error || 'Dështoi'); info.revert(); }
          else { alert('Takimi u rivendos.'); }
        } catch(e) { alert('Gabim komunikimi'); info.revert(); }
      }
    });
    calendar.render();
  }

  function showModal(visible){
    var m = document.getElementById('editModal');
    var msg = document.getElementById('editMsg');
    if (visible) {
      m.classList.add('open');
      m.setAttribute('aria-hidden','false');
      msg.textContent = '';
      msg.style.display = 'none';
    } else {
      m.classList.remove('open');
      m.setAttribute('aria-hidden','true');
    }
  }

  document.getElementById('e_cancel').addEventListener('click', function(){ showModal(false); });
  document.getElementById('modalClose').addEventListener('click', function(){ showModal(false); });

  document.getElementById('editForm').addEventListener('submit', async function(e){
    e.preventDefault();
    var id = document.getElementById('e_id').value;
    var date = document.getElementById('e_date').value;
    var time = document.getElementById('e_time').value;
    if ((time || '').length === 5) time += ':00'; // ensure HH:MM:SS format

    var msg = document.getElementById('editMsg');
    msg.textContent = 'Duke u dërguar...';
    msg.className = 'msg info mt-2';
    msg.style.display = 'block';

    var payload = { id: id, date: date, time: time };
    var durationInput = document.getElementById('e_duration');
    if (!durationInput.disabled) {
      payload.duration = parseInt(durationInput.value || '30', 10);
    }

    try {
      var res = await fetch(BASE + '/api/reschedule_appointment.php', {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      var data = await res.json().catch(function(){ return {}; });
      debugger;
      if (!res.ok) {
        msg.textContent = data.error || 'Dështoi rivendosja e takimit';
        msg.className = 'msg error mt-2';
        msg.style.display = 'block';
        return;
      }
      msg.textContent = 'Takimi u rivendos me sukses!';
      msg.className = 'msg success mt-2';
      msg.style.display = 'block';
      setTimeout(function(){ location.reload(); }, 1200);
    } catch (err) {
      msg.textContent = 'Gabim komunikimi: ' + err.message;
      msg.className = 'msg error mt-2';
      msg.style.display = 'block';
    }
  });

  function escapeHtml(s){ if (s===null || s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
