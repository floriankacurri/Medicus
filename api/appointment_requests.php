<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/Appointment.php';

use App\Models\Appointment;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($current['role'] !== 'doctor') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$appointmentModel = new Appointment($mysqli);
$rows = $appointmentModel->getPending();
echo json_encode($rows);
?>