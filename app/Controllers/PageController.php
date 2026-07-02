<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Helpers/auth.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Doctor.php';
require_once __DIR__ . '/../Models/Appointment.php';
require_once __DIR__ . '/../Models/HealthCard.php';

use App\Models\User;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\HealthCard;

class PageController {
    /** @var \mysqli */
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    // Admin dashboard with multi-feature overview & quick links
    public function adminDashboard() {
        require_role('admin');

        $userModel = new User($this->mysqli);
        $doctorModel = new Doctor($this->mysqli);
        $appointmentModel = new Appointment($this->mysqli);
        $hcModel = new HealthCard($this->mysqli);

        // Counts and recent items
        $totalUsers = count($userModel->getAll());
        $totalDoctors = count($doctorModel->getAll());
        $totalPatients = count($userModel->getAll('patient'));
        $pendingAppointments = $appointmentModel->getPending();
        // Use appointments instead of reservations for admin overview
        $res = $this->mysqli->query('SELECT * FROM appointments ORDER BY appointment_date DESC, appointment_time DESC LIMIT 10');
        $recentAppointments = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

        $currentUser = current_user();
        // Expose variables to the view
        $usersCount = $totalUsers;
        $doctorsCount = $totalDoctors;
        $patientsCount = $totalPatients;
        $pendingAppointmentsList = $pendingAppointments;
        $recentAppointmentsList = $recentAppointments;

        require __DIR__ . '/../../app/Views/admin/dashboard.php';
    }

    public function index() {
        $currentUser = current_user();
        // Serve the homepage from the MVC pages folder
        require __DIR__ . '/../../app/Views/pages/homepage.php';
    }

    public function homepage() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/homepage.php';
    }

    public function blog() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/blog.php';
    }

    public function deget() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/deget.php';
    }

    public function alergologji() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/alergologji.php';
    }

    public function deshmi() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/deshmi.php';
    }

    public function login() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/auth/login.php';
    }

    public function register() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/auth/register.php';
    }


    public function patientDashboard() {
        require_login();
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/patient/index.php';
    }

    public function doctorDashboard() {
        require_role('doctor');
        $currentUser = current_user();

        $doctorModel = new Doctor($this->mysqli);
        $appointmentModel = new Appointment($this->mysqli);
        // simple counts
        $patientsCount = 0;
        $pendingRequestsCount = 0;
        // attempt to get patients assigned to doctor if model supports it
        try {
            $patients = $this->mysqli->query("SELECT COUNT(*) as c FROM users u JOIN patients p ON u.id = p.user_id");
            $patientsCount = $patients ? intval($patients->fetch_assoc()['c']) : 0;
            $req = $this->mysqli->query("SELECT COUNT(*) as c FROM appointments WHERE status = 'pending'");
            $pendingRequestsCount = $req ? intval($req->fetch_assoc()['c']) : 0;
        } catch (\Exception $e) {
            $patientsCount = 0; $pendingRequestsCount = 0;
        }

        require __DIR__ . '/../../app/Views/doctor/index.php';
    }

    public function healthcard() {
        require_login();
        $currentUser = current_user();
        $is_doctor = ($currentUser['role'] ?? '') === 'doctor';
        require __DIR__ . '/../../app/Views/healthcard/index.php';
    }

    public function rezervoni() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/rezervoni.php';
    }

    public function sherbimet() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/sherbimet.php';
    }

    public function stafimjekesor() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/stafimjekesor.php';
    }

    public function tereja() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/tereja.php';
    }

    public function rrethnesh() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/rrethnesh.php';
    }

    public function kontakt() {
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/pages/kontakt.php';
    }

    public function profile() {
        require_login();
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/profile/index.php';
    }

    public function patientAppointments() {
        // require_role('patient');
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/patient/appointments.php';
    }

    public function doctorPatients() {
        require_role('doctor');
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/doctor/patients.php';
    }

    public function doctorRequests() {
        require_role('doctor');
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/doctor/requests.php';
    }

    public function doctorSchedule() {
        require_role('doctor');
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/doctor/schedule.php';
    }

    public function doctorAppointments() {
        require_role('doctor');
        $currentUser = current_user();
        require __DIR__ . '/../../app/Views/doctor/appointments.php';
    }
}
?>