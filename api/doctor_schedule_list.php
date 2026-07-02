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

// Only doctors and admins can list/edit schedules
if ($current['role'] !== 'doctor' && $current['role'] !== 'admin') { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }

$doctorModel = new Doctor($mysqli);
$doctor = $doctorModel->findByUserId((int)$current['id']);
if (!$doctor) { http_response_code(404); echo json_encode(['error' => 'Doctor profile not found']); exit; }

$ds = new DoctorSchedule($mysqli);
$list = $ds->getByDoctorId((int)$doctor['id']);

echo json_encode($list);
?>
