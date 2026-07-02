<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/Doctor.php';

use App\Models\Doctor;

class DoctorController {
    private $doctorModel;

    public function __construct($mysqli) {
        $this->doctorModel = new Doctor($mysqli);
    }

    public function listDoctors() {
        $doctors = $this->doctorModel->getAll();
        echo json_encode($doctors);
    }
}
?>