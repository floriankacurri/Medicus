<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Models/Patient.php';

use App\Models\Appointment;
use App\Models\Patient;

$current = current_user();
if (!$current) { http_response_code(401); echo json_encode(['error' => 'Unauthorized']); exit; }

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'])) { http_response_code(400); echo json_encode(['error' => 'Missing id']); exit; }

$apptModel = new Appointment($mysqli);
$appt = $apptModel->findById((int)$input['id']);
if (!$appt) { http_response_code(404); echo json_encode(['error' => 'Appointment not found']); exit; }

// Authorization: only owner patient or doctor/admin
$ownerOk = false;
if ($current['role'] === 'patient') {
    $patientModel = new Patient($mysqli);
    $patient = $patientModel->findByUserId((int)$current['id']);
    $ownerOk = $patient && ((int)$patient['id'] === (int)$appt['patient_id']);
} elseif ($current['role'] === 'doctor' || $current['role'] === 'admin') {
    $ownerOk = true;
}
if (!$ownerOk) { http_response_code(403); echo json_encode(['error' => 'Forbidden']); exit; }

// Mark status as 'cancelled'
$ok = $apptModel->updateStatus((int)$appt['id'], 'cancelled');
if ($ok) { echo json_encode(['success' => true]); exit; }
http_response_code(500); echo json_encode(['error' => 'Update failed']);
?>
