<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Controllers/HealthCardController.php';

use App\Controllers\HealthCardController;

$controller = new HealthCardController($mysqli);
$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

// Only doctors (or admin) can update a patient's health card for a specified user_id
$target_user_id = isset($data['user_id']) ? (int)$data['user_id'] : (int)$current['id'];

if ($target_user_id !== (int)$current['id'] && $current['role'] !== 'doctor' && $current['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$controller->updateForUser($target_user_id);
?>