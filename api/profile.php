<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/Doctor.php';

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userModel = new User($mysqli);
$patientModel = new Patient($mysqli);
$doctorModel  = new Doctor($mysqli);

// Fetch common user info
$user = $userModel->findById((int)$current['id']);

$result = ['user' => $user];

// If the user is a patient, include patient fields
if ($current['role'] === 'patient') {
    $patient = $patientModel->findByUserId((int)$current['id']);
    if ($patient) {
        $result['patient'] = $patient;
    }
}

// If the user is a doctor, include doctor fields
if ($current['role'] === 'doctor') {
    $doctor = $doctorModel->findByUserId((int)$current['id']);
    if ($doctor) {
        $result['doctor'] = $doctor;
    }
}

echo json_encode($result);