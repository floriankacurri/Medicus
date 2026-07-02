<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Models/DoctorSchedule.php';

use App\Models\Patient;
use App\Models\Appointment;
use App\Models\DoctorSchedule;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($current['role'] !== 'patient') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data || !isset($data['date'], $data['time'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing date or time']);
    exit;
}

$patientModel = new Patient($mysqli);
$patient = $patientModel->findByUserId((int)$current['id']);
if (!$patient) {
    http_response_code(404);
    echo json_encode(['error' => 'Patient profile not found']);
    exit;
}

$appointmentModel = new Appointment($mysqli);
$doctorId = isset($data['doctor_id']) && $data['doctor_id'] !== '' ? (int)$data['doctor_id'] : null;
// Require a doctor to be selected by patient
if (!$doctorId) {
    http_response_code(400);
    echo json_encode(['error' => 'Zgjidhni nje mjek për rezervimin.']);
    exit;
}
// Do not trust client-supplied duration for patient-created appointments. Use server default.
$duration = Appointment::DEFAULT_DURATION_MIN;

if ($doctorId) {
    // Server-side availability check using model
    $isOk = $appointmentModel->isAvailable((int)$doctorId, $data['date'], $data['time'], null, $duration);
    if (!$isOk) {
        // Could be overlap or outside schedule; try to distinguish by checking schedule containment
        try {
            $ds = new \App\Models\DoctorSchedule($mysqli);
            $weekday = intval(date('w', strtotime($data['date'])));
            $start = date('H:i:s', strtotime($data['time']));
            $end = date('H:i:s', strtotime($start) + ($duration * 60));
            if (!$ds->isIntervalWithinSchedule((int)$doctorId, $weekday, $start, $end)) {
                http_response_code(422);
                echo json_encode(['error' => 'Requested time is outside doctor schedule']);
                exit;
            }
        } catch (\Throwable $e) {
            // ignore
        }
        http_response_code(409);
        echo json_encode(['error' => 'Selected time slot overlaps with another appointment for this doctor']);
        exit;
    }
}

$reason = isset($data['reason']) ? trim($data['reason']) : null;
if ($appointmentModel->create((int)$patient['id'], $doctorId, $data['date'], $data['time'], $duration, 'pending', $reason)) {
    $newId = $appointmentModel->getLastInsertId();
    error_log('Appointment created successfully. Patient ID: ' . (int)$patient['id'] . ', Doctor ID: ' . $doctorId . ', New Appointment ID: ' . $newId);
    http_response_code(201);
    echo json_encode(['success' => 'Appointment requested', 'id' => (int)$newId]);
} else {
    error_log('Appointment creation failed. Patient ID: ' . (int)$patient['id'] . ', Doctor ID: ' . $doctorId . ', Date: ' . $data['date'] . ', Time: ' . $data['time']);
    http_response_code(500);
    echo json_encode(['error' => 'Could not create appointment']);
}
?>