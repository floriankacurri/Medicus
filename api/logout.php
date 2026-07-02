<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Controllers/AuthController.php';

use App\Controllers\AuthController;

$controller = new AuthController($mysqli);
$controller->logout();
?>
