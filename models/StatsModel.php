<?php
// numbers for the dashboard
class StatsModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    function orgsJoined($studentId) {
        $st = $this->db->prepare("SELECT COUNT(*) FROM membership WHERE student_id = ?");
        $st->execute([$studentId]);
        return (int) $st->fetchColumn();
    }

    function countOrgs() {
        $st = $this->db->query("SELECT COUNT(*) FROM organization");
        return (int) $st->fetchColumn();
    }

    function countUsers() {
        $st = $this->db->query("SELECT COUNT(*) FROM `user`");
        return (int) $st->fetchColumn();
    }

    function countMemberships() {
        $st = $this->db->query("SELECT COUNT(*) FROM membership");
        return (int) $st->fetchColumn();
    }

    function countCourses() {
        $st = $this->db->query("SELECT COUNT(*) FROM course");
        return (int) $st->fetchColumn();
    }

    function countSections() {
        $st = $this->db->query("SELECT COUNT(*) FROM section");
        return (int) $st->fetchColumn();
    }

    // free slots of each course, all its sections added together
    function openSlotsPerCourse() {
        $st = $this->db->query("SELECT c.code, SUM(s.slots) AS total
                FROM course c
                JOIN section s ON s.course_id = c.course_id
                GROUP BY c.course_id, c.code
                ORDER BY c.code");
        return $st->fetchAll();
    }

    function sectionsPerCourse() {
        $st = $this->db->query("SELECT c.code, COUNT(s.section_id) AS total
                FROM course c
                JOIN section s ON s.course_id = c.course_id
                GROUP BY c.course_id, c.code
                ORDER BY c.code");
        return $st->fetchAll();
    }

    // LEFT JOIN so orgs with 0 members still show up
    function membersPerOrg() {
        $st = $this->db->query("SELECT o.name, COUNT(m.membership_id) AS total
                FROM organization o
                LEFT JOIN membership m ON m.org_id = o.org_id
                GROUP BY o.org_id, o.name
                ORDER BY o.name");
        return $st->fetchAll();
    }

    // next 6 events from today, LIMIT is typed in the query because ? doesnt work with LIMIT here
    function upcomingEvents() {
        $st = $this->db->query("SELECT e.title, e.date, e.place, o.name AS org_name, o.org_id
                FROM `event` e
                JOIN organization o ON o.org_id = e.org_id
                WHERE e.date >= CURDATE()
                ORDER BY e.date
                LIMIT 6");
        return $st->fetchAll();
    }

    // how many applications + resignations are waiting
    // admin sees all of them, an officer only sees the orgs he can manage
    function pendingRequests($user) {
        if ($user['role'] == 'admin') {
            $st = $this->db->query("SELECT COUNT(*) FROM application WHERE status = 'pending'");
            $apps = (int) $st->fetchColumn();
            $st = $this->db->query("SELECT COUNT(*) FROM resignation WHERE status = 'pending'");
            $res = (int) $st->fetchColumn();
            return $apps + $res;
        }

        $st = $this->db->prepare("SELECT COUNT(*) FROM application a
                JOIN membership m ON m.org_id = a.org_id
                JOIN student s ON s.student_id = m.student_id
                JOIN org_role r ON r.role_id = m.role_id
                WHERE a.status = 'pending' AND s.user_id = ? AND r.can_manage = 1");
        $st->execute([$user['user_id']]);
        $apps = (int) $st->fetchColumn();

        $st = $this->db->prepare("SELECT COUNT(*) FROM resignation x
                JOIN membership m ON m.org_id = x.org_id
                JOIN student s ON s.student_id = m.student_id
                JOIN org_role r ON r.role_id = m.role_id
                WHERE x.status = 'pending' AND s.user_id = ? AND r.can_manage = 1");
        $st->execute([$user['user_id']]);
        $res = (int) $st->fetchColumn();

        return $apps + $res;
    }
}
