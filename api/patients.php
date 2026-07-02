<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/User.php';

use App\Models\Patient;
use App\Models\User;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Only doctors may view patient list
if ($current['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

// Join users and patients (moved to model)
$patientModel = new Patient($mysqli);
$rows = $patientModel->getAllWithUser();
echo json_encode($rows);
?>