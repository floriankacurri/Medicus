<?php
namespace App\Models;

class HealthCard {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getByPatientId($patient_id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM health_cards WHERE patient_id = ? LIMIT 1');
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function createOrUpdateByPatientId($patient_id, $medical_history, $allergies, $notes) {
        $existing = $this->getByPatientId($patient_id);
        if ($existing) {
            $stmt = $this->mysqli->prepare('UPDATE health_cards SET medical_history = ?, allergies = ?, notes = ?, updated_at = NOW() WHERE patient_id = ?');
            $stmt->bind_param('sssi', $medical_history, $allergies, $notes, $patient_id);
            return $stmt->execute();
        } else {
            $stmt = $this->mysqli->prepare('INSERT INTO health_cards (patient_id, medical_history, allergies, notes) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('isss', $patient_id, $medical_history, $allergies, $notes);
            return $stmt->execute();
        }
    }
}
?>