<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Controllers/HealthCardController.php';

use App\Controllers\HealthCardController;

$controller = new HealthCardController($mysqli);

// If ?user_id= is provided and current user is doctor, allow fetching that patient's card
$requested = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
$current = current_user();

if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if ($requested) {
    if ($current['role'] !== 'doctor' && $current['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden']);
        exit;
    }
    $controller->getForUser($requested);
} else {
    $controller->getForUser((int)$current['id']);
}
?>