<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Models/Doctor.php';

use App\Models\Doctor;

$model = new Doctor($mysqli);
$list = $model->getAllWithUser();
echo json_encode(array_map(function ($row) {
    return [
        'id' => (int)$row['id'],
        'name' => $row['name'] ?? 'Dr. Unknown',
        'specialization' => $row['specialization'] ?? 'General',
    ];
}, $list));
