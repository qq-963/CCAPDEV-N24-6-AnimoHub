<?php
class CourseModel extends Model {

    // every section with course info, days string and modes
    function sections() {
        $rows = $this->q("SELECT s.section_id, s.section_code AS sec, s.time_slot AS time, s.slots, s.max_slots AS max, c.course_id, c.code, c.name, c.units, r.room_code AS room, u.name AS prof FROM section s JOIN course c ON c.course_id = s.course_id JOIN room r ON r.room_id = s.room_id JOIN professor p ON p.professor_id = s.professor_id JOIN `user` u ON u.user_id = p.user_id ORDER BY c.course_id, s.section_id")->fetchAll();
        $list = [];
        foreach ($rows as $r) {
            $i = strrpos($r['prof'], ' ');
            $r['prof'] = substr($r['prof'], $i + 1) . ', ' . substr($r['prof'], 0, $i);
            $r['days'] = '';
            $r['modes'] = [];
            $list[$r['section_id']] = $r;
        }
        foreach ($this->q("SELECT * FROM section_schedule ORDER BY section_id, FIELD(day, 'M', 'T', 'W', 'H', 'F', 'S')")->fetchAll() as $s) {
            $list[$s['section_id']]['days'] .= $s['day'];
            $list[$s['section_id']]['modes'][] = $s['mode'];
        }
        return $list;
    }

    // courses with their sections inside
    function all() {
        $courses = [];
        foreach ($this->sections() as $s) {
            $id = $s['course_id'];
            if (!isset($courses[$id])) $courses[$id] = ['code' => $s['code'], 'name' => $s['name'], 'units' => $s['units'], 'sections' => []];
            $courses[$id]['sections'][] = $s;
        }
        return array_values($courses);
    }

    function enlisted($sid) {
        $ids = $this->q("SELECT section_id FROM enlistment WHERE student_id = ?", $sid)->fetchAll(PDO::FETCH_COLUMN);
        return array_values(array_filter($this->sections(), fn($s) => in_array($s['section_id'], $ids)));
    }

    // returns an error message, null if it worked
    function enlist($sid, $secId) {
        $all = $this->sections();
        if (!isset($all[$secId])) return 'Section not found';
        $f = $all[$secId];
        $mine = $this->enlisted($sid);

        foreach ($mine as $o) {
            if ($o['code'] == $f['code']) return 'You already enlisted ' . $f['code'];
        }
        if ($f['slots'] <= 0) return 'Section is full';
        if (array_sum(array_column($mine, 'units')) + $f['units'] > MAX_UNITS) return 'Max units reached (' . MAX_UNITS . ')';
        foreach ($mine as $o) {
            if ($o['time'] == $f['time'] && array_intersect(str_split($o['days']), str_split($f['days']))) return 'Schedule conflict with ' . $o['code'];
        }

        $this->db->beginTransaction();
        if (!$this->q("UPDATE section SET slots = slots - 1 WHERE section_id = ? AND slots > 0", $secId)->rowCount()) {
            $this->db->rollBack();
            return 'Section is full';
        }
        $this->q("INSERT INTO enlistment (student_id, section_id, date_enlisted) VALUES (?, ?, CURDATE())", $sid, $secId);
        $this->db->commit();
    }

    // returns the course code, false if it wasnt enlisted
    function drop($sid, $secId) {
        if (!$this->q("DELETE FROM enlistment WHERE student_id = ? AND section_id = ?", $sid, $secId)->rowCount()) return false;
        $this->q("UPDATE section SET slots = slots + 1 WHERE section_id = ?", $secId);
        return $this->q("SELECT c.code FROM section s JOIN course c ON c.course_id = s.course_id WHERE s.section_id = ?", $secId)->fetchColumn();
    }
}
