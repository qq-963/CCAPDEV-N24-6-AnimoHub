<?php
class UserModel {
    private $db;

    function __construct($db) { $this->db = $db; }

    function findByEmail($email) {
        $st = $this->db->prepare("SELECT * FROM `user` WHERE email = ?");
        $st->execute([$email]);
        return $st->fetch();
    }

    function findById($id) {
        $st = $this->db->prepare("SELECT * FROM `user` WHERE user_id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    // adds the user and its admin/professor/student row
    function create($name, $email, $password, $role) {
        $this->db->beginTransaction();
        $st = $this->db->prepare("INSERT INTO `user` (email, password, name, role) VALUES (?, ?, ?, ?)");
        $st->execute([$email, $password, $name, $role]);
        $id = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO `$role` (user_id) VALUES ($id)");
        $this->db->commit();
        return $id;
    }

    // true if the user can open org settings
    function canManage($user) {
        if ($user['role'] == 'admin') return true;
        if ($user['role'] != 'student') return false;
        $st = $this->db->prepare("SELECT COUNT(*) FROM membership m JOIN student s ON s.student_id = m.student_id JOIN org_role r ON r.role_id = m.role_id WHERE s.user_id = ? AND r.can_manage = 1");
        $st->execute([$user['user_id']]);
        return $st->fetchColumn() > 0;
    }
}
