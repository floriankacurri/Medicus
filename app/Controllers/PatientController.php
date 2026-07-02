<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/Patient.php';

use App\Models\Patient;

class PatientController {
    private $patientModel;

    public function __construct($mysqli) {
        $this->patientModel = new Patient($mysqli);
    }

    public function getProfile($user_id) {
        $profile = $this->patientModel->findByUserId($user_id);
        if ($profile) {
            echo json_encode($profile);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Patient not found']);
        }
    }
}
?>