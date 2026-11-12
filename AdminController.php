<?php
require_once 'Controller.php';

class AdminController extends Controller {

    function index() {
        $this->needRole('admin');
        if ($this->isPost()) {
            $a = $this->post('action');
            if ($a == 'saveorg') $this->saveOrg();
            elseif ($a == 'deleteorg') $this->deleteOrg();
            elseif ($a == 'createadmin') $this->createAdmin();
            $this->go('admin.php');
        }
        $orgs = $this->orgs->all();
        foreach ($orgs as $i => $o) {
            $p = $this->orgs->president($o['org_id']);
            $orgs[$i]['president'] = $p ? $p['email'] : '';
        }
        return $this->data(['orgs' => $orgs]);
    }

    private function saveOrg() {
        $id = $this->post('oId');
        $name = $this->post('name');
        $desc = $this->post('desc');
        $type = $this->post('orgType');
        $pres = strtolower($this->post('president'));

        if ($name == '' || $desc == '' || $type == '' || $pres == '') return $this->flash('error', 'Please fill in all fields');
        $p = $this->users->findStudent($pres);
        if (!$p) return $this->flash('error', 'President must be a registered student email');

        if ($id == '') {
            $id = $this->orgs->add($name, $desc, $this->post('category'), $type, $this->users->adminId($this->user['user_id']));
            $this->flash('success', 'Organization added');
        } else {
            $this->orgs->edit($id, $name, $desc, $this->post('category'), $type);
            $this->flash('success', 'Organization updated');
        }
        $this->orgs->setPresident($id, $p['student_id']);
    }

    private function deleteOrg() {
        $this->orgs->remove($this->post('id'));
        $this->flash('info', 'Organization deleted');
    }

    private function createAdmin() {
        $name = $this->post('name');
        $em = strtolower($this->post('email'));
        $pw = $_POST['password'] ?? '';

        if ($name == '' || $em == '' || $pw == '') return $this->flash('error', 'Fill in all fields');
        if (!dlsuEmail($em)) return $this->flash('error', 'Use a DLSU email');
        if (strlen($pw) < 6) return $this->flash('error', 'Password must be at least 6 characters');
        if ($this->users->findByEmail($em)) return $this->flash('error', 'Email already exists');

        $this->users->create($name, $em, $pw, 'admin');
        $this->flash('success', 'Admin account created');
    }
}
