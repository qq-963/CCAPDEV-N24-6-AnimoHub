<?php
// profile page with 3 tabs: info, schedule, my organizations
class ProfileController extends Controller {
    private $profile;
    private $orgs;
    private $enlistments;

    function __construct($db) {
        parent::__construct($db);
        $this->profile = new ProfileModel($db);
        $this->orgs = new OrgModel($db);
        $this->enlistments = new EnlistmentModel($db);
    }

    function index() {
        $user = $this->needLogin();

        // which tab is open, the link has &tab=sched or &tab=orgs
        $tab = 'info';
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }
        if ($tab != 'sched' && $tab != 'orgs') {
            $tab = 'info';
        }

        // only students have a schedule and orgs
        $scheduleRows = [];
        $myOrgs = [];
        if ($user['role'] == 'student') {
            $studentId = $this->enlistments->getStudentId($user['user_id']);
            $scheduleRows = $this->enlistments->getSchedule($studentId);

            $myOrgs = $this->profile->myOrgs($user['user_id']);
            foreach ($myOrgs as $i => $o) {
                $myOrgs[$i]['css'] = $this->orgs->badgeClass($o['role_name'], $o['can_manage']);
            }
        }

        $this->view('profile', [
            'tab' => $tab,
            'scheduleRows' => $scheduleRows,
            'myOrgs' => $myOrgs
        ]);
    }

    // the save button of the info tab (POST)
    function save() {
        $user = $this->needLogin();

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->go('profile');
        }

        $name = '';
        if (isset($_POST['name'])) {
            $name = trim($_POST['name']);
        }

        if ($name == '') {
            $this->flash('error', 'Name cant be empty');
            $this->go('profile');
        }
        if (strlen($name) > 100) {
            $this->flash('error', 'Name is too long');
            $this->go('profile');
        }

        $this->profile->updateName($user['user_id'], $name);
        $this->flash('success', 'Profile updated');
        $this->go('profile');
    }
}
