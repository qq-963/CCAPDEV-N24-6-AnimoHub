<?php
require_once 'Controller.php';

class ProfileController extends Controller {

    function index() {
        $this->needLogin();
        if ($this->isPost()) {
            $name = $this->post('name');
            if ($name == '') $this->flash('error', 'Name cant be empty');
            else {
                $this->users->rename($this->user['user_id'], $name);
                $this->flash('success', 'Profile updated');
            }
            $this->go('profile.php');
        }
        return $this->data(['mine' => $this->courses->enlisted($this->user['student_id']), 'myOrgs' => $this->orgs->myOrgs($this->user['student_id'])]);
    }
}
