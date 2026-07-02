<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/Doctor.php';
require_once __DIR__ . '/../app/Models/Appointment.php';

use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$appointmentModel = new Appointment($mysqli);

if ($current['role'] === 'patient') {
    $patientModel = new Patient($mysqli);
    $patient = $patientModel->findByUserId((int)$current['id']);
    if (!$patient) {
        http_response_code(404);
        echo json_encode(['error' => 'Patient profile not found']);
        exit;
    }
    $appointments = $appointmentModel->getByPatientId((int)$patient['id']);
    echo json_encode($appointments);
    exit;
}

if ($current['role'] === 'doctor') {
    $doctorModel = new Doctor($mysqli);
    $doctor = $doctorModel->findByUserId((int)$current['id']);
    if (!$doctor) {
        http_response_code(404);
        echo json_encode(['error' => 'Doctor profile not found']);
        exit;
    }
    $appointments = $appointmentModel->getByDoctorId((int)$doctor['id']);
    echo json_encode($appointments);
    exit;
}

http_response_code(403);
echo json_encode(['error' => 'Forbidden']);
?>