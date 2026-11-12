<?php
// resignation table: members asking to leave an org
class ResignationModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    function hasPending($studentId, $orgId) {
        $st = $this->db->prepare("SELECT COUNT(*) FROM resignation
                WHERE student_id = ? AND org_id = ? AND status = 'pending'");
        $st->execute([$studentId, $orgId]);
        return $st->fetchColumn() > 0;
    }

    function create($studentId, $orgId, $reason) {
        $st = $this->db->prepare("INSERT INTO resignation (student_id, org_id, reason, status, date_submitted)
                VALUES (?, ?, ?, 'pending', CURDATE())");
        $st->execute([$studentId, $orgId, $reason]);
    }

    function getPending($orgId) {
        $st = $this->db->prepare("SELECT x.resignation_id, x.student_id, x.reason, x.date_submitted,
                u.name, u.email
                FROM resignation x
                JOIN student s ON s.student_id = x.student_id
                JOIN `user` u ON u.user_id = s.user_id
                WHERE x.org_id = ? AND x.status = 'pending'
                ORDER BY x.date_submitted");
        $st->execute([$orgId]);
        return $st->fetchAll();
    }

    function find($id) {
        $st = $this->db->prepare("SELECT * FROM resignation WHERE resignation_id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    // status is 'accepted' or 'declined'
    function setStatus($id, $status) {
        $st = $this->db->prepare("UPDATE resignation SET status = ? WHERE resignation_id = ?");
        $st->execute([$status, $id]);
    }
}
