<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';
foreach (['Model', 'UserModel', 'OrgModel', 'CourseModel'] as $m) require_once __DIR__ . "/../models/$m.php";

// parent of all controllers
class Controller {
    protected $users, $orgs, $courses, $user;

    function __construct() {
        global $db;
        $this->users = new UserModel($db);
        $this->orgs = new OrgModel($db);
        $this->courses = new CourseModel($db);
        $this->user = isset($_SESSION['uid']) ? ($this->users->findById($_SESSION['uid']) ?: null) : null;
    }

    protected function flash($type, $msg) { $_SESSION['flash'] = [$type, $msg]; }

    protected function go($url) {
        header("Location: $url");
        exit;
    }

    protected function isPost() { return $_SERVER['REQUEST_METHOD'] == 'POST'; }

    protected function post($key) { return trim($_POST[$key] ?? ''); }

    protected function needLogin() { if (!$this->user) $this->go('index.php'); }

    protected function needRole($role) {
        $this->needLogin();
        if ($this->user['role'] != $role) $this->go('dashboard.php');
    }

    // the users role in an org + if he can manage it
    protected function access($orgId) {
        $r = $this->orgs->roleOf($this->user['student_id'], $orgId);
        $admin = $this->user['role'] == 'admin';
        $role = $r ? strtolower($r['role_name']) : 'none';
        return ['role' => $role, 'manager' => $admin || ($r && $r['can_manage']), 'president' => $admin || $role == 'president'];
    }

    // adds what the navbar needs
    protected function data($d = []) {
        $d['user'] = $this->user;
        $d['manages'] = $this->user && count($this->orgs->managed($this->user)) > 0;
        return $d;
    }
}
