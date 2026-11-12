<?php
// small stuff for the profile page
class ProfileModel {
    private $db;

    function __construct($db) {
        $this->db = $db;
    }

    function updateName($userId, $name) {
        $st = $this->db->prepare("UPDATE `user` SET name = ? WHERE user_id = ?");
        $st->execute([$name, $userId]);
    }

    // the orgs of a student with his role in each
    function myOrgs($userId) {
        $st = $this->db->prepare("SELECT o.org_id, o.name, r.role_name, r.can_manage
                FROM membership m
                JOIN student s ON s.student_id = m.student_id
                JOIN organization o ON o.org_id = m.org_id
                JOIN org_role r ON r.role_id = m.role_id
                WHERE s.user_id = ?
                ORDER BY o.name");
        $st->execute([$userId]);
        return $st->fetchAll();
    }
}
