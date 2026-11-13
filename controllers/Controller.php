<?php
// parent of all controllers
class Controller {
    protected $db;
    protected $users;

    function __construct($db) {
        $this->db = $db;
        $this->users = new UserModel($db);
    }

    protected function view($name, $data = []) {
        $user = $this->user();
        $manages = $user ? $this->users->canManage($user) : false;
        extract($data);
        require "views/$name.php";
    }

    protected function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }

    protected function go($page) {
        header("Location: index.php?page=$page");
        exit;
    }

    protected function user() {
        return isset($_SESSION['uid']) ? $this->users->findById($_SESSION['uid']) : null;
    }

    protected function needLogin() {
        $u = $this->user();
        if (!$u) $this->go('login');
        return $u;
    }

    protected function needAdmin() {
        $u = $this->needLogin();
        if ($u['role'] != 'admin') $this->go('dashboard');
        return $u;
    }
}
