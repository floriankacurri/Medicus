<?php
namespace App\Models;

class Doctor {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function findByUserId($user_id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM doctors WHERE user_id = ? LIMIT 1');
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($user_id, $specialization = null) {
        $stmt = $this->mysqli->prepare('INSERT INTO doctors (user_id, specialization) VALUES (?, ?)');
        $stmt->bind_param('is', $user_id, $specialization);
        return $stmt->execute();
    }

    public function findById($id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM doctors WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAll() {
        $res = $this->mysqli->query('SELECT * FROM doctors ORDER BY created_at DESC');
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAllWithUser() {
        $sql = 'SELECT d.id, d.specialization, u.name FROM doctors d INNER JOIN users u ON d.user_id = u.id ORDER BY u.name';
        $res = $this->mysqli->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>