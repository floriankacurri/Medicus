<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Models/DoctorSchedule.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

use App\Models\DoctorSchedule;
use App\Models\Appointment;

// Params: doctor_id (required), date (YYYY-MM-DD, required), duration (minutes, optional), step (minutes, optional)
$doctorId = isset($_GET['doctor_id']) && $_GET['doctor_id'] !== '' ? (int)$_GET['doctor_id'] : null;
$date = isset($_GET['date']) ? trim($_GET['date']) : null;
$duration = isset($_GET['duration']) ? (int)$_GET['duration'] : Appointment::DEFAULT_DURATION_MIN;
$step = isset($_GET['step']) ? (int)$_GET['step'] : 15;
if ($step <= 0) $step = 15;
if (!$doctorId || !$date) { http_response_code(400); echo json_encode(['error' => 'Missing doctor_id or date']); exit; }

// validate date
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { http_response_code(400); echo json_encode(['error' => 'Invalid date format']); exit; }

$ds = new DoctorSchedule($mysqli);
$appt = new Appointment($mysqli);
$weekday = intval(date('w', strtotime($date)));
$intervals = $ds->getIntervalsForDay((int)$doctorId, $weekday);

$slots = [];
foreach ($intervals as $int) {
    $start = strtotime($date . ' ' . $int['start_time']);
    $end = strtotime($date . ' ' . $int['end_time']);
    // iterate from start to end-step ensuring the appointment fits entirely
    for ($t = $start; $t + ($duration * 60) <= $end; $t += ($step * 60)) {
        $timeStr = date('H:i:s', $t);
        // check availability (no overlap and fits schedule) by calling model
        $ok = $appt->isAvailable((int)$doctorId, $date, $timeStr, null, $duration);
        if ($ok) {
            $slots[] = [ 'start' => date('H:i', $t), 'end' => date('H:i', $t + ($duration * 60)) ];
        }
    }
}

$message = '';
if (empty($intervals)) {
    $message = 'Mjeku nuk ka publikuar disponueshmëri për këtë ditë.';
} elseif (empty($slots)) {
    $message = 'Nuk ka slot-e të lira për këtë ditë (ose janë bllokuar nga takimet e tjera).';
}

// default response object with slots and message
$response = ['slots' => $slots, 'message' => $message];

// backward compatibility: if client requested old array format
if (isset($_GET['format']) && $_GET['format'] === 'array') {
    echo json_encode($slots);
} else {
    echo json_encode($response);
}
?>
