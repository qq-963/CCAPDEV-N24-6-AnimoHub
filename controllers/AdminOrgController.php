<?php
// Admin side of the organizations
// only admins can use these pages, needAdmin() sends everybody else back to the dashboard
//   index.php?page=adminorgs   -> index()  shows the table + the add/edit form
//   index.php?page=saveorg     -> save()   the form posts here (add AND edit)
//   index.php?page=deleteorg   -> delete() the delete button posts here
class AdminOrgController extends Controller {
    private $orgs;

    function __construct($db) {
        parent::__construct($db);
        $this->orgs = new OrgModel($db);
    }

    // shows the page. if the url has &edit=3 the form is filled in with org 3
    function index() {
        $this->needAdmin();

        // default values for a brand new org
        $form = [
            'name' => '',
            'category' => 'Academic',
            'org_type' => 'Academic Organization',
            'description' => '',
            'president_email' => ''
        ];
        $editId = 0;

        if (!empty($_GET['edit'])) {
            $org = $this->orgs->findById((int)$_GET['edit']);
            if ($org) {
                $editId = $org['org_id'];
                $form = [
                    'name' => $org['name'],
                    'category' => $org['category'],
                    'org_type' => $org['org_type'],
                    'description' => $org['description'],
                    'president_email' => $this->orgs->presidentEmail($org['org_id'])
                ];
            } else {
                $this->flash('error', 'Organization not found');
                $this->go('adminorgs');
            }
        }

        $this->showPage($form, $editId);
    }

    // add a new org (no org_id sent) or edit one (org_id sent)
    function save() {
        $user = $this->needAdmin();

        // this page only makes sense after submitting the form (POST),
        // if somebody just opens it we send them back
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('adminorgs');

        // 0 = we are adding a new org
        $id = (int)($_POST['org_id'] ?? 0);

        $form = [
            'name' => trim($_POST['name'] ?? ''),
            'category' => $_POST['category'] ?? '',
            'org_type' => trim($_POST['org_type'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'president_email' => strtolower(trim($_POST['president_email'] ?? ''))
        ];

        $err = $this->check($form, $id);

        // the president has to be a registered student
        $president = null;
        if (!$err) {
            $president = $this->orgs->findStudentByEmail($form['president_email']);
            if (!$president) $err = 'President must be a registered student email';
        }

        // when editing, make sure the org still exists (somebody may have deleted it)
        if (!$err && $id && !$this->orgs->findById($id)) $err = 'Organization not found';

        // something is wrong: show the same page again with what the admin typed,
        // so they don't have to type everything again
        if ($err) {
            $this->flash('error', $err);
            $this->showPage($form, $id);
            return;
        }

        if ($id) {
            $this->orgs->update($id, $form, $president['student_id']);
            $this->flash('success', 'Organization updated');
        } else {
            $adminId = $this->orgs->adminIdOf($user['user_id']);
            $this->orgs->create($form, $adminId, $president['student_id']);
            $this->flash('success', 'Organization added');
        }
        $this->go('adminorgs');
    }

    // delete one org (the database removes its members, events, etc. on its own)
    function delete() {
        $this->needAdmin();
        // deleting must be a POST, a link someone clicks by accident (GET) should never delete anything
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('adminorgs');

        $id = (int)($_POST['org_id'] ?? 0);
        if (!$this->orgs->findById($id)) {
            $this->flash('error', 'Organization not found');
        } else {
            $this->orgs->delete($id);
            $this->flash('success', 'Organization deleted');
        }
        $this->go('adminorgs');
    }

    // loads the table data and shows the page, used by index() and by save() when there is an error
    private function showPage($form, $editId) {
        $this->view('admin_orgs', [
            'orgList' => $this->orgs->listForAdmin(),
            'categories' => $this->orgs->categories,
            'form' => $form,
            'editId' => $editId
        ]);
    }

    // checks the form, returns an error message or null if everything is fine
    private function check($f, $id) {
        if ($f['name'] == '' || $f['org_type'] == '' || $f['description'] == '' || $f['president_email'] == '') {
            return 'Please fill in all fields';
        }
        if (!in_array($f['category'], $this->orgs->categories)) return 'Please pick a valid category';
        // lengths match the columns in the database
        if (strlen($f['name']) > 100) return 'Name is too long (100 characters max)';
        if (strlen($f['org_type']) > 50) return 'Organization type is too long (50 characters max)';
        if (!preg_match('/^[a-z0-9._-]+@dlsu\.edu\.ph$/', $f['president_email'])) return 'Use a DLSU email for the president';
        if ($this->orgs->nameTaken($f['name'], $id)) return 'An organization with that name already exists';
        return null;
    }
}
