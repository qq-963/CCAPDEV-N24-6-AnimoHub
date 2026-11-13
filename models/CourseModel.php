<?php
class CourseModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    function getSections($q, $mode, $avail) {
        $sql = "SELECT s.section_id, s.section_code, s.time_slot, s.slots, s.max_slots,
                c.course_id, c.code, c.name, c.units, r.room_code, u.name AS prof
                FROM section s
                JOIN course c ON c.course_id = s.course_id
                JOIN room r ON r.room_id = s.room_id
                JOIN professor p ON p.professor_id = s.professor_id
                JOIN `user` u ON u.user_id = p.user_id
                WHERE (c.code LIKE ? OR c.name LIKE ? OR u.name LIKE ?)";

        if ($avail == 'open') {
            $sql .= " AND s.slots > 0";
        }
        if ($avail == 'full') {
            $sql .= " AND s.slots <= 0";
        }
        $sql .= " ORDER BY c.code, s.section_code";

        $like = "%" . $q . "%";
        $st = $this->db->prepare($sql);
        $st->execute([$like, $like, $like]);
        $rows = $st->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $row['days'] = $this->getDays($row['section_id']);

            // mode filter is done here in php
            $keep = false;
            if ($mode == 'all') {
                $keep = true;
            }
            foreach ($row['days'] as $d) {
                if ($d['mode'] == $mode) {
                    $keep = true;
                }
            }

            if ($keep) {
                $result[] = $row;
            }
        }
        return $result;
    }

    function getSection($sectionId) {
        $st = $this->db->prepare("SELECT s.section_id, s.section_code, s.time_slot, s.slots, s.max_slots,
                c.course_id, c.code, c.name, c.units
                FROM section s
                JOIN course c ON c.course_id = s.course_id
                WHERE s.section_id = ?");
        $st->execute([$sectionId]);
        $section = $st->fetch();

        if ($section) {
            $section['days'] = $this->getDays($sectionId);
        }
        return $section;
    }

    // days of one section, M T W H F S
    function getDays($sectionId) {
        $st = $this->db->prepare("SELECT day, mode FROM section_schedule WHERE section_id = ? ORDER BY day");
        $st->execute([$sectionId]);
        return $st->fetchAll();
    }
}
