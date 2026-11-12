<?php
class UserModel extends Model {

    function findByEmail($email) {
        return $this->q("SELECT * FROM `user` WHERE email = ?", $email)->fetch();
    }

    function findById($id) {
        return $this->q("SELECT u.*, s.student_id FROM `user` u LEFT JOIN student s ON s.user_id = u.user_id WHERE u.user_id = ?", $id)->fetch();
    }

    function findStudent($email) {
        return $this->q("SELECT u.*, s.student_id FROM `user` u JOIN student s ON s.user_id = u.user_id WHERE u.email = ?", $email)->fetch();
    }

    // adds the user and its admin/professor/student row
    function create($name, $email, $pw, $role) {
        $this->db->beginTransaction();
        $this->q("INSERT INTO `user` (email, password, name, role) VALUES (?, ?, ?, ?)", $email, $pw, $name, $role);
        $id = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO `$role` (user_id) VALUES ($id)");
        $this->db->commit();
        return $id;
    }

    function rename($id, $name) { $this->q("UPDATE `user` SET name = ? WHERE user_id = ?", $name, $id); }

    function adminId($uid) { return $this->q("SELECT admin_id FROM admin WHERE user_id = ?", $uid)->fetchColumn(); }

    function count() { return $this->q("SELECT COUNT(*) FROM `user`")->fetchColumn(); }
}
