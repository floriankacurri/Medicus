#!/usr/bin/env bash
# Medicus - API test suite (TC-01..TC-10, TC-SEC-01)
# Prerequisites:
#   1) MySQL/MariaDB running locally, schema imported: mysql -u root < database/schema.sql
#   2) Seed data imported: mysql -u root < tests/seed_test_data.sql
#   3) App served so that BASE_URL '/Medicus' resolves, e.g.:
#        php -S 127.0.0.1:8000 -t <parent-of-Medicus> router.php
#   4) All test accounts use password: Test1234!
#
# Usage: BASE=http://127.0.0.1:8000/Medicus bash tests/run_api_tests.sh

set -u
BASE="${BASE:-http://127.0.0.1:8000/Medicus}"
JAR_DIR=$(mktemp -d)
PASS=0
FAIL=0
RESULTS=()

record() {
  local id="$1" ok="$2" detail="$3"
  if [ "$ok" = "1" ]; then
    PASS=$((PASS+1)); RESULTS+=("PASS | $id | $detail")
  else
    FAIL=$((FAIL+1)); RESULTS+=("FAIL | $id | $detail")
  fi
}

login() {
  local jar="$1" email="$2" pass="$3"
  curl -s -c "$jar" -b "$jar" -X POST "$BASE/api/login.php" \
    -H 'Content-Type: application/json' \
    -d "{\"email\":\"$email\",\"password\":\"$pass\"}"
}

# Next Monday's date, used so it matches the doctor_schedules row we insert (day_of_week=1)
NEXT_MON=$(php -r 'echo date("Y-m-d", strtotime("next monday"));')
DOW=1

echo "== Seeding doctor availability for $NEXT_MON (day_of_week=$DOW) =="
mysql -u root -e "
USE medicus;
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time)
SELECT d.id, $DOW, '09:00:00', '13:00:00' FROM doctors d JOIN users u ON u.id=d.user_id WHERE u.email='doctor1@medicus.test';
"

JAR_P1="$JAR_DIR/patient1.jar"
JAR_P2="$JAR_DIR/patient2.jar"
JAR_DOC="$JAR_DIR/doctor1.jar"
JAR_ADMIN="$JAR_DIR/admin.jar"
JAR_ANON="$JAR_DIR/anon.jar"

login "$JAR_P1" "patient1@medicus.test" "Test1234!" > /dev/null
login "$JAR_P2" "patient2@medicus.test" "Test1234!" > /dev/null
login "$JAR_DOC" "doctor1@medicus.test" "Test1234!" > /dev/null
login "$JAR_ADMIN" "admin@medicus.test" "Test1234!" > /dev/null

echo "== TC-01: Regjistrim me email ekzistues (REQ-F-01) =="
R=$(curl -s -o /dev/null -w "%{http_code}" -X POST "$BASE/api/register.php" \
  -H 'Content-Type: application/json' \
  -d '{"name":"X","email":"patient1@medicus.test","password":"Test1234!"}')
[ "$R" = "409" ] && record TC-01 1 "HTTP $R (pritej 409 - email i zene)" || record TC-01 0 "HTTP $R (pritej 409)"

echo "== TC-02: Pacient rezervon slot te lire (REQ-F-02) =="
DOC1_ID=$(mysql -u root -N -e "USE medicus; SELECT d.id FROM doctors d JOIN users u ON u.id=d.user_id WHERE u.email='doctor1@medicus.test';")
RESP=$(curl -s -c "$JAR_P1" -b "$JAR_P1" -X POST "$BASE/api/create_appointment.php" \
  -H 'Content-Type: application/json' \
  -d "{\"doctor_id\":$DOC1_ID,\"date\":\"$NEXT_MON\",\"time\":\"09:00\",\"reason\":\"Kontroll rutine\"}")
APPT1_ID=$(echo "$RESP" | php -r '$d=json_decode(file_get_contents("php://stdin"),true); echo $d["id"] ?? "";')
if [ -n "$APPT1_ID" ]; then
  ST=$(mysql -u root -N -e "USE medicus; SELECT status FROM appointments WHERE id=$APPT1_ID;")
  [ "$ST" = "pending" ] && record TC-02 1 "Krijuar id=$APPT1_ID, status=pending" || record TC-02 0 "status=$ST"
else
  record TC-02 0 "Nuk u krijua: $RESP"
fi

echo "== TC-03: Pacient tjeter rezervon te njejtin slot 'pending'->duhet refuzuar mbivendosje (REQ-F-02/07) =="
RESP2=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_P2" -b "$JAR_P2" -X POST "$BASE/api/create_appointment.php" \
  -H 'Content-Type: application/json' \
  -d "{\"doctor_id\":$DOC1_ID,\"date\":\"$NEXT_MON\",\"time\":\"09:00\",\"reason\":\"Test mbivendosje\"}")
[ "$RESP2" = "409" ] && record TC-03 1 "HTTP $RESP2 (pritej 409 - slot i zene)" || record TC-03 0 "HTTP $RESP2 (pritej 409)"

echo "== TC-06: Doktori miraton kerkesen pending (REQ-F-05) =="
RESP3=$(curl -s -c "$JAR_DOC" -b "$JAR_DOC" -X POST "$BASE/api/update_appointment_status.php" \
  -H 'Content-Type: application/json' \
  -d "{\"id\":$APPT1_ID,\"status\":\"approved\"}")
ST2=$(mysql -u root -N -e "USE medicus; SELECT status FROM appointments WHERE id=$APPT1_ID;")
[ "$ST2" = "approved" ] && record TC-06 1 "status=approved" || record TC-06 0 "status=$ST2 resp=$RESP3"

echo "== TC-07: Pacienti perpiqet te miratoje vete (REQ-F-05, siguri) =="
RESP4=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_P1" -b "$JAR_P1" -X POST "$BASE/api/update_appointment_status.php" \
  -H 'Content-Type: application/json' \
  -d "{\"id\":$APPT1_ID,\"status\":\"approved\"}")
[ "$RESP4" = "403" ] && record TC-07 1 "HTTP $RESP4 (pritej 403)" || record TC-07 0 "HTTP $RESP4 (pritej 403)"

echo "== TC-05: Pacient ricakton takim TASHME 'approved' (REQ-F-04 - rregull biznesi) =="
RESP5=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_P1" -b "$JAR_P1" -X POST "$BASE/api/reschedule_appointment.php" \
  -H 'Content-Type: application/json' \
  -d "{\"id\":$APPT1_ID,\"date\":\"$NEXT_MON\",\"time\":\"10:00\"}")
[ "$RESP5" = "403" ] && record TC-05 1 "HTTP $RESP5 - sistemi bllokon ricaktimin e takimit 'approved' (ndryshe nga pershkrimi fillestar i draftit)" || record TC-05 0 "HTTP $RESP5 (pritej 403 sipas logjikes aktuale te kodit)"

echo "== TC-04: Pacient anulon takim pending (REQ-F-03) =="
RESP6=$(curl -s -c "$JAR_P2" -b "$JAR_P2" -X POST "$BASE/api/create_appointment.php" \
  -H 'Content-Type: application/json' \
  -d "{\"doctor_id\":$DOC1_ID,\"date\":\"$NEXT_MON\",\"time\":\"10:00\",\"reason\":\"Per anulim\"}")
APPT2_ID=$(echo "$RESP6" | php -r '$d=json_decode(file_get_contents("php://stdin"),true); echo $d["id"] ?? "";')
curl -s -c "$JAR_P2" -b "$JAR_P2" -X POST "$BASE/api/cancel_appointment.php" \
  -H 'Content-Type: application/json' -d "{\"id\":$APPT2_ID}" > /dev/null
ST3=$(mysql -u root -N -e "USE medicus; SELECT status FROM appointments WHERE id=$APPT2_ID;")
[ "$ST3" = "cancelled" ] && record TC-04 1 "status=cancelled" || record TC-04 0 "status=$ST3"

echo "== TC-08: Doktori shton interval te ri orar (REQ-F-06) =="
RESP7=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_DOC" -b "$JAR_DOC" -X POST "$BASE/api/doctor_schedule_add.php" \
  -H 'Content-Type: application/json' \
  -d '{"day_of_week":2,"start_time":"14:00","end_time":"17:00"}')
[ "$RESP7" = "200" ] && record TC-08 1 "HTTP $RESP7 - interval shtuar" || record TC-08 0 "HTTP $RESP7"

echo "== TC-09: Dy kerkesa pothuajse njekohesisht per te njejtin slot (REQ-F-07/NF-04) =="
DOC2_ID=$(mysql -u root -N -e "USE medicus; SELECT d.id FROM doctors d JOIN users u ON u.id=d.user_id WHERE u.email='doctor2@medicus.test';")
mysql -u root -e "
USE medicus;
INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time) VALUES ($DOC2_ID, $DOW, '09:00:00', '11:00:00');
"
( curl -s -c "$JAR_P1" -b "$JAR_P1" -X POST "$BASE/api/create_appointment.php" -H 'Content-Type: application/json' -d "{\"doctor_id\":$DOC2_ID,\"date\":\"$NEXT_MON\",\"time\":\"09:30\"}" -o "$JAR_DIR/race1.json" ) &
( curl -s -c "$JAR_P2" -b "$JAR_P2" -X POST "$BASE/api/create_appointment.php" -H 'Content-Type: application/json' -d "{\"doctor_id\":$DOC2_ID,\"date\":\"$NEXT_MON\",\"time\":\"09:30\"}" -o "$JAR_DIR/race2.json" ) &
wait
SUCCESS_COUNT=$(grep -l '"success"' "$JAR_DIR/race1.json" "$JAR_DIR/race2.json" 2>/dev/null | wc -l)
[ "$SUCCESS_COUNT" = "1" ] && record TC-09 1 "Vetem $SUCCESS_COUNT nga 2 kerkesat kaluan me sukses" || record TC-09 0 "$SUCCESS_COUNT nga 2 kerkesat kaluan me sukses (pritej 1)"

echo "== TC-10: Pacient perditeson karten shendetesore (REQ-F-08) =="
WHOAMI=$(curl -s -c "$JAR_P1" -b "$JAR_P1" "$BASE/api/whoami.php")
UID1=$(echo "$WHOAMI" | php -r '$d=json_decode(file_get_contents("php://stdin"),true); echo $d["id"] ?? "";')
RESP8=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_P1" -b "$JAR_P1" -X POST "$BASE/api/update_healthcard.php" \
  -H 'Content-Type: application/json' \
  -d "{\"user_id\":$UID1,\"medical_history\":\"Asgje e vecante\",\"allergies\":\"Penicilinat\",\"notes\":\"Test TC-10\"}")
PID1=$(mysql -u root -N -e "USE medicus; SELECT p.id FROM patients p JOIN users u ON u.id=p.user_id WHERE u.id=$UID1;")
ALLERGY=$(mysql -u root -N -e "USE medicus; SELECT allergies FROM health_cards WHERE patient_id=$PID1;")
[ "$ALLERGY" = "Penicilinat" ] && record TC-10 1 "health_cards.allergies u perditesua" || record TC-10 0 "HTTP=$RESP8 allergies='$ALLERGY'"

echo "== TC-SEC-01: Vizitor i pa-loguar hap /admin/dashboard (REQ-NF-01) =="
RESP9=$(curl -s -o /dev/null -w "%{http_code}" -c "$JAR_ANON" -b "$JAR_ANON" "$BASE/admin/dashboard")
[ "$RESP9" = "302" ] && record TC-SEC-01 1 "HTTP $RESP9 (redirect pritej 302)" || record TC-SEC-01 0 "HTTP $RESP9 (pritej 302)"

echo ""
echo "================ REZULTATET ================"
for line in "${RESULTS[@]}"; do echo "$line"; done
echo "=============================================="
echo "PASS: $PASS   FAIL: $FAIL   TOTAL: $((PASS+FAIL))"

rm -rf "$JAR_DIR"
