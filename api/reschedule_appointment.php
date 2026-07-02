<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/Doctor.php';
require_once __DIR__ . '/../app/Models/DoctorSchedule.php';

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\DoctorSchedule;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['id'], $input['date'], $input['time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing parameters']);
    exit;
}

$apptModel = new Appointment($mysqli);
$appt = $apptModel->findById((int)$input['id']);
if (!$appt) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit;
}

// Check ownership/permissions
$ownerOk = false;
if ($current['role'] === 'patient') {
    $patientModel = new Patient($mysqli);
    $patient = $patientModel->findByUserId((int)$current['id']);
    $ownerOk = $patient && ((int)$patient['id'] === (int)$appt['patient_id']);
} elseif ($current['role'] === 'doctor' || $current['role'] === 'admin') {
    $ownerOk = true;
}
if (!$ownerOk) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Patients can only reschedule if the appointment is not already approved
if ($current['role'] === 'patient' && $appt['status'] === 'approved') {
    http_response_code(403);
    echo json_encode(['error' => 'Cannot reschedule an approved appointment']);
    exit;
}

$doctor_id = $appt['doctor_id'];

// Determine new duration
if ($current['role'] === 'doctor' || $current['role'] === 'admin') {
    $requestedDuration = isset($input['duration']) ? (int)$input['duration'] : null;
    $duration = $requestedDuration ?: (isset($appt['duration_minutes']) ? (int)$appt['duration_minutes'] : Appointment::DEFAULT_DURATION_MIN);
} else {
    $duration = isset($appt['duration_minutes']) ? (int)$appt['duration_minutes'] : Appointment::DEFAULT_DURATION_MIN;
}
if ($duration <= 0) $duration = Appointment::DEFAULT_DURATION_MIN;

// Check availability if doctor assigned
if ($doctor_id) {
    $ok = $apptModel->isAvailable((int)$doctor_id, $input['date'], $input['time'], (int)$appt['id'], $duration);
    if (!$ok) {
        http_response_code(409);
        echo json_encode(['error' => 'Doctor is not available at this time']);
        exit;
    }
}

// Update appointment datetime
$updated = $apptModel->updateDateTime((int)$appt['id'], $input['date'], $input['time'], $duration);

// If patient rescheduled: set status to pending so doctor must re‑approve
if ($updated && $current['role'] === 'patient') {
    $apptModel->updateStatus((int)$appt['id'], 'pending');
}

if ($updated) {
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(500);
echo json_encode(['error' => 'Update failed']);
?>