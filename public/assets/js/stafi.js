// Doctor filter migrated from script.js/stafi.js
function filterDoctors(specialty) {
  const allDoctors = document.querySelectorAll('.doctor-card');

  allDoctors.forEach(doctor => {
    if (specialty === 'all' || doctor.classList.contains(specialty)) {
      doctor.style.display = 'block';
    } else {
      doctor.style.display = 'none';
    }
  });
}