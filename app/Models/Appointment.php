<?php
namespace App\Models;

require_once __DIR__ . '/DoctorSchedule.php';

use App\Models\DoctorSchedule;

class Appointment {
    private $mysqli;
    // default duration for an appointment in minutes
    const DEFAULT_DURATION_MIN = 30;
    // store the last insert_id
    private $lastInsertId = null;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getLastInsertId() {
        return $this->lastInsertId;
    }

    public function create($patient_id, $doctor_id, $date, $time, $duration_minutes = self::DEFAULT_DURATION_MIN, $status = 'pending', $reason = null) {
        $stmt = $this->mysqli->prepare('INSERT INTO appointments (patient_id, doctor_id, appointment_date, appointment_time, duration_minutes, status, reason, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');
        if (!$stmt) {
            error_log('Appointment::create prepare failed: ' . $this->mysqli->error);
            return false;
        }
        $stmt->bind_param('iississ', $patient_id, $doctor_id, $date, $time, $duration_minutes, $status, $reason);
        $result = $stmt->execute();
        if ($result) {
            $this->lastInsertId = $this->mysqli->insert_id;
        } else {
            error_log('Appointment::create execute failed: ' . $stmt->error);
        }
        $stmt->close();
        return $result;
    }

    public function findById($id) {
        $stmt = $this->mysqli->prepare('SELECT * FROM appointments WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByPatientId($patient_id) {
        $stmt = $this->mysqli->prepare('SELECT a.*, u.name doctor_name FROM appointments a JOIN doctors d ON d.id = a.doctor_id JOIN users u ON u.id = d.user_id WHERE patient_id = ? ORDER BY appointment_date DESC');
        $stmt->bind_param('i', $patient_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByDoctorId($doctor_id) {
        $stmt = $this->mysqli->prepare('SELECT a.*, u.name patient_name FROM appointments a JOIN patients p ON p.id = a.patient_id JOIN users u ON u.id = p.user_id WHERE a.doctor_id = ? ORDER BY appointment_date DESC');
        $stmt->bind_param('i', $doctor_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getPending() {
        $res = $this->mysqli->query("SELECT a.*, u.name patient_name FROM appointments a JOIN patients p ON p.id = a.patient_id JOIN users u ON u.id = p.user_id WHERE status = 'pending' ORDER BY appointment_date ASC, appointment_time ASC");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function updateStatus($id, $status) {
        $stmt = $this->mysqli->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        $stmt->bind_param('si', $status, $id);
        return $stmt->execute();
    }

    public function assignToDoctor($id, $doctor_id) {
        $stmt = $this->mysqli->prepare('UPDATE appointments SET doctor_id = ? WHERE id = ?');
        $stmt->bind_param('ii', $doctor_id, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->mysqli->prepare('DELETE FROM appointments WHERE id = ?');
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }

    // Check if a doctor is available for a proposed appointment time (no overlapping appointments)
    // $doctor_id: doctor's internal id in doctors table
    // $date: 'YYYY-MM-DD'
    // $time: 'HH:MM:SS' (or 'HH:MM')
    // $excludeId: optional appointment id to exclude (useful when rescheduling an existing appointment)
    public function isAvailable($doctor_id, $date, $time, $excludeId = null, $durationMin = null) {
        $durationMin = $durationMin ?: self::DEFAULT_DURATION_MIN;
        // normalize time to HH:MM:SS
        $start = date('H:i:s', strtotime($time));
        // compute end time by adding duration minutes
        $endTimestamp = strtotime($start) + ($durationMin * 60);
        $end = date('H:i:s', $endTimestamp);
        $durationSql = sprintf('00:%02d:00', (int)$durationMin);

        // If doctor schedule exists, ensure the full interval fits within a schedule interval
        if ($doctor_id) {
            try {
                $ds = new \App\Models\DoctorSchedule($this->mysqli);
                $weekday = intval(date('w', strtotime($date))); // 0 (Sunday) to 6 (Saturday)
                if (!$ds->isIntervalWithinSchedule((int)$doctor_id, $weekday, $start, $end)) {
                    // not fully within doctor's schedule
                    return false;
                }
            } catch (\Exception $e) {
                // if schedule check fails, we continue to overlap check (fail open)
            }
        }

        if ($excludeId) {
            $sql = "SELECT appointment_time, duration_minutes FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND id != ? AND status IN ('pending','approved')";
            $stmt = $this->mysqli->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('isi', $doctor_id, $date, $excludeId);
        } else {
            $sql = "SELECT appointment_time, duration_minutes FROM appointments WHERE doctor_id = ? AND appointment_date = ? AND status IN ('pending','approved')";
            $stmt = $this->mysqli->prepare($sql);
            if (!$stmt) return false;
            $stmt->bind_param('is', $doctor_id, $date);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        if (!$res) return false;
        while ($row = $res->fetch_assoc()) {
            $existStart = date('H:i:s', strtotime($row['appointment_time']));
            $existDuration = isset($row['duration_minutes']) ? (int)$row['duration_minutes'] : self::DEFAULT_DURATION_MIN;
            $existEnd = date('H:i:s', strtotime($existStart) + ($existDuration * 60));
            // overlap check: newStart < existEnd && existStart < newEnd
            if (strtotime($start) < strtotime($existEnd) && strtotime($existStart) < strtotime($end)) {
                return false;
            }
        }

        return true;
    }

    // Update appointment date and time (used for rescheduling)
    public function updateDateTime($id, $date, $time, $duration_minutes = null) {
        error_log("updateDateTime called with: " . json_encode([
            'id' => $id, 'date' => $date, 'time' => $time, 'duration' => $duration_minutes
        ]));
        if ($duration_minutes === null) {
            $stmt = $this->mysqli->prepare('UPDATE appointments SET appointment_date = ?, appointment_time = ? WHERE id = ?');
            $stmt->bind_param('ssi', $date, $time, $id);
        } else {
            $stmt = $this->mysqli->prepare('UPDATE appointments SET appointment_date = ?, appointment_time = ?, duration_minutes = ? WHERE id = ?');
            $stmt->bind_param('ssii', $date, $time, $duration_minutes, $id);
        }

        if (!$stmt->execute()) {
            error_log("Update failed: " . $stmt->error);
            return false;
        } else {
            error_log("Update succeeded for id: $id");
            return true;
        }
    }
}
?>