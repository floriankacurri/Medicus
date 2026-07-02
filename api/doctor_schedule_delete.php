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
if (!isset($input['id'])) { http_response_code(400); echo json_encode(['error' => 'Missing id']); exit; }

$doctorModel = new Doctor($mysqli);
$doctor = $doctorModel->findByUserId((int)$current['id']);
if (!$doctor) { http_response_code(404); echo json_encode(['error' => 'Doctor profile not found']); exit; }

$ds = new DoctorSchedule($mysqli);
// Ensure the schedule entry belongs to this doctor (simple ownership check)
$list = $ds->getByDoctorId((int)$doctor['id']);
$found = false; foreach ($list as $row) { if ($row['id'] == $input['id']) { $found = true; break; } }
if (!$found) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }

$ok = $ds->delete((int)$input['id']);
if ($ok) { echo json_encode(['success' => true]); exit; }
http_response_code(500); echo json_encode(['error' => 'Delete failed']);
?>
