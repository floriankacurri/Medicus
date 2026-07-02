<?php
namespace App\Models;

class User {
    private $mysqli;
    
    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }
    
    public function create($name, $email, $password_hash, $role = 'patient') {
        $stmt = $this->mysqli->prepare('INSERT INTO users (name, email, `password`, role, created_at) VALUES (?, ?, ?, ?, NOW())');
        $stmt->bind_param('ssss', $name, $email, $password_hash, $role);
        return $stmt->execute();
    }
    
    public function findByEmail($email) {
        $stmt = $this->mysqli->prepare('SELECT id, name, email, `password`, role FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }
    
    public function findById($id) {
        $stmt = $this->mysqli->prepare('SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        return $res->fetch_assoc();
    }
    
    public function getAll($role = null) {
        if ($role) {
            $stmt = $this->mysqli->prepare('SELECT id, name, email, role FROM users WHERE role = ? ORDER BY created_at DESC');
            $stmt->bind_param('s', $role);
        } else {
            $stmt = $this->mysqli->prepare('SELECT id, name, email, role FROM users ORDER BY created_at DESC');
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
