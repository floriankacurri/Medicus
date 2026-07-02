<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Models/Doctor.php';

use App\Models\Appointment;
use App\Models\Doctor;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'], $data['status'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing id or status']);
    exit;
}

$appointmentModel = new Appointment($mysqli);
$appt = $appointmentModel->findById((int)$data['id']);
if (!$appt) {
    http_response_code(404);
    echo json_encode(['error' => 'Appointment not found']);
    exit;
}

$status = $data['status'];

// Only allow valid statuses
if (!in_array($status, ['approved','cancelled','pending','refused'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid status']);
    exit;
}

// ==============================
// ROLE BASED UPDATE RULES
// ==============================

// DOCTOR or ADMIN
if ($current['role'] === 'doctor' || $current['role'] === 'admin') {

    // Must find doctor id if doctor
    $doctorModel = new Doctor($mysqli);
    $doctor = $doctorModel->findByUserId((int)$current['id']);
    $doctor_id = $doctor ? (int)$doctor['id'] : null;

    // Approving or refusing appointment
    if ($status === 'approved') {
        if (!$doctor_id) {
            http_response_code(403);
            echo json_encode(['error' => 'Doctor profile not found']);
            exit;
        }
        $appointmentModel->assignToDoctor((int)$data['id'], $doctor_id);
    }
    $appointmentModel->updateStatus((int)$data['id'], $status);
    echo json_encode(['success' => 'Status updated']);
    exit;
}

// PATIENT
if ($current['role'] === 'patient') {
    // Patients can only cancel or request a "pending" change, not approve/refuse
    // AND only if the appointment is not already approved.
    $currentStatus = $appt['status'];

    // Check allowed transitions
    // You can cancel anytime before it is cancelled
    if ($status === 'cancelled') {
        $appointmentModel->updateStatus((int)$data['id'], 'cancelled');
        echo json_encode(['success' => 'Appointment cancelled']);
        exit;
    }

    // Patients can only set status to 'pending' or 'refused'
    if (in_array($status, ['pending','refused'])) {
        // Allowed only if not already approved
        if ($currentStatus === 'approved') {
            http_response_code(403);
            echo json_encode(['error' => 'Cannot change an approved appointment']);
            exit;
        }
        $appointmentModel->updateStatus((int)$data['id'], $status);
        echo json_encode(['success' => 'Appointment status updated']);
        exit;
    }

    // Otherwise forbidden
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// ALL OTHER ROLES
http_response_code(403);
echo json_encode(['error' => 'Forbidden']);
exit;
?>