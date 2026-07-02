# Medicus — Sistem për Menaxhimin e Pacientëve dhe Rezervimeve në një Klinikë

Aplikacion web (PHP/MySQL, arkitekturë MVC) për menaxhimin e pacienteve dhe rezervimeve në një klinikë: regjistrim dhe autentikim i përdoruesve, caktim i takimeve mes pacientëve dhe doktorëve, orar disponueshmërie, dhe kartelë shëndetësore digjitale.

## Stack-u teknik

- **Backend:** PHP 8.3, arkitekturë MVC
- **Databazë:** MySQL / MariaDB
- **Frontend:** Views PHP + Bootstrap
- **Testim:** suitë testesh API (bash + curl), CI/CD me GitHub Actions

## Struktura e projektit

```
app/
  Controllers/   → Auth, Admin, Appointment, Doctor, Patient, HealthCard, Page
  Models/        → User, Patient, Doctor, DoctorSchedule, Appointment, HealthCard
  Views/         → pamjet PHP
  Config/, Helpers/
database/
  schema.sql     → skema e databazës
routes/
  web.php        → hartëzimi i rrugëve (GET/POST) me controller-at
tests/
  seed_test_data.sql   → të dhëna testuese standarde
  run_api_tests.sh     → suitë testesh automatike (TC-01…TC-10, TC-SEC-01)
.github/workflows/     → CI pipeline (GitHub Actions)
public/                → entry point + assets
```

## Rolet e përdoruesve

- **Pacient** — regjistrohet, rezervon/anulon takime, shikon kartelën shëndetësore
- **Doktor** — miraton/refuzon kërkesa për takim, menaxhon orarin e disponueshmërisë
- **Admin** — mbikëqyr rezervimet nga paneli i administrimit

## Instalimi lokal

```bash
git clone <repo-url>
cd Medicus
# importo skemën
mysql -u root -p < database/schema.sql
# konfiguro lidhjen me DB te app/Config
# nise me server lokal (p.sh. XAMPP ose php -S)
php -S localhost:8000 -t public
```

## Testimi

```bash
bash tests/run_api_tests.sh
```

Suita ekzekuton teste komponenti, integrimi dhe sigurie kundrejt një serveri lokal (kërkon MariaDB + PHP aktiv). CI-ja në GitHub Actions e ekzekuton automatikisht në çdo push/PR.

