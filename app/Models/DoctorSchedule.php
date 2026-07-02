<?php
namespace App\Models;

class DoctorSchedule {
    private $mysqli;

    public function __construct($mysqli) {
        $this->mysqli = $mysqli;
    }

    public function getByDoctorId($doctor_id) {
        try {
            $stmt = $this->mysqli->prepare('SELECT * FROM doctor_schedules WHERE doctor_id = ? ORDER BY day_of_week, start_time');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('i', $doctor_id);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:getByDoctorId] DB error: ' . $e->getMessage());
            // If table doesn't exist (error 1146) or other DB issues, return empty list to avoid fatal errors in views
            return [];
        }
    }

    public function addInterval($doctor_id, $day_of_week, $start_time, $end_time) {
        try {
            $stmt = $this->mysqli->prepare('INSERT INTO doctor_schedules (doctor_id, day_of_week, start_time, end_time, created_at) VALUES (?, ?, ?, ?, NOW())');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('iiss', $doctor_id, $day_of_week, $start_time, $end_time);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:addInterval] DB error: ' . $e->getMessage() . '; Ensure doctor_schedules table exists and migrations were applied.');
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->mysqli->prepare('DELETE FROM doctor_schedules WHERE id = ?');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('i', $id);
            return $stmt->execute();
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:delete] DB error: ' . $e->getMessage());
            return false;
        }
    }

    // Check if a given time (HH:MM:SS) on a weekday (0-6) is within any schedule interval for the doctor
    public function isWithinSchedule($doctor_id, $weekday, $time) {
        try {
            $stmt = $this->mysqli->prepare('SELECT COUNT(*) as c FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND start_time <= ? AND end_time > ?');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('iiss', $doctor_id, $weekday, $time, $time);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            return intval($row['c']) > 0;
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:isWithinSchedule] DB error: ' . $e->getMessage());
            // If table missing, treat as no schedule (i.e., unavailable) to be safe
            return false;
        }
    }

    // New: return intervals for a specific doctor and weekday
    public function getIntervalsForDay($doctor_id, $weekday) {
        try {
            $stmt = $this->mysqli->prepare('SELECT id, doctor_id, day_of_week, start_time, end_time FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? ORDER BY start_time');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('ii', $doctor_id, $weekday);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:getIntervalsForDay] DB error: ' . $e->getMessage());
            return [];
        }
    }

    // New: check whether a full [start, end) interval (HH:MM:SS) is contained within any single schedule interval
    public function isIntervalWithinSchedule($doctor_id, $weekday, $start_time, $end_time) {
        try {
            // We want an interval where start_time >= sched.start_time and end_time <= sched.end_time
            $stmt = $this->mysqli->prepare('SELECT COUNT(*) as c FROM doctor_schedules WHERE doctor_id = ? AND day_of_week = ? AND start_time <= ? AND end_time >= ?');
            if (!$stmt) throw new \Exception($this->mysqli->error);
            $stmt->bind_param('iiss', $doctor_id, $weekday, $start_time, $end_time);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            return intval($row['c']) > 0;
        } catch (\Throwable $e) {
            error_log('[DoctorSchedule:isIntervalWithinSchedule] DB error: ' . $e->getMessage());
            // if DB missing or broken, be conservative and return false
            return false;
        }
    }
}
?>
