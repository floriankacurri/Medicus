<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Helpers/auth.php';

use App\Models\Appointment;

class AdminController {
    private $appointmentModel;

    public function __construct($mysqli) {
        // Admin CRUD for schedule is handled via Appointment model and api/admin.php
        $this->appointmentModel = new Appointment($mysqli);
    }

    // Render simple admin login page
    public function login() {
        require __DIR__ . '/../../app/Views/admin/login.php';
    }

    // Redirect to the full admin dashboard (appointments and management live there)
    public function reservations() {
        require_role('admin');
        // Keep route for backward compatibility but forward to dashboard
        header('Location: /Medicus/admin/dashboard');
        exit;
    }
}
?>
