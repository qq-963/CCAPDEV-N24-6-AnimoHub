<?php
class EnlistmentModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    // session has user_id but enlistment uses student_id
    function getStudentId($userId) {
        $st = $this->db->prepare("SELECT student_id FROM student WHERE user_id = ?");
        $st->execute([$userId]);
        return $st->fetchColumn();
    }

    function getEnlisted($studentId) {
        $st = $this->db->prepare("SELECT s.section_id, s.section_code, s.time_slot,
                c.course_id, c.code, c.name, c.units
                FROM enlistment e
                JOIN section s ON s.section_id = e.section_id
                JOIN course c ON c.course_id = s.course_id
                WHERE e.student_id = ?
                ORDER BY c.code");
        $st->execute([$studentId]);
        $rows = $st->fetchAll();

        // add the days like MH or TF
        foreach ($rows as $i => $row) {
            $st2 = $this->db->prepare("SELECT day FROM section_schedule WHERE section_id = ? ORDER BY day");
            $st2->execute([$row['section_id']]);
            $days = "";
            foreach ($st2->fetchAll() as $d) {
                $days .= $d['day'];
            }
            $rows[$i]['days'] = $days;
        }
        return $rows;
    }

    function totalUnits($studentId) {
        $total = 0;
        foreach ($this->getEnlisted($studentId) as $e) {
            $total += $e['units'];
        }
        return $total;
    }

    // already enlisted in this course?
    function hasCourse($studentId, $courseId) {
        foreach ($this->getEnlisted($studentId) as $e) {
            if ($e['course_id'] == $courseId) {
                return true;
            }
        }
        return false;
    }

    // same time and same day as a section the student already has?
    function hasConflict($studentId, $section) {
        foreach ($this->getEnlisted($studentId) as $e) {
            if ($e['time_slot'] == $section['time_slot']) {
                foreach ($section['days'] as $d) {
                    if (strpos($e['days'], $d['day']) !== false) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    function enlist($studentId, $sectionId) {
        $this->db->beginTransaction();

        $st = $this->db->prepare("UPDATE section SET slots = slots - 1 WHERE section_id = ? AND slots > 0");
        $st->execute([$sectionId]);
        if ($st->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }

        $st = $this->db->prepare("INSERT INTO enlistment (student_id, section_id, date_enlisted) VALUES (?, ?, CURDATE())");
        $st->execute([$studentId, $sectionId]);

        $this->db->commit();
        return true;
    }

    function drop($studentId, $sectionId) {
        $this->db->beginTransaction();

        $st = $this->db->prepare("DELETE FROM enlistment WHERE student_id = ? AND section_id = ?");
        $st->execute([$studentId, $sectionId]);
        if ($st->rowCount() == 0) {
            $this->db->rollBack();
            return false;
        }

        $st = $this->db->prepare("UPDATE section SET slots = slots + 1 WHERE section_id = ?");
        $st->execute([$sectionId]);

        $this->db->commit();
        return true;
    }

    // one row per day of each enlisted section
    function getSchedule($studentId) {
        $st = $this->db->prepare("SELECT ss.day, ss.mode, s.time_slot, s.section_code,
                c.code, r.room_code, u.name AS prof
                FROM enlistment e
                JOIN section s ON s.section_id = e.section_id
                JOIN course c ON c.course_id = s.course_id
                JOIN room r ON r.room_id = s.room_id
                JOIN professor p ON p.professor_id = s.professor_id
                JOIN `user` u ON u.user_id = p.user_id
                JOIN section_schedule ss ON ss.section_id = s.section_id
                WHERE e.student_id = ?");
        $st->execute([$studentId]);
        return $st->fetchAll();
    }
}
