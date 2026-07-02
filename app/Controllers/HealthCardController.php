<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/HealthCard.php';
require_once __DIR__ . '/../Models/Patient.php';

use App\Models\HealthCard;
use App\Models\Patient;

class HealthCardController {
    private $hcModel;
    private $patientModel;

    public function __construct($mysqli) {
        $this->hcModel = new HealthCard($mysqli);
        $this->patientModel = new Patient($mysqli);
    }

    // Accept a user_id (from API) and map to patient_id
    public function getForUser($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $patient = $this->patientModel->findByUserId((int)$user_id);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['error' => 'Patient not found']);
            return;
        }

        $card = $this->hcModel->getByPatientId((int)$patient['id']);
        if ($card) {
            echo json_encode($card);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Health card not found']);
        }
    }

    public function updateForUser($user_id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }

        $data = json_decode(file_get_contents('php://input'), true);
        $medical_history = $data['medical_history'] ?? null;
        $allergies = $data['allergies'] ?? null;
        $notes = $data['notes'] ?? null;

        $patient = $this->patientModel->findByUserId((int)$user_id);
        if (!$patient) {
            http_response_code(404);
            echo json_encode(['error' => 'Patient not found']);
            return;
        }

        if ($this->hcModel->createOrUpdateByPatientId((int)$patient['id'], $medical_history, $allergies, $notes)) {
            echo json_encode(['success' => 'Health card saved']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save health card']);
        }
    }
}
?>