<?php
namespace App\Models;

class Patient {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function findByUserId($user_id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM patients WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAllWithUser() {
        $stmt = $this->mysqli->prepare('SELECT patients.id as patient_id, users.id as user_id, users.name, patients.date_of_birth, patients.gender FROM patients JOIN users ON patients.user_id = users.id ORDER BY users.name ASC');
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function create($user_id, $date_of_birth = null, $gender = null) {
        $stmt = $this->mysqli->prepare('INSERT INTO patients (user_id, date_of_birth, gender) VALUES (?, ?, ?)');
        $stmt->bind_param('iss', $user_id, $date_of_birth, $gender);
        return $stmt->execute();
    }

    public function findById($id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM patients WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
?>