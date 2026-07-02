<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Doctor.php';
require_once __DIR__ . '/../app/Models/DoctorSchedule.php';

use App\Models\Doctor;
use App\Models\DoctorSchedule;

$current = current_user();
if (!$current) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }
if ($current['role'] !== 'doctor' && $current['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['day_of_week'], $input['start_time'], $input['end_time'])) { http_response_code(400); echo json_encode(['error' => 'Missing parameters']); exit; }

$day = (int)$input['day_of_week'];
$start = $input['start_time'];
$end = $input['end_time'];

// Basic validation: start < end
$startTs = strtotime($start);
$endTs = strtotime($end);
if ($startTs === false || $endTs === false || $startTs >= $endTs) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid interval: ensure start_time < end_time and valid times']);
    exit;
}

$doctorModel = new Doctor($mysqli);
$doctor = $doctorModel->findByUserId((int)$current['id']);
if (!$doctor) { http_response_code(404); echo json_encode(['error' => 'Doctor profile not found']); exit; }

$ds = new DoctorSchedule($mysqli);
// check for overlaps with existing intervals for this doctor on the same day
$existing = $ds->getByDoctorId((int)$doctor['id']);
foreach ($existing as $row) {
    if ((int)$row['day_of_week'] !== $day) continue;
    $exStart = strtotime($row['start_time']);
    $exEnd = strtotime($row['end_time']);
    // overlap if newStart < exEnd && exStart < newEnd
    if ($startTs < $exEnd && $exStart < $endTs) {
        http_response_code(409);
        echo json_encode(['error' => 'New interval overlaps existing schedule interval']);
        exit;
    }
}

$ok = $ds->addInterval((int)$doctor['id'], $day, $start, $end);
if ($ok) { echo json_encode(['success' => true]); exit; }
http_response_code(500); echo json_encode(['error' => 'Insert failed']);
?>
