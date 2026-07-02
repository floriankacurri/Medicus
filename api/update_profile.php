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

$data = json_decode(file_get_contents('php://input'), true) ?: [];

$userModel    = new User($mysqli);
$patientModel = new Patient($mysqli);
$doctorModel  = new Doctor($mysqli);

$errors = [];

// ---- Validate basic fields ----

// Name required
if (!isset($data['name']) || trim($data['name']) === '') {
    $errors['name'] = 'Emri është i detyrueshëm.';
}

// Email required + valid format
if (!isset($data['email']) || trim($data['email']) === '') {
    $errors['email'] = 'Email është i detyrueshëm.';
} elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Email i pavlefshëm.';
} else {
    // unique check
    $existing = $userModel->findByEmail($data['email']);
    if ($existing && $existing['id'] != $current['id']) {
        $errors['email'] = 'Ky email tashmë është përdorur.';
    }
}

// ---- Role-specific validation ----

if ($current['role'] === 'patient') {
    // Validate gender if present
    if (isset($data['gender']) && !in_array($data['gender'], ['male','female','other'], true)) {
        $errors['gender'] = 'Zgjidhni një gjini të vlefshme.';
    }
    // Validate date
    if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
        $date = date_create($data['date_of_birth']);
        if (!$date) {
            $errors['date_of_birth'] = 'Data e lindjes është e pavlefshme.';
        }
    }
}

if ($current['role'] === 'doctor') {
    if (!isset($data['specialization']) || trim($data['specialization']) === '') {
        $errors['specialization'] = 'Specializimi është i detyrueshëm.';
    }
}

// If we have any validation errors, return them
if (!empty($errors)) {
    http_response_code(422); // Unprocessable Entity
    echo json_encode(['errors' => $errors]);
    exit;
}

// ---- Update users table ----

$stmt = $mysqli->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
$stmt->bind_param('ssi', $data['name'], $data['email'], $current['id']);
if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to update user']);
    exit;
}

// ---- Update patient or doctor profile ----

if ($current['role'] === 'patient') {
    $patient = $patientModel->findByUserId((int)$current['id']);

    $dob    = $data['date_of_birth'] ?? null;
    $gender = $data['gender'] ?? null;

    if ($patient) {
        $stmt2 = $mysqli->prepare('UPDATE patients SET date_of_birth = ?, gender = ? WHERE user_id = ?');
        $stmt2->bind_param('ssi', $dob, $gender, $current['id']);
        $stmt2->execute();
    } else {
        $stmt2 = $mysqli->prepare('INSERT INTO patients (user_id, date_of_birth, gender) VALUES (?, ?, ?)');
        $stmt2->bind_param('iss', $current['id'], $dob, $gender);
        $stmt2->execute();
    }
}

if ($current['role'] === 'doctor') {
    $doctor = $doctorModel->findByUserId((int)$current['id']);
    $spec = trim($data['specialization']);

    if ($doctor) {
        $stmt3 = $mysqli->prepare('UPDATE doctors SET specialization = ? WHERE user_id = ?');
        $stmt3->bind_param('si', $spec, $current['id']);
        $stmt3->execute();
    } else {
        $stmt3 = $mysqli->prepare('INSERT INTO doctors (user_id, specialization) VALUES (?, ?)');
        $stmt3->bind_param('is', $current['id'], $spec);
        $stmt3->execute();
    }
}

echo json_encode(['success' => 'Profile updated']);
?>