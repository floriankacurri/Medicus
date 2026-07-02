<?php
if (!isset($currentUser)) { header('Location: ' . BASE_URL . '/'); exit; }
$is_doctor = isset($is_doctor) ? $is_doctor : (($currentUser['role'] ?? '') === 'doctor');
$viewUserId = isset($_GET['user_id']) && $is_doctor ? (int)$_GET['user_id'] : (int)$currentUser['id'];
// allow editing if doctor or viewing own card
$canEdit = $is_doctor || ($viewUserId === (int)$currentUser['id']);
include_once __DIR__ . '/../layouts/header.php';
?>

<section class="page-container">
  <div class="dashboard">
    <aside class="sidebar">
      <?php include __DIR__ . '/../layouts/sidebar.php'; ?>
    </aside>
    <main class="content">
      <div class="form-card" style="max-width: 640px;">
        <h2>Karta e shëndetit</h2>
        <?php if ($is_doctor): ?>
          <div style="margin-bottom: 16px;">
            <label for="patientSelect">Zgjidh pacientin</label>
            <select id="patientSelect" style="width:100%;padding:10px;border-radius:6px;border:1px solid var(--color-border);">
              <option value="">Zgjidh një pacient...</option>
            </select>
          </div>
        <?php endif; ?>

        <div id="cardArea">Duke ngarkuar...</div>

        <?php if ($canEdit): ?>
          <div id="noCardActions" style="display:none;margin-top:12px;"></div>
          <div id="editArea" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid var(--color-border);">
            <h3 style="margin: 0 0 16px; font-size: 1.1rem;">Ndrysho karten e shëndetit</h3>
            <form id="editCard">
              <input type="hidden" id="target_user_id" value="<?php echo $viewUserId; ?>">
              <label for="medical_history">Historiku mjekësor</label>
              <textarea id="medical_history" rows="3"></textarea>
              <label for="allergies">Alergjitë</label>
              <textarea id="allergies" rows="2"></textarea>
              <label for="notes">Shënime</label>
              <textarea id="notes" rows="2"></textarea>
              <button type="submit" class="btn btn-primary" style="margin-top: 16px;">Ruaj</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </main>
  </div>
</section>

<script>
(function(){
  var BASE = '<?php echo BASE_URL; ?>';
  var isDoctor = <?php echo $is_doctor ? 'true' : 'false'; ?>;
  var canEdit = <?php echo $canEdit ? 'true' : 'false'; ?>;
  var viewUserId = <?php echo (int)$viewUserId; ?>;

  function displayCard(data) {
    var area = document.getElementById('cardArea');
    area.innerHTML = '<p><strong>Historiku mjekësor:</strong> ' + (data.medical_history || '–') + '</p>' +
      '<p><strong>Alergjitë:</strong> ' + (data.allergies || '–') + '</p>' +
      '<p><strong>Shënime:</strong> ' + (data.notes || '–') + '</p>';
  }

  function showCreateActions() {
    var container = document.getElementById('noCardActions');
    container.innerHTML = '';
    var btn = document.createElement('button');
    btn.className = 'btn btn-primary';
    btn.textContent = 'Krijo kartën e shëndetit';
    btn.addEventListener('click', function(){
      document.getElementById('editArea').style.display = 'block';
      document.getElementById('medical_history').value = '';
      document.getElementById('allergies').value = '';
      document.getElementById('notes').value = '';
      document.getElementById('target_user_id').value = viewUserId;
      // smooth scroll to form
      document.getElementById('editArea').scrollIntoView({ behavior: 'smooth' });
    });
    container.appendChild(btn);
    container.style.display = 'block';
  }

  async function populatePatients() {
    try {
      var res = await fetch(BASE + '/api/patients.php');
      if (!res.ok) return;
      var list = await res.json();
      var sel = document.getElementById('patientSelect');
      if (!sel) return;
      list.forEach(function(p){
        var opt = document.createElement('option');
        opt.value = p.user_id;
        opt.textContent = (p.name || ('User ' + p.user_id)) + (p.date_of_birth ? (' – ' + p.date_of_birth) : '');
        sel.appendChild(opt);
      });
      sel.addEventListener('change', function(){
        var uid = sel.value;
        if (uid) { viewUserId = uid; loadCard(uid); }
      });
    } catch (e) { console.error(e); }
  }

  window.loadCard = function(userId) {
    var url = BASE + '/api/healthcard.php' + (userId ? ('?user_id=' + userId) : '');
    fetch(url).then(function(res) {
      if (!res.ok) {
        if (res.status === 404 && canEdit) {
          document.getElementById('cardArea').innerText = 'Nuk u gjet karta. Mund ta krijoni më poshtë.';
          showCreateActions();
          document.getElementById('editArea').style.display = 'none';
        } else {
          document.getElementById('cardArea').innerText = 'Nuk u gjet karta.';
        }
        return;
      }
      return res.json();
    }).then(function(data) {
      if (!data) return;
      displayCard(data);
      if (canEdit) {
        document.getElementById('editArea').style.display = 'block';
        document.getElementById('target_user_id').value = userId || viewUserId;
        document.getElementById('medical_history').value = data.medical_history || '';
        document.getElementById('allergies').value = data.allergies || '';
        document.getElementById('notes').value = data.notes || '';
      }
    }).catch(function(){
      document.getElementById('cardArea').innerText = 'Nuk u gjet karta.';
    });
  };

  if (isDoctor) {
    populatePatients();
  }

  if (canEdit) {
    document.getElementById('editCard').addEventListener('submit', function(e){
      e.preventDefault();
      var payload = {
        user_id: parseInt(document.getElementById('target_user_id').value, 10),
        medical_history: document.getElementById('medical_history').value.trim(),
        allergies: document.getElementById('allergies').value.trim(),
        notes: document.getElementById('notes').value.trim()
      };
      fetch(BASE + '/api/update_healthcard.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      }).then(function(res) {
        return res.json().catch(function(){ return {}; });
      }).then(function(d) {
        if (d.success !== undefined) { alert('U ruajt.'); loadCard(payload.user_id); }
        else { alert(d.error || 'Ruajtja dështoi.'); }
      }).catch(function(){ alert('Gabim komunikimi.'); });
    });
  }
  loadCard(viewUserId);
})();
</script>

<?php include_once __DIR__ . '/../layouts/footer.php'; ?>
