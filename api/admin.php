<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../app/Config/config.php';
require_once __DIR__ . '/../app/Helpers/auth.php';
require_once __DIR__ . '/../app/Models/User.php';
require_once __DIR__ . '/../app/Models/Doctor.php';
require_once __DIR__ . '/../app/Models/Patient.php';
require_once __DIR__ . '/../app/Models/Appointment.php';
require_once __DIR__ . '/../app/Models/HealthCard.php';

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\HealthCard;

$current = current_user();
if (!$current) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
if (($current['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? null;
$input = json_decode(file_get_contents('php://input'), true) ?: [];

$userModel = new User($mysqli);
$doctorModel = new Doctor($mysqli);
$patientModel = new Patient($mysqli);
$appModel = new Appointment($mysqli);
$hcModel = new HealthCard($mysqli);

function json_ok($data = []) { echo json_encode($data); }
function json_err($msg, $code = 400) { http_response_code($code); echo json_encode(['error'=>$msg]); }

switch ($action) {
    // USERS
    case 'list_users':
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per = max(5, min(100, (int)($_GET['per'] ?? 20)));
        $offset = ($page - 1) * $per;
        $search = trim($_GET['search'] ?? '');

        if ($search !== '') {
            $stmt = $mysqli->prepare("SELECT id, name, email, role, created_at FROM users WHERE name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%') ORDER BY created_at DESC LIMIT ?, ?");
            $stmt->bind_param('ssii', $search, $search, $offset, $per);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $cntStmt = $mysqli->prepare("SELECT COUNT(*) as c FROM users WHERE name LIKE CONCAT('%', ?, '%') OR email LIKE CONCAT('%', ?, '%')");
            $cntStmt->bind_param('ss', $search, $search);
            $cntStmt->execute();
            $total = $cntStmt->get_result()->fetch_assoc()['c'] ?? 0;
        } else {
            $stmt = $mysqli->prepare('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC LIMIT ?, ?');
            $stmt->bind_param('ii', $offset, $per);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $res = $mysqli->query('SELECT COUNT(*) as c FROM users');
            $total = $res ? (int)$res->fetch_assoc()['c'] : 0;
        }

        json_ok(['rows'=>$rows, 'total'=>$total, 'page'=>$page, 'per'=>$per]);
        break;

    case 'get_user':
        $id = (int)($_GET['user_id'] ?? 0);
        if (!$id) return json_err('Missing user_id');
        $row = $userModel->findById($id);
        if (!$row) return json_err('User not found', 404);
        json_ok($row);
        break;

    case 'create_user':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $password = $input['password'] ?? null;
        $role = $input['role'] ?? 'patient';
        if (!$name || !$email || !$password) return json_err('Missing fields');
        if ($userModel->findByEmail($email)) return json_err('Email already exists', 409);

        // Field validations
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return json_err('Invalid email format');
        if (!in_array($role, ['admin','doctor','patient'])) return json_err('Invalid role');

        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($userModel->create($name, $email, $hash, $role)) json_ok(['success'=>true]); else json_err('Create failed', 500);
        break;

    case 'update_user':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $id = (int)($input['id'] ?? 0);
        if (!$id) return json_err('Missing id');
        $name = trim($input['name'] ?? '');
        $email = trim($input['email'] ?? '');
        $role = $input['role'] ?? null;
        $active = isset($input['active']) ? (int)$input['active'] : null;

        $fields = [];
        $types = '';
        $vals = [];

        // Name validation
        if ($name !== '') { $fields[]='name=?'; $types.='s'; $vals[]=$name; }
        else { return json_err('Name cannot be empty'); }

        // Email validation and uniqueness check
        if ($email === '') {
            return json_err('Email cannot be empty');
        } else {
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return json_err('Invalid email format');
            $existing = $userModel->findByEmail($email);
            if ($existing && (int)$existing['id'] !== $id) {
                return json_err('Email already exists');
            }
        }
        $fields[]='email=?'; $types.='s'; $vals[]=$email; 
        
        // Role validation
        if ($role === null) return json_err('Role is required');
        if (!in_array($role, ['admin','doctor','patient'])) return json_err('Invalid role'); 
        $fields[]='role=?'; $types.='s'; $vals[]=$role; 

        if ($active !== null) { $fields[]='active=?'; $types.='i'; $vals[]=$active; }
        else { return json_err('Active status is required'); }

        if (empty($fields)) return json_err('No fields to update');
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $types .= 'i'; 
        $vals[] = $id;
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param($types, ...$vals);
        if ($stmt->execute()) {
            json_ok(['success'=>true]); 
        } else {
            json_err('Update failed', 500);
        }
        break;

    case 'delete_user':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $id = (int)($input['user_id'] ?? 0);
        if (!$id) return json_err('Missing user_id');
        // prevent deleting yourself
        if ($id === (int)$current['id']) return json_err('Cannot delete current admin', 403);
        $stmt = $mysqli->prepare('DELETE FROM users WHERE id = ?');
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) json_ok(['success'=>true]); else json_err('Delete failed', 500);
        break;

    // DOCTORS
    case 'list_doctors':
        $stmt = $mysqli->prepare('SELECT d.id, d.user_id, d.specialization, u.name, u.email FROM doctors d LEFT JOIN users u ON d.user_id = u.id ORDER BY u.name');
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        json_ok($rows);
        break;

    case 'get_doctor':
        $id = intval($_GET['id'] ?? 0);
        if (!$id) { echo json_encode(['error' => 'Missing id']); exit; }

        $stmt = $mysqli->prepare("
            SELECT d.id, d.user_id, d.specialization, u.name, u.email
            FROM doctors d
            JOIN users u ON u.id = d.user_id
            WHERE d.id = ?
        ");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $doc = $stmt->get_result()->fetch_assoc();

        echo json_encode($doc ?: ['error' => 'Not found']);        
        break;

    case 'update_doctor':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['doctor_id'])) {
            echo json_encode(['error' => 'Missing doctor_id']);
            exit;
        }

        $doctor_id = (int)$data['doctor_id'];

        // first get the user_id for this doctor
        $stmt1 = $mysqli->prepare("SELECT user_id FROM doctors WHERE id = ?");
        $stmt1->bind_param('i', $doctor_id);
        $stmt1->execute();
        $res1 = $stmt1->get_result()->fetch_assoc();
        if (!$res1 || !isset($res1['user_id'])) {
            echo json_encode(['error' => 'Doctor record not found']);
            exit;
        }
        $user_id = (int)$res1['user_id'];

        // now update the users table with name/email
        $stmt2 = $mysqli->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt2->bind_param(
            'ssi',
            $data['name'],
            $data['email'],
            $user_id
        );
        $ok1 = $stmt2->execute();

        // update doctors table with specialization
        $stmt3 = $mysqli->prepare("UPDATE doctors SET specialization = ? WHERE id = ?");
        $stmt3->bind_param('si', $data['specialization'], $doctor_id);
        $ok2 = $stmt3->execute();

        if ($ok1 && $ok2) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Update failed']);
        }
        break;

    // Note: reservation-related endpoints were removed — appointment endpoints should be used instead.


    // PATIENTS
    case 'list_patients':
        $stmt = $mysqli->prepare('SELECT p.id, p.user_id, u.name, u.email FROM patients p LEFT JOIN users u ON p.user_id = u.id ORDER BY u.name');
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        json_ok($rows);
        break;


    // APPOINTMENTS
   case 'list_appointments':
      $page = max(1, (int)($_GET['page'] ?? 1));
      $per  = max(5, min(200, (int)($_GET['per'] ?? 20)));
      $offset = ($page - 1) * $per;

      $doctor = isset($_GET['doctor_id']) ? (int)$_GET['doctor_id'] : null;
      $status = $_GET['status'] ?? null;
      $start  = $_GET['start'] ?? null;
      $end    = $_GET['end'] ?? null;

      $where = [];
      $types = '';
      $params = [];

      if ($doctor) {
          $where[] = 'doctor_id = ?';
          $types .= 'i';
          $params[] = $doctor;
      }
      if ($status) {
          $where[] = 'status = ?';
          $types .= 's';
          $params[] = $status;
      }
      if ($start) {
          $where[] = 'appointment_date >= ?';
          $types .= 's';
          $params[] = $start;
      }
      if ($end) {
          $where[] = 'appointment_date <= ?';
          $types .= 's';
          $params[] = $end;
      }

      $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

      /* ---------- MAIN QUERY ---------- */
      $sql = "
          SELECT *
          FROM appointments
          $whereSql
          ORDER BY appointment_date DESC, appointment_time DESC
          LIMIT ?, ?
      ";

      $stmt = $mysqli->prepare($sql);

      $mainTypes = $types . 'ii';
      $mainParams = array_merge($params, [$offset, $per]);

      $stmt->bind_param($mainTypes, ...$mainParams);
      $stmt->execute();

      $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

      /* ---------- COUNT QUERY ---------- */
      $cntSql = "SELECT COUNT(*) AS c FROM appointments $whereSql";
      $cntStmt = $mysqli->prepare($cntSql);

      if ($types !== '') {
          // IMPORTANT: use ONLY filter params
          $cntStmt->bind_param($types, ...$params);
      }

      $cntStmt->execute();
      $total = (int)$cntStmt->get_result()->fetch_assoc()['c'];

      json_ok([
          'rows'  => $rows,
          'page'  => $page,
          'per'   => $per,
          'total' => $total
      ]);
      break;

    case 'assign_appointment_doctor':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $id = (int)($input['id'] ?? 0);
        $doktori_id = (int)($input['doktori_id'] ?? 0);
        if (!$id || !$doktori_id) return json_err('Missing id or doktori_id');
        if ($appModel->assignToDoctor($id, $doktori_id)) json_ok(['success'=>true]); else json_err('Assign failed', 500);
        break;

    case 'reschedule_appointment':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $id = (int)($input['id'] ?? 0);
        $date = $input['date'] ?? null;
        $time = $input['time'] ?? null;
        if (!$id || !$date || !$time) return json_err('Missing id, date, or time');
        // Validate date and time formats
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) return json_err('Invalid date format');
        if (!preg_match('/^\d{2}:\d{2}$/', $time)) return json_err('Invalid time format');

        // Check if appointment exists
        $appt = $appModel->findById($id);
        if (!$appt) return json_err('Appointment not found', 404);
        // Check if doctor is available at the new time (if doctor assigned)
        $result = $appModel->isAvailable((int)$appt['doctor_id'], $date, $time, $id);
        if (!$result) return json_err('Doctor not available at the requested time', 409);
        // Perform the update
        $result = $appModel->updateDateTime($id, $date, $time);
        if ($result === true) {
            json_ok(['success' => true]);
        } else {
            json_err($result ?? 'Reschedule failed', 400);
        }        

    case 'delete_appointment':
        if ($method !== 'POST') return json_err('Method not allowed', 405);
        $id = (int)($input['id'] ?? 0);
        if (!$id) return json_err('Missing id');
        if ($appModel->delete($id)) json_ok(['success'=>true]); else json_err('Delete failed', 500);
        break;

    // HEALTHCARD already has get_healthcard above

    case 'get_healthcard':
        $id = $_GET['id'] ?? null;
        if (!$id) { echo json_encode(['error'=>'Missing id']); exit; }

        $stmt = $mysqli->prepare(
            "SELECT hc.id, p.id AS patient_id, u.name AS patient_name,
                    hc.medical_history, hc.allergies, hc.notes, hc.updated_at
            FROM health_cards hc
            JOIN patients p ON p.id = hc.patient_id
            JOIN users u ON u.id = p.user_id
            WHERE hc.id = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        echo json_encode($row ?: ['error'=>'Not found']);
        break;

    case 'list_healthcards':
        // get params
        $page    = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $per     = isset($_GET['per'])  ? (int)$_GET['per']  : 20;
        $search  = isset($_GET['search']) ? trim($_GET['search']) : '';

        $offset = ($page - 1) * $per;

        // build base query
        $sql = "SELECT hc.id, hc.patient_id, u.name AS patient_name, hc.medical_history, hc.allergies, hc.notes, hc.updated_at
                FROM health_cards hc
                JOIN patients p ON p.id = hc.patient_id
                JOIN users u ON u.id = p.user_id";

        $params = [];
        if ($search !== '') {
            $sql .= " WHERE u.name LIKE ?";
            $params[] = '%' . $search . '%';
        }

        // count total
        $countSql = "SELECT COUNT(*) FROM (" . $sql . ") AS tmp";
        $stmt = $mysqli->prepare($countSql);

        // bind only if there are params
        if (count($params) > 0) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $total = $stmt->get_result()->fetch_row()[0] ?? 0;

        // add pagination
        $sql .= " ORDER BY hc.updated_at DESC LIMIT ? OFFSET ?";
        $params[] = $per;
        $params[] = $offset;

        // build types string
        $types = str_repeat('s', count($params) - 2) . 'ii';
        $stmt2 = $mysqli->prepare($sql);
        $stmt2->bind_param($types, ...$params);
        $stmt2->execute();
        $result = $stmt2->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }

        echo json_encode([
            'rows'  => $rows,
            'page'  => $page,
            'per'   => $per,
            'total' => $total
        ]);
        break;

    case 'update_healthcard':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['id'])) {
            echo json_encode(['error'=>'Missing id']); exit;
        }

        $stmt = $mysqli->prepare(
            "UPDATE health_cards
            SET medical_history = ?, allergies = ?, notes = ?
            WHERE id = ?"
        );
        $stmt->bind_param('sssi',
            $data['medical_history'],
            $data['allergies'],
            $data['notes'],
            $data['id']
        );

        if ($stmt->execute()) {
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['error'=>'Update failed']);
        }
        break;

    
    case 'create_user_with_role': 
        if ($method !== 'POST') {
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
            exit;
        }
        $data = json_decode(file_get_contents('php://input'), true);

        // basic validation
        if (!isset($data['name'], $data['email'], $data['password'], $data['role'])) {
            echo json_encode(['error'=>'Missing required fields']);
            exit;
        }

        // password hashing
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);

        // insert into users
        $stmt = $mysqli->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss',
            $data['name'],
            $data['email'],
            $hash,
            $data['role']
        );
        if (!$stmt->execute()) {
            echo json_encode(['error' => 'Failed creating user']);
            exit;
        }

        // get new user id
        $user_id = $mysqli->insert_id;

        // insert into role specific table if needed
        if ($data['role'] === 'patient') {
            $stmt2 = $mysqli->prepare("INSERT INTO patients (user_id, date_of_birth, gender) VALUES (?, ?, ?)");
            $stmt2->bind_param('iss', $user_id, $data['date_of_birth'], $data['gender']);
            $stmt2->execute();
        } elseif ($data['role'] === 'doctor') {
            $stmt3 = $mysqli->prepare("INSERT INTO doctors (user_id, specialization) VALUES (?, ?)");
            $stmt3->bind_param('is', $user_id, $data['specialization']);
            $stmt3->execute();
        }

        echo json_encode(['success'=>true]);
        break;


    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unknown action']);
}

?>
