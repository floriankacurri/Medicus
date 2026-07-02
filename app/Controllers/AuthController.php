<?php
namespace App\Controllers;
require_once __DIR__ . '/../Config/config.php';
require_once __DIR__ . '/../Models/User.php';
require_once __DIR__ . '/../Models/Patient.php';
require_once __DIR__ . '/../Models/Doctor.php';
require_once __DIR__ . '/../Helpers/auth.php';

use App\Models\User;
use App\Models\Patient;
use App\Models\Doctor;

class AuthController {
    private $userModel;
    private $patientModel;
    private $doctorModel;
    
    public function __construct($mysqli) {
        $this->userModel = new User($mysqli);
        $this->patientModel = new Patient($mysqli);
        $this->doctorModel = new Doctor($mysqli);
    }
    
    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name'], $data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            return;
        }
        
        if ($this->userModel->findByEmail($data['email'])) {
            http_response_code(409);
            echo json_encode(['error' => 'Email already exists']);
            return;
        }
        
        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
        $role = $data['role'] ?? 'patient';
        
        if ($this->userModel->create($data['name'], $data['email'], $password_hash, $role)) {
            // Create corresponding patient/doctor record
            $user = $this->userModel->findByEmail($data['email']);
            if ($role === 'patient') {
                $this->patientModel->create($user['id']);
            } elseif ($role === 'doctor') {
                $this->doctorModel->create($user['id']);
            }

            http_response_code(201);
            echo json_encode(['success' => 'User registered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Registration failed']);
        }
    }
    
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['email'], $data['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing email or password']);
            return;
        }
        
        $user = $this->userModel->findByEmail($data['email']);
        
        if (!$user || !password_verify($data['password'], $user['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid email or password']);
            return;
        }
        
        // Use centralized auth helper to set session
        $u = login_user_from_row($user);
        http_response_code(200);
        echo json_encode(['success' => 'Login successful', 'user' => $u]);
    }
    
    public function logout() {
        logout();
        header('Location: /Medicus/');
        exit;
    }
}
?>
