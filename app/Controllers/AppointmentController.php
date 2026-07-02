<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/Appointment.php';

use App\Models\Appointment;

class AppointmentController {
    private $appointmentModel;

    public function __construct($mysqli) {
        $this->appointmentModel = new Appointment($mysqli);
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        $required = ['patient_id','appointment_date','appointment_time'];
        foreach ($required as $f) {
            if (!isset($data[$f])) {
                http_response_code(400);
                echo json_encode(['error' => 'Missing field: '.$f]);
                return;
            }
        }

        $doctor_id = $data['doctor_id'] ?? null;
        if ($this->appointmentModel->create($data['patient_id'], $doctor_id, $data['appointment_date'], $data['appointment_time'])) {
            http_response_code(201);
            echo json_encode(['success' => 'Appointment created']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create appointment']);
        }
    }

    public function updateStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing status']);
            return;
        }
        if ($this->appointmentModel->updateStatus($id, $data['status'])) {
            echo json_encode(['success' => 'Status updated']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Update failed']);
        }
    }

    public function getByPatient($patient_id) {
        $res = $this->appointmentModel->getByPatientId($patient_id);
        echo json_encode($res);
    }

    public function getByDoctor($doctor_id) {
        $res = $this->appointmentModel->getByDoctorId($doctor_id);
        echo json_encode($res);
    }
}
?>