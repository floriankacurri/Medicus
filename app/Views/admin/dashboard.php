<?php
// Admin dashboard - interactive single-page admin panel
// Expects: $currentUser
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Admin Dashboard</title>
  <link rel="stylesheet" href="/Medicus/public/assets/css/app.css">
  <style>
    .admin-container { max-width:1200px; margin:24px auto; padding:16px; }
    .tabs { display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; }
    .tab { padding:8px 12px; border:1px solid #ddd; border-radius:6px; cursor:pointer; background:#fff }
    .tab.active { background:var(--color-primary); color:#fff }
    .panel { border:1px solid #eee; padding:12px; border-radius:6px; background:#fff }
    .table-wrap { overflow:auto; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px; border-bottom:1px solid #eee; }
    .controls { display:flex; gap:8px; align-items:center; margin-bottom:12px }
    .btn { padding:8px 10px; border-radius:4px; border:1px solid #ccc; background:#f7f7f7; cursor:pointer }
    .btn.primary { background:var(--color-primary); color:#fff; border-color:var(--color-primary) }
    .muted { color:var(--color-text-muted); }
    .modal { position:fixed; inset:0; display:none; align-items:center; justify-content:center; background:rgba(0,0,0,0.4); }
    .modal.open { display:flex; }
    .modal .card { background:#fff; padding:16px; border-radius:6px; width:90%; max-width:600px; }
    .pagination { display:flex; gap:6px; align-items:center }
    input[type=text], select { padding:6px 8px; }
  </style>
</head>
<body>
  <div class="admin-container">
    <h1>Admin Dashboard</h1>
    <p>Signed in as <?php echo htmlspecialchars($currentUser['name'] ?? $currentUser['email'] ?? 'admin'); ?> — <a href="/Medicus/">Back to site</a></p>

    <div class="tabs" role="tablist">
      <div class="tab active" data-tab="overview">Overview</div>
      <div class="tab" data-tab="users">Users</div>
      <div class="tab" data-tab="doctors">Doctors</div>
      <div class="tab" data-tab="appointments">Appointments</div>
      <div class="tab" data-tab="healthcards">Health Cards</div>
    </div>

    <div class="panel" id="panel-overview">
      <div id="overviewContent">Loading…</div>
    </div>

    <div class="panel" id="panel-users" style="display:none">
      <div class="controls">
        <input id="userSearch" type="text" placeholder="Search users">
        <button id="userSearchBtn" class="btn">Search</button>
        <button id="newUserBtn" class="btn primary">New User</button>
        <div style="margin-left:auto" id="usersPager"></div>
      </div>
      <div class="table-wrap"><table id="usersTable"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
    </div>

    <div class="panel" id="panel-doctors" style="display:none">
      <!-- <div class="controls"><button id="refreshDoctors" class="btn">Refresh</button> <button id="newDoctorBtn" class="btn primary">New Doctor</button></div> -->
      <div class="table-wrap"><table id="doctorsTable"><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Specialization</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
    </div>

    <!-- Reservations panel removed: Appointment management consolidated under Appointments tab -->

    <div class="panel" id="panel-appointments" style="display:none">
      <div class="controls">
        <select id="apptDoctorFilter"><option value="">All doctors</option></select>
        <select id="apptStatusFilter"><option value="">All</option><option>pending</option><option>approved</option><option>rejected</option></select>
        <input id="apptStart" type="date"> to <input id="apptEnd" type="date">
        <button id="apptFilterBtn" class="btn">Filter</button>
        <div style="margin-left:auto" id="apptPager"></div>
      </div>
      <div class="table-wrap"><table id="apptTable"><thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead><tbody></tbody></table></div>
    </div>

    <div class="panel" id="panel-healthcards" style="display:none">
      <div class="controls">
        <input id="hcSearch" type="text" placeholder="Search by patient name">
        <button id="hcSearchBtn" class="btn">Search</button>
        <div style="margin-left:auto" id="hcPager"></div>
      </div>
      <div class="table-wrap">
        <table id="hcTable">
          <thead>
            <tr>
              <th>ID</th><th>Patient</th><th>Medical History</th><th>Allergies</th><th>Notes</th><th>Last Updated</th><th>Actions</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>    
    </div>

  </div>

  <!-- modal -->
  <div class="modal" id="modal">
    <div class="card">
      <div id="modalBody"></div>
      <div style="text-align:right;margin-top:12px"><button id="modalClose" class="btn">Close</button> <button id="modalSave" class="btn primary">Save</button></div>
    </div>
  </div>

<script>
const API = '/Medicus/api/admin.php';
const qs = (s)=>document.querySelector(s);
const qsa = (s)=>Array.from(document.querySelectorAll(s));

// Tab switching
qsa('.tab').forEach(t => t.addEventListener('click', ()=>{
  qsa('.tab').forEach(x=>x.classList.remove('active'));
  t.classList.add('active');
  const tab = t.dataset.tab;
  qsa('[id^="panel-"]').forEach(p=>p.style.display='none');
  qs('#panel-'+tab).style.display = '';
    if (tab === 'overview') loadOverview();
    if (tab === 'users') loadUsers();
    if (tab === 'doctors') loadDoctors();
    if (tab === 'appointments') loadAppointments();
    if (tab === 'healthcards') loadHealthcards();
}));

// Generic fetch helpers
async function apiGet(action, params = {}) {
  const url = API + '?action=' + encodeURIComponent(action) + (Object.keys(params).length ? '&' + new URLSearchParams(params).toString() : '');
  const res = await fetch(url);
  return res.json();
}
async function apiPost(action, body = {}) {
  const res = await fetch(API + '?action=' + encodeURIComponent(action), {method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify(body)});
  return res.json();
}

// Overview
async function loadOverview(){
  const users = await apiGet('list_users', {per:1});
  const doctors = await apiGet('list_doctors');
  const pacients = await apiGet('list_patients');
  const appts = await apiGet('list_appointments', {per:1});
  const html = `
    <div class="grid">
      <div class="card"><h3>Users</h3><p>${users.total ?? 0}</p></div>
      <div class="card"><h3>Doctors</h3><p>${doctors.length ?? 0}</p></div>
      <div class="card"><h3>Patients</h3><p>${pacients.length ?? 0}</p></div>
      <div class="card"><h3>Appointments</h3><p>${appts.total ?? 0}</p></div>
    </div>
  `;
  qs('#overviewContent').innerHTML = html;
}

// USERS
let usersState = {page:1, per:20, search:''};
async function loadUsers(){
  const data = await apiGet('list_users', {page: usersState.page, per: usersState.per, search: usersState.search});
  const tbody = qs('#usersTable tbody'); tbody.innerHTML = '';
  data.rows.forEach(u => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${u.id}</td><td>${escapeHtml(u.name)}</td><td>${escapeHtml(u.email)}</td><td>${escapeHtml(u.role)}</td><td><button class="btn editUser" data-id="${u.id}">Edit</button> <button class="btn deleteUser" data-id="${u.id}">Delete</button></td>`;
    tbody.appendChild(tr);
  });
  renderPager('#usersPager', data.page, data.per, data.total, (p)=>{ usersState.page = p; loadUsers(); });
  qsa('.editUser').forEach(b=>b.addEventListener('click', openEditUser));
  qsa('.deleteUser').forEach(b=>b.addEventListener('click', deleteUser));
}

function openEditUser(e){
  const id = e.currentTarget.dataset.id;
  showModal('Edit user', async (body)=>{
    const user = await apiGet('get_user', {user_id: id});
    body.innerHTML = `<label>Name</label><input id="u_name" value="${escapeHtml(user.name)}"><label>Email</label><input id="u_email" value="${escapeHtml(user.email)}"><label>Role</label><select id="u_role"><option value="patient">patient</option><option value="doctor">doctor</option><option value="admin">admin</option></select><label>Active</label><select id="u_active"><option value="1">Active</option><option value="0">Inactive</option></select>`;
    qs('#u_role').value = user.role || 'patient';
    qs('#u_active').value = user.active ?? 1;
  }, async ()=>{
    const payload = {id: id, name: qs('#u_name').value.trim(), email: qs('#u_email').value.trim(), role: qs('#u_role').value, active: parseInt(qs('#u_active').value)};
    const res = await apiPost('update_user', payload);
    if (res.success) { closeModal(); loadUsers(); } else alert('Error: ' + (res.error || 'unknown'));
  });
}

function deleteUser(e){
  if(!confirm('Delete user?')) return;
  const id = e.currentTarget.dataset.id;
  apiPost('delete_user', {user_id: id}).then(r=>{ if (r.success) loadUsers(); else alert('Error: ' + (r.error||'unknown')) });
}

qs('#userSearchBtn').addEventListener('click', ()=>{ usersState.search = qs('#userSearch').value.trim(); usersState.page = 1; loadUsers(); });

qs('#newUserBtn').addEventListener('click', () => {
  showModal('Create User', (body) => {
    body.innerHTML = `
      <label>Name</label>
      <input id="cu_name" type="text" style="width:100%;margin-bottom:8px;">

      <label>Email</label>
      <input id="cu_email" type="email" style="width:100%;margin-bottom:8px;">

      <label>Password</label>
      <input id="cu_password" type="password" style="width:100%;margin-bottom:8px;">

      <label>Role</label>
      <select id="cu_role" style="width:100%;margin-bottom:8px;">
        <option value="">Select role</option>
        <option value="patient">Patient</option>
        <option value="doctor">Doctor</option>
        <option value="admin">Admin</option>
      </select>

      <div id="roleFields"></div>
    `;

    // listen for role change
    qs('#cu_role').addEventListener('change', function(){
      const roleFields = qs('#roleFields');
      if (this.value === 'patient') {
        roleFields.innerHTML = `
          <label>Date of Birth</label><input id="cu_dob" type="date" style="width:100%;margin-bottom:8px;">
          <label>Gender</label><select id="cu_gender" style="width:100%;margin-bottom:8px;">
            <option value="">Select</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
          </select>
        `;
      } else if (this.value === 'doctor') {
        roleFields.innerHTML = `
          <label>Specialization</label>
          <input id="cu_spec" type="text" style="width:100%;margin-bottom:8px;">
        `;
      } else {
        roleFields.innerHTML = '';
      }
    });
  }, async () => {
    const role = qs('#cu_role').value;
    const payload = {
      name: qs('#cu_name').value.trim(),
      email: qs('#cu_email').value.trim(),
      password: qs('#cu_password').value,
      role,
      date_of_birth: role === 'patient' ? qs('#cu_dob').value : null,
      gender: role === 'patient' ? qs('#cu_gender').value : null,
      specialization: role === 'doctor' ? qs('#cu_spec').value.trim() : null
    };

    const res = await apiPost('create_user_with_role', payload);
    if (res.success) {
      closeModal();
      loadUsers();
    } else {
      alert('Error: ' + (res.error || 'unknown'));
    }
  });
});

// DOCTORS
async function loadDoctors(){
  const rows = await apiGet('list_doctors');
  const tbody = qs('#doctorsTable tbody'); tbody.innerHTML = '';
  rows.forEach(d => {
    const tr = document.createElement('tr');
    tr.innerHTML = `<td>${d.id}</td><td>${escapeHtml(d.name||'')}</td><td>${escapeHtml(d.email||'')}</td><td>${escapeHtml(d.specialization||'')}</td><td><button class="btn editDoc" data-id="${d.id}">Edit</button></td>`;
    tbody.appendChild(tr);
  });
  qsa('.editDoc').forEach(b=>b.addEventListener('click', openEditDoctor));
}
function openEditDoctor(e){
  const id = e.currentTarget.dataset.id;
  showModal('Edit Doctor', async (body) => {
    // Fetch current doctor data
    const d = await apiGet('get_doctor', { id });

    body.innerHTML = `
      <label>Name</label>
      <input id="d_name" type="text" value="${escapeHtml(d.name || '')}" style="width:100%;margin-bottom:8px;">

      <label>Email</label>
      <input id="d_email" type="email" value="${escapeHtml(d.email || '')}" style="width:100%;margin-bottom:8px;">

      <label>Specialization</label>
      <input id="d_spec" type="text" value="${escapeHtml(d.specialization || '')}" style="width:100%;margin-bottom:8px;">
    `;
  }, async () => {
    // Gather updated values
    const payload = {
      doctor_id: id,
      name: qs('#d_name').value.trim(),
      email: qs('#d_email').value.trim(),
      specialization: qs('#d_spec').value.trim(),
    };

    // Send update request
    const res = await apiPost('update_doctor', payload);
    if (res.success) {
      closeModal();
      loadDoctors(); // refresh list
    } else {
      alert('Error: ' + (res.error || 'unknown'));
    }
  });
}

// Reservations functionality was consolidated into the Appointments tab; no separate Reservations UI remains.

// APPOINTMENTS
let apptState = {page:1, per:20, doctor:'', status:'', start:'', end:''};
async function loadAppointments(){
  const params = {page: apptState.page, per: apptState.per};
  if(apptState.doctor) params.doctor_id = apptState.doctor;
  if(apptState.status) params.status = apptState.status;
  if(apptState.start) params.start = apptState.start;
  if(apptState.end) params.end = apptState.end;
  const data = await apiGet('list_appointments', params);
  const tbody = qs('#apptTable tbody'); tbody.innerHTML = '';
  data.rows.forEach(a=>{ const tr = document.createElement('tr'); tr.dataset.id = a.id; tr.innerHTML = `<td>${a.id}</td><td>${escapeHtml(a.patient_id)}</td><td>${escapeHtml(a.doctor_id)}</td><td>${escapeHtml(a.appointment_date)}</td><td>${escapeHtml(a.appointment_time)}</td><td>${escapeHtml(a.status)}</td><td><button class='btn apAssign' data-id='${a.id}'>Assign</button> <button class='btn apResch' data-id='${a.id}'>Reschedule</button></td>`; tbody.appendChild(tr); });
  renderPager('#apptPager', data.page, data.per, data.total, (p)=>{ apptState.page = p; loadAppointments(); });
  qsa('.apAssign').forEach(b=>b.addEventListener('click', async e=>{ const id = e.currentTarget.dataset.id; const docs = await apiGet('list_doctors'); let opts = '';
    docs.forEach(d=> opts += `<option value="${d.id}">${escapeHtml(d.name||d.email||'')} (${escapeHtml(d.specialization||'')})</option>`);
    showModal('Assign doctor', (body)=>{ body.innerHTML = `<select id='assignDoc'>${opts}</select>`; }, async ()=>{ const d = qs('#assignDoc').value; const r = await apiPost('assign_appointment_doctor', {id:id, doktori_id: d}); if(r.success){ closeModal(); loadAppointments(); } else alert('Error:'+ (r.error||'unknown')); }); }));
  qsa('.apResch').forEach(b=>b.addEventListener('click', async e=>{ const id = e.currentTarget.dataset.id; showModal('Reschedule', (body)=>{ body.innerHTML = `<label>Date</label><input id='rs_date' type='date'><label>Time</label><input id='rs_time' type='time'>`; }, async ()=>{ const date = qs('#rs_date').value; const time = qs('#rs_time').value; const r = await apiPost('reschedule_appointment', {id:id, date:date, time:time, doctor_id: document.querySelector('#apptTable tr[data-id="'+id+'"] td:nth-child(3)').textContent}); if(r.success){ closeModal(); loadAppointments(); } else alert('Error:'+ (r.error||'unknown')); }); }));
}
qs('#apptFilterBtn').addEventListener('click', ()=>{ apptState.doctor = qs('#apptDoctorFilter').value; apptState.status = qs('#apptStatusFilter').value; apptState.start = qs('#apptStart').value; apptState.end = qs('#apptEnd').value; apptState.page = 1; loadAppointments(); });

// HEALTHCARDS
// qs('#hcLoadBtn').addEventListener('click', async ()=>{ const uid = qs('#hcUserId').value.trim(); if(!uid) return alert('Enter user id'); const r = await apiGet('get_healthcard', {user_id: uid}); if (r.error) { qs('#hcContent').innerText = 'Not found'; } else { qs('#hcContent').innerHTML = `<pre>${escapeHtml(JSON.stringify(r, null, 2))}</pre>`; } });

// modal helpers
function showModal(title, onOpen, onSave){ qs('#modal').classList.add('open'); qs('#modalBody').innerHTML = `<h3>${title}</h3><div id="modalInner">Loading...</div>`; if(onOpen) onOpen(qs('#modalInner')); qs('#modalSave').onclick = onSave; qs('#modalClose').onclick = closeModal; }
function closeModal(){ qs('#modal').classList.remove('open'); qs('#modalBody').innerHTML = ''; }

// utilities
function escapeHtml(s){ if(s==null) return ''; return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;"}[c]; }); }
function renderPager(selector, page, per, total, onPage){ const el = qs(selector); if(!el) return; const pages = Math.max(1, Math.ceil(total/per)); let html = `<div class='pagination'>Page ${page}/${pages}`; if(page>1) html += ` <button class='btn' data-p='${page-1}'>Prev</button>`; if(page<pages) html += ` <button class='btn' data-p='${page+1}'>Next</button>`; html += `</div>`; el.innerHTML = html; qsa(selector + ' .btn').forEach(b=>b.addEventListener('click', ()=> onPage(parseInt(b.dataset.p)))); }

// init load
loadOverview();

// populate doctor filter
(async function populateDoctorsFilter(){ const docs = await apiGet('list_doctors'); const sel = qs('#apptDoctorFilter'); for(const d of docs) { const opt = document.createElement('option'); opt.value = d.id; opt.text = (d.name||d.email||'Doctor') + (d.specialization?(' - '+d.specialization):''); sel.appendChild(opt); }})();

let hcState = { page:1, per:20, search:'' };

async function loadHealthcards(){
  const params = { page: hcState.page, per: hcState.per };
  if (hcState.search) params.search = hcState.search;

  const data = await apiGet('list_healthcards', params) || { rows: [], page: 1, per: hcState.per, total: 0 };
  const tbody = qs('#hcTable tbody');
  tbody.innerHTML = '';

  if (!Array.isArray(data.rows) || !data.rows.length) {
    tbody.innerHTML = '<tr><td colspan="6">No health cards found.</td></tr>';
  } else {
    data.rows.forEach(hc => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${hc.id}</td>
        <td>${escapeHtml(hc.patient_name)}</td>
        <td>${escapeHtml(hc.medical_history)}</td>
        <td>${escapeHtml(hc.allergies)}</td>
        <td>${escapeHtml(hc.notes)}</td>
        <td>${escapeHtml(hc.updated_at)}</td>
        <td><button class="btn editHC" data-id="${hc.id}">Edit</button></td>
      `;
      tbody.appendChild(tr);
    });
  }

  renderPager('#hcPager', data.page, data.per, data.total, p => {
    hcState.page = p;
    loadHealthcards();
  });
  
  qsa('.editHC').forEach(b => b.addEventListener('click', onEditHC));
}

// bind search
qs('#hcSearchBtn').addEventListener('click', ()=> {
  hcState.search = qs('#hcSearch').value.trim();
  hcState.page = 1;
  loadHealthcards();
});

function onEditHC(e) {
  const id = e.currentTarget.getAttribute('data-id');

  showModal('Edit Health Card', async (body) => {
    // load the health card from API
    const hc = await apiGet('get_healthcard', { id: id });

    // populate default fields with existing values
    body.innerHTML = `
      <label>Medical History</label>
      <textarea id="hc_medical_history" rows="4" style="width:100%">${escapeHtml(hc.medical_history || '')}</textarea>

      <label>Allergies</label>
      <textarea id="hc_allergies" rows="3" style="width:100%">${escapeHtml(hc.allergies || '')}</textarea>

      <label>Notes</label>
      <textarea id="hc_notes" rows="3" style="width:100%">${escapeHtml(hc.notes || '')}</textarea>
    `;

  }, async () => {
    // payload with updated values
    const payload = {
      id: id,
      medical_history: qs('#hc_medical_history').value.trim(),
      allergies: qs('#hc_allergies').value.trim(),
      notes: qs('#hc_notes').value.trim()
    };

    const res = await apiPost('update_healthcard', payload);

    if (res.success) {
      closeModal();
      loadHealthcards(); // refresh list
    } else {
      alert('Error: ' + (res.error || 'unknown'));
    }
  });
}


</script>
</body>
</html>
