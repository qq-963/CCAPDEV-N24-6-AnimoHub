<?php
class AuthController extends Controller {

    function login() {
        if ($this->user()) $this->go('dashboard');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = strtolower(trim($_POST['email'] ?? ''));
            $pw = $_POST['password'] ?? '';
            $u = $this->users->findByEmail($email);
            if ($email == '' || $pw == '') {
                $this->flash('error', 'Please fill in all fields');
            } elseif (!$u || $u['password'] != $pw) {
                $this->flash('error', 'Wrong email or password');
            } else {
                $_SESSION['uid'] = $u['user_id'];
                $this->flash('success', 'Welcome ' . $u['name'] . '!');
                $this->go('dashboard');
            }
        }
        $this->view('auth', ['tab' => 'login']);
    }

    function signup() {
        if ($this->user()) $this->go('dashboard');
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $pw = $_POST['password'] ?? '';
            $role = $this->detectRole($email);
            $err = $this->check($name, $email, $pw);
            if (!$err && !$role) $err = 'Please use a valid DLSU email';
            if (!$err && $pw != ($_POST['password2'] ?? '')) $err = 'Passwords dont match';
            if ($err) {
                $this->flash('error', $err);
            } else {
                $_SESSION['uid'] = $this->users->create($name, $email, $pw, $role);
                $this->flash('success', 'Account created!');
                $this->go('dashboard');
            }
        }
        $this->view('auth', ['tab' => 'signup']);
    }

    function logout() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') session_destroy();
        $this->go('login');
    }

    // create admin account form
    function admin() {
        $this->needAdmin();
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = strtolower(trim($_POST['email'] ?? ''));
            $pw = $_POST['password'] ?? '';
            $err = $this->check($name, $email, $pw);
            if ($err) {
                $this->flash('error', $err);
            } else {
                $this->users->create($name, $email, $pw, 'admin');
                $this->flash('success', 'Admin account created');
            }
            $this->go('admin');
        }
        $this->view('admin');
    }

    // student has _ and professor has .
    private function detectRole($email) {
        $local = explode('@', $email)[0];
        if (strpos($local, '_') !== false) return 'student';
        if (strpos($local, '.') !== false) return 'professor';
        return null;
    }

    // returns error message or null if ok
    private function check($name, $email, $pw) {
        if ($name == '' || $email == '' || $pw == '') return 'Please fill in all fields';
        if (!preg_match('/^[a-z0-9._-]+@dlsu\.edu\.ph$/', $email)) return 'Please use a valid DLSU email';
        if (strlen($pw) < 6) return 'Password must be at least 6 characters';
        if ($this->users->findByEmail($email)) return 'Email is already registered';
        return null;
    }
}
