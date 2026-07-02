// Minimal booking stepper for Medicus
(function(){
  if (typeof window === 'undefined') return;
  var BASE = window.BASE || '';
  var toStep2 = document.getElementById('toStep2');
  var toStep3 = document.getElementById('toStep3');
  var backTo1 = document.getElementById('backTo1');
  var backTo2 = document.getElementById('backTo2');
  var submitBtn = document.getElementById('submitBooking');
  var doctorSelect = document.getElementById('doctorSelect');
  var doctorsLoading = document.getElementById('doctorsLoading');
  var bookingStatus = document.getElementById('bookingStatus');
  var dateInput = document.getElementById('date');
  var timeSelect = document.getElementById('time');
  var slotsLoading = document.getElementById('slotsLoading');

  var DEFAULT_DURATION = 30; // fixed server-side duration

  function showStep(n) {
    document.querySelectorAll('.booking-step').forEach(function(el){ el.style.display = 'none'; });
    document.getElementById('step-' + n).style.display = 'block';
    document.querySelectorAll('#stepper .step').forEach(function(el, idx){
      el.classList.toggle('active', idx === (n-1));
    });
    // when showing step 2, attempt to load slots immediately
    if (n === 2) loadSlots();
  }

  function loadDoctors() {
    if (!doctorsLoading) return;
    doctorsLoading.style.display = 'block';
    fetch(BASE + '/api/doctors.php').then(function(res){
      return res.json();
    }).then(function(list){
      doctorsLoading.style.display = 'none';
      if (!Array.isArray(list)) return;
      list.forEach(function(d){
        var opt = document.createElement('option');
        opt.value = d.id || '';
        opt.textContent = (d.name || 'Dr. Unknown') + (d.specialization ? ' – ' + d.specialization : '');
        doctorSelect.appendChild(opt);
      });
    }).catch(function(){
      doctorsLoading.textContent = 'Dështoi ngarkimi i mjekëve.';
    });
  }

  function gatherReview() {
    var doctorOpt = doctorSelect.options[doctorSelect.selectedIndex];
    var doctorText = doctorOpt ? doctorOpt.textContent : 'Pa preferencë';
    var date = document.getElementById('date').value;
    var timeVal = document.getElementById('time').value;
    var reason = document.getElementById('reason').value;
    var duration = DEFAULT_DURATION;
    var review = document.getElementById('review');
    review.innerHTML = '<p><strong>Mjeku:</strong> ' + escapeHtml(doctorText) + '</p>' +
                       '<p><strong>Data & Ora:</strong> ' + escapeHtml(date + ' ' + timeVal) + '</p>' +
                       '<p><strong>Kohëzgjatja:</strong> ' + escapeHtml(duration) + ' min</p>' +
                       (reason ? '<p><strong>Arsyeja:</strong> ' + escapeHtml(reason) + '</p>' : '');
  }

  function escapeHtml(s){ if (s===null || s===undefined) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

  function createICS(title, description, startDate, durationMinutes) {
    // startDate: 'YYYY-MM-DD' and time as 'HH:MM'
    var dt = startDate.split(' ');
    if (dt.length < 2) return null;
    var datePart = dt[0].replace(/-/g,'');
    var timePart = (dt[1] || '').replace(/:/g,'') + '00';
    var dtstart = datePart + 'T' + timePart;
    var end = new Date(dt[0] + 'T' + dt[1]);
    end.setMinutes(end.getMinutes() + (durationMinutes||30));
    var y = end.getFullYear(), m = (''+(end.getMonth()+1)).padStart(2,'0'), d = (''+end.getDate()).padStart(2,'0');
    var hh = (''+end.getHours()).padStart(2,'0'), mm = (''+end.getMinutes()).padStart(2,'0');
    var dtend = '' + y + m + d + 'T' + hh + mm + '00';
    var ics = 'BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n';
    ics += 'SUMMARY:' + title + '\r\n';
    ics += 'DESCRIPTION:' + description + '\r\n';
    ics += 'DTSTART:' + dtstart + '\r\n';
    ics += 'DTEND:' + dtend + '\r\n';
    ics += 'END:VEVENT\r\nEND:VCALENDAR';
    return ics;
  }

  if (toStep2) toStep2.addEventListener('click', function(){
    // require a doctor selection before moving to step 2
    if (!doctorSelect || !doctorSelect.value) {
      bookingStatus.textContent = 'Ju lutem zgjidhni një mjek për të vazhduar.';
      bookingStatus.className = 'msg error mt-2';
      if (doctorSelect) doctorSelect.focus();
      return;
    }
    bookingStatus.textContent = '';
    bookingStatus.className = '';
    showStep(2);
  });
  if (backTo1) backTo1.addEventListener('click', function(){ showStep(1); });
  if (toStep3) toStep3.addEventListener('click', function(){
    // simple validation
    var date = document.getElementById('date').value;
    var timeVal = document.getElementById('time').value;
    if (!date || !timeVal) {
      bookingStatus.textContent = 'Zgjidhni datën dhe orën për të vazhduar.';
      bookingStatus.className = 'msg error mt-2';
      return;
    }
    bookingStatus.textContent = '';
    gatherReview();
    showStep(3);
  });
  if (backTo2) backTo2.addEventListener('click', function(){ showStep(2); });

  if (submitBtn) submitBtn.addEventListener('click', function(){
    submitBtn.disabled = true;
    bookingStatus.textContent = '';
    var duration = DEFAULT_DURATION;
    var timeVal = document.getElementById('time').value;
    var payload = {
      doctor_id: doctorSelect.value || null,
      date: document.getElementById('date').value,
      time: timeVal,
      reason: document.getElementById('reason').value || '',
      duration: duration
    };
    if (!payload.time) {
      bookingStatus.textContent = 'Zgjidhni një orë për të vazhduar.';
      bookingStatus.className = 'msg error mt-2';
      submitBtn.disabled = false;
      return;
    }
    fetch(BASE + '/api/create_appointment.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    }).then(function(res){
      return res.json().then(function(data){ return { ok: res.ok, status: res.status, data: data }; }).catch(function(){ return { ok: res.ok, status: res.status, data: {} }; });
    }).then(function(res){
      if (res.ok) {
        bookingStatus.textContent = 'Rezervimi u dërgua. Do të njoftoheni kur të aprovohet.';
        bookingStatus.className = 'msg success mt-2';
        // offer ICS download
        var id = res.data && res.data.id ? res.data.id : null;
        var title = 'Takim me Medicus';
        var when = payload.date + ' ' + payload.time;
        var desc = payload.reason || (payload.doctor_id ? 'Takim me mjekun' : 'Takim i rezervuar në Medicus');
        var ics = createICS(title, desc, when, duration);
        if (ics) {
          var blob = new Blob([ics], { type: 'text/calendar' });
          var url = URL.createObjectURL(blob);
          var a = document.createElement('a');
          a.href = url;
          a.download = 'medicus-appointment' + (id ? ('-' + id) : '') + '.ics';
          a.textContent = 'Shkarko kalendarin (ICS)';
          a.style.display = 'inline-block';
          a.style.marginLeft = '10px';
          bookingStatus.appendChild(a);
        }
        // short redirect
        setTimeout(function(){ window.location = BASE + '/patient/dashboard'; }, 2200);
      } else {
        if (res.status === 409) {
          bookingStatus.textContent = res.data.error || 'Koha e zgjedhur përputhet me një takim tjetër. Zgjidh një kohë tjetër.';
        } else if (res.status === 422) {
          bookingStatus.textContent = res.data.error || 'Mjeku nuk është i disponueshëm në këtë kohë.';
        } else {
          bookingStatus.textContent = (res.data && res.data.error) ? res.data.error : 'Dështoi rezervimi.';
        }
        bookingStatus.className = 'msg error mt-2';
      }
    }).catch(function(){
      bookingStatus.textContent = 'Gabim në lidhje. Provoni përsëri.';
      bookingStatus.className = 'msg error mt-2';
    }).finally(function(){ submitBtn.disabled = false; });
  });

  // populate slots when doctor/date changes (fixed duration)
  async function loadSlots() {
    if (!timeSelect) return;
    var doctorId = doctorSelect.value;
    var date = dateInput.value;
    var duration = DEFAULT_DURATION;
    timeSelect.innerHTML = '';
    let placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = date ? 'Zgjidh orën' : 'Zgjidh datën për të parë oraret';
    timeSelect.appendChild(placeholder);

    if (!doctorId) {
      // if no specific doctor selected, don't try to fetch aggregated slots
      return;
    }
    if (!date) return;

    if (slotsLoading) slotsLoading.style.display = 'block';
    try {
      var q = new URLSearchParams({ doctor_id: doctorId, date: date, duration: duration, step: 15 });
      var res = await fetch(BASE + '/api/available_slots.php?' + q.toString());
      if (!res.ok) throw new Error('Network');
      var data = await res.json();
      // handle new object response { slots: [], message: '' }
      var slots = Array.isArray(data) ? data : (data && data.slots ? data.slots : []);
      var message = data && data.message ? data.message : '';

      timeSelect.innerHTML = '';
      if (!Array.isArray(slots) || !slots.length) {
        let optEmpty = document.createElement('option'); optEmpty.value = ''; optEmpty.textContent = message || 'Nuk ka orare të disponueshme'; timeSelect.appendChild(optEmpty);
      } else {
        let opt0 = document.createElement('option'); opt0.value = ''; opt0.textContent = 'Zgjidh orën'; timeSelect.appendChild(opt0);
        slots.forEach(function(s){
          let opt = document.createElement('option'); opt.value = s.start; opt.textContent = s.start + ' – ' + s.end; timeSelect.appendChild(opt);
        });
      }
    } catch (err) {
      console.error('Failed to load slots', err);
      // leave placeholder
      timeSelect.innerHTML = '';
      let optFail = document.createElement('option'); optFail.value = ''; optFail.textContent = 'Dështoi ngarkimi i orareve'; timeSelect.appendChild(optFail);
    } finally {
      if (slotsLoading) slotsLoading.style.display = 'none';
    }
  }

  // bind events
  if (doctorSelect) doctorSelect.addEventListener('change', loadSlots);
  if (dateInput) dateInput.addEventListener('change', loadSlots);

  // init
  loadDoctors();
  showStep(1);

})();
