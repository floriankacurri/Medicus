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
      <div class="form-card" style="max-width:640px;margin:24px auto;">
        <h2>Profili im</h2>
        <form id="profileForm">
          <label for="p_name">Emri</label>
          <input id="p_name" type="text" required>

          <label for="p_email">Email</label>
          <input id="p_email" type="email" required>

          <!-- Patient fields -->
          <div id="patientFields" style="margin:16px 0 25px;">
            <label for="p_dob">Data e lindjes</label>
            <input id="p_dob" type="date">

            <label for="p_gender">Gjinia</label>
            <select id="p_gender">
              <option value="">Zgjidh</option>
              <option value="male">Mashkull</option>
              <option value="female">Femër</option>
              <option value="other">Tjetër</option>
            </select>
          </div>

          <!-- Doctor fields -->
          <div id="doctorFields" style="display:none; margin:16px 0 25px;">
            <label for="p_specialization">Specializimi</label>
            <input id="p_specialization" type="text">
          </div>

          <div style="margin-top:12px;display:flex;gap:8px;">
            <button class="btn btn-primary" type="submit">Ruaj</button>
            <a href="<?php echo BASE_URL; ?>/<?php echo $currentUser['role']=='doctor' ? 'doctor/dashboard' : 'patient/dashboard'; ?>" class="btn btn-ghost">Kthehu</a>
          </div>

          <div id="profileMsg" class="mt-2" role="status"></div>
        </form>
      </div>
    </main>
  </div>
</section>

<script>
(async function(){
  var BASE = window.BASE || '<?php echo BASE_URL; ?>';
  var currentRole = null;

  async function loadProfile(){
    var res = await fetch(BASE + '/api/profile.php');
    if (!res.ok) return;
    var data = await res.json();
    if (data.user) {
      currentRole = data.user.role;
      document.getElementById('p_name').value = data.user.name || '';
      document.getElementById('p_email').value = data.user.email || '';
    }

    // show fields depending on role
    if (currentRole === 'patient') {
      document.getElementById('patientFields').style.display = 'block';
      document.getElementById('doctorFields').style.display = 'none';
      if (data.patient) {
        document.getElementById('p_dob').value = data.patient.date_of_birth || '';
        document.getElementById('p_gender').value = data.patient.gender || '';
      }
    } else if (currentRole === 'doctor') {
      document.getElementById('doctorFields').style.display = 'block';
      document.getElementById('patientFields').style.display = 'none';
      if (data.doctor) {
        document.getElementById('p_specialization').value = data.doctor.specialization || '';
      }
    }
  }

    function clearValidationErrors() {
      // Remove any existing error messages
      document.querySelectorAll('.field-error').forEach(el => el.remove());
    }

    // Show errors under each form field
    function displayValidationErrors(errors) {
      clearValidationErrors();

      Object.keys(errors).forEach(fieldName => {
        let field = document.getElementById('p_' + fieldName) || document.getElementById('p_' + fieldName.replace('_', ''));
        if (field) {
          let errorMsg = document.createElement('div');
          errorMsg.className = 'field-error';
          errorMsg.style.color = 'red';
          errorMsg.style.marginTop = '4px';
          errorMsg.textContent = errors[fieldName];
          field.insertAdjacentElement('afterend', errorMsg);
        }
      });
    }

    document.getElementById('profileForm').addEventListener('submit', async function(e){
      e.preventDefault();
      clearValidationErrors(); // clear old messages

      var msg = document.getElementById('profileMsg');
      msg.textContent = '';
      msg.className = '';

      var payload = {
        name: document.getElementById('p_name').value.trim(),
        email: document.getElementById('p_email').value.trim()
      };

      if (currentRole === 'patient') {
        payload.date_of_birth = document.getElementById('p_dob').value || null;
        payload.gender = document.getElementById('p_gender').value || null;
      } else if (currentRole === 'doctor') {
        payload.specialization = document.getElementById('p_specialization').value.trim();
      }

      const res = await fetch(BASE + '/api/update_profile.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const result = await res.json().catch(() => ({}));

      if (!res.ok) {
        if (result.errors) {
          // Show field-specific errors
          displayValidationErrors(result.errors);
        } else {
          // Show general error
          msg.textContent = result.error || 'Dështoi';
          msg.className = 'msg error mt-2';
        }
      } else {
        // Success
        msg.textContent = 'Profil u ruajt.';
        msg.className = 'msg success mt-2';
      }
    });

  loadProfile();
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>