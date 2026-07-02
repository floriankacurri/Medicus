<?php
header('Content-Type: application/json; charset=utf-8');
// Stub: accepts contact form submission. Can later store in DB or send email.
$data = json_decode(file_get_contents('php://input'), true) ?: [];
$emri = isset($data['emri']) ? trim((string)$data['emri']) : '';
$email = isset($data['email']) ? trim((string)$data['email']) : '';
$mesazhi = isset($data['mesazhi']) ? trim((string)$data['mesazhi']) : '';
if ($emri === '' || $email === '' || $mesazhi === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Mungojnë fushat e nevojshme.']);
    exit;
}
echo json_encode(['success' => true, 'message' => 'Mesazhi u dërgua.']);
