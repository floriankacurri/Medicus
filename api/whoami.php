<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';

$u = current_user();
if (!$u) { http_response_code(401); echo json_encode(['error' => 'unauthenticated']); exit; }
// return user minimal info
echo json_encode(['id' => $u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']]);
?>
