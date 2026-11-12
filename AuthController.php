<?php
require_once 'Controller.php';

// login, signup and logout
class AuthController extends Controller {

    function index() {
        if ($this->user) $this->go('dashboard.php');
        $tab = 'login';
        if ($this->isPost()) {
            if ($this->post('action') == 'signup') {
                $tab = 'signup';
                $this->signup();
            }
            else $this->login();
        }
        return $this->data(['tab' => $tab]);
    }

    private function login() {
        $email = strtolower($this->post('email'));
        $pw = $_POST['password'] ?? '';
        $u = $this->users->findByEmail($email);

        if ($email == '' || $pw == '') return $this->flash('error', 'Please fill in all fields');
        if (!$u || $u['password'] != $pw) return $this->flash('error', 'Wrong email or password');

        $_SESSION['uid'] = $u['user_id'];
        $this->flash('success', 'Welcome ' . $u['name'] . '!');
        $this->go('dashboard.php');
    }

    private function signup() {
        $name = $this->post('name');
        $email = strtolower($this->post('email'));
        $pw = $_POST['password'] ?? '';
        $role = $this->detectRole($email);

        if ($name == '' || $email == '' || $pw == '') return $this->flash('error', 'Please fill in all fields');
        if (!$role) return $this->flash('error', 'Please use a valid DLSU email');
        if (strlen($pw) < 6) return $this->flash('error', 'Password must be at least 6 characters');
        if ($pw != ($_POST['password2'] ?? '')) return $this->flash('error', 'Passwords dont match');
        if ($this->users->findByEmail($email)) return $this->flash('error', 'Email is already registered');

        $_SESSION['uid'] = $this->users->create($name, $email, $pw, $role);
        $this->flash('success', 'Account created!');
        $this->go('dashboard.php');
    }

    function logout() {
        if ($this->isPost()) session_destroy();
        $this->go('index.php');
    }

    // student has _ and professor has .
    private function detectRole($email) {
        if (!dlsuEmail($email)) return null;
        $local = explode('@', $email)[0];
        if (strpos($local, '_') !== false) return 'student';
        return strpos($local, '.') !== false ? 'professor' : null;
    }
}
