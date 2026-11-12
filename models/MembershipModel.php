<?php
// membership table: who is in which org and what role they have
class MembershipModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    // the session has user_id but membership uses student_id
    function getStudentId($userId) {
        $st = $this->db->prepare("SELECT student_id FROM student WHERE user_id = ?");
        $st->execute([$userId]);
        return $st->fetchColumn();
    }

    // role of a student in one org, false if he is not a member
    function getRole($studentId, $orgId) {
        $st = $this->db->prepare("SELECT m.role_id, r.role_name, r.can_manage
                FROM membership m
                JOIN org_role r ON r.role_id = m.role_id
                WHERE m.student_id = ? AND m.org_id = ?");
        $st->execute([$studentId, $orgId]);
        return $st->fetch();
    }

    function add($studentId, $orgId, $roleId) {
        $st = $this->db->prepare("INSERT INTO membership (student_id, org_id, role_id, date_joined) VALUES (?, ?, ?, CURDATE())");
        $st->execute([$studentId, $orgId, $roleId]);
    }

    function setRole($studentId, $orgId, $roleId) {
        $st = $this->db->prepare("UPDATE membership SET role_id = ? WHERE student_id = ? AND org_id = ?");
        $st->execute([$roleId, $studentId, $orgId]);
    }

    function remove($studentId, $orgId) {
        $st = $this->db->prepare("DELETE FROM membership WHERE student_id = ? AND org_id = ?");
        $st->execute([$studentId, $orgId]);
    }
}
