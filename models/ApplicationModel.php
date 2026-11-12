<?php
// application table: students applying to join an org (role 6 = Member) or to be an officer (role 5)
class ApplicationModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    // does this student already have a waiting application for this org and role?
    function hasPending($studentId, $orgId, $roleId) {
        $st = $this->db->prepare("SELECT COUNT(*) FROM application
                WHERE student_id = ? AND org_id = ? AND role_id = ? AND status = 'pending'");
        $st->execute([$studentId, $orgId, $roleId]);
        return $st->fetchColumn() > 0;
    }

    function create($studentId, $orgId, $roleId, $reason) {
        $st = $this->db->prepare("INSERT INTO application (student_id, org_id, role_id, reason, status, date_submitted)
                VALUES (?, ?, ?, ?, 'pending', CURDATE())");
        $st->execute([$studentId, $orgId, $roleId, $reason]);
    }

    // all waiting applications of one org, with the name of the student
    function getPending($orgId) {
        $st = $this->db->prepare("SELECT a.application_id, a.student_id, a.role_id, a.reason, a.date_submitted,
                u.name, u.email, r.role_name
                FROM application a
                JOIN student s ON s.student_id = a.student_id
                JOIN `user` u ON u.user_id = s.user_id
                JOIN org_role r ON r.role_id = a.role_id
                WHERE a.org_id = ? AND a.status = 'pending'
                ORDER BY a.date_submitted");
        $st->execute([$orgId]);
        return $st->fetchAll();
    }

    // one application, false if the id does not exist
    function find($id) {
        $st = $this->db->prepare("SELECT * FROM application WHERE application_id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    // status is 'approved' or 'rejected'
    function setStatus($id, $status) {
        $st = $this->db->prepare("UPDATE application SET status = ? WHERE application_id = ?");
        $st->execute([$status, $id]);
    }
}
