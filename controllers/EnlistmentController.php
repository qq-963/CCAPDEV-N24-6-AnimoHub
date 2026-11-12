<?php
class EnlistmentController extends Controller {
    private $courses;
    private $enlistments;
    private $maxUnits = 24;

    function __construct($db) {
        parent::__construct($db);
        $this->courses = new CourseModel($db);
        $this->enlistments = new EnlistmentModel($db);
    }

    function index() {
        $user = $this->needLogin();
        if ($user['role'] != 'student') {
            $this->flash('error', 'Only students can enlist');
            $this->go('dashboard');
        }

        $studentId = $this->enlistments->getStudentId($user['user_id']);

        $this->view('enlistment', [
            'sections' => $this->courses->getSections('', 'all', 'all'),
            'enlisted' => $this->enlistments->getEnlisted($studentId),
            'units' => $this->enlistments->totalUnits($studentId),
            'maxUnits' => $this->maxUnits
        ]);
    }

    function schedule() {
        $user = $this->needLogin();
        if ($user['role'] != 'student') {
            $this->flash('error', 'Only students have a schedule');
            $this->go('dashboard');
        }

        $studentId = $this->enlistments->getStudentId($user['user_id']);

        $this->view('schedule', [
            'scheduleRows' => $this->enlistments->getSchedule($studentId)
        ]);
    }

    function enlist() {
        $user = $this->needLogin();
        if ($user['role'] != 'student') {
            $this->flash('error', 'Only students can enlist');
            $this->go('dashboard');
        }

        // which page to go back to
        $from = 'enlistment';
        if (isset($_POST['from']) && $_POST['from'] == 'courses') {
            $from = 'courses';
        }

        // typing the url opens it with GET, send them back
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->go($from);
        }

        $studentId = $this->enlistments->getStudentId($user['user_id']);
        $section = $this->courses->getSection($_POST['section_id']);

        // check 1: section exists
        if (!$section) {
            $this->flash('error', 'Section not found');
            $this->go($from);
        }

        // check 2: duplicate course
        if ($this->enlistments->hasCourse($studentId, $section['course_id'])) {
            $this->flash('error', 'You already enlisted ' . $section['code']);
            $this->go($from);
        }

        // check 3: full section
        if ($section['slots'] <= 0) {
            $this->flash('error', 'Section is full');
            $this->go($from);
        }

        // check 4: max units
        $units = $this->enlistments->totalUnits($studentId);
        if ($units + $section['units'] > $this->maxUnits) {
            $this->flash('error', 'Max units reached (' . $this->maxUnits . ')');
            $this->go($from);
        }

        // check 5: schedule conflict
        if ($this->enlistments->hasConflict($studentId, $section)) {
            $this->flash('error', 'Schedule conflict with another course');
            $this->go($from);
        }

        $ok = $this->enlistments->enlist($studentId, $section['section_id']);
        if ($ok) {
            $this->flash('success', 'Enlisted in ' . $section['code'] . ' ' . $section['section_code']);
        } else {
            $this->flash('error', 'Could not enlist, try again');
        }
        $this->go($from);
    }

    function drop() {
        $user = $this->needLogin();
        if ($user['role'] != 'student') {
            $this->flash('error', 'Only students can drop');
            $this->go('dashboard');
        }

        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->go('enlistment');
        }

        $studentId = $this->enlistments->getStudentId($user['user_id']);
        $section = $this->courses->getSection($_POST['section_id']);

        if (!$section) {
            $this->flash('error', 'Section not found');
            $this->go('enlistment');
        }

        $ok = $this->enlistments->drop($studentId, $section['section_id']);
        if ($ok) {
            $this->flash('info', 'Dropped ' . $section['code']);
        } else {
            $this->flash('error', 'Could not drop that course');
        }
        $this->go('enlistment');
    }
}
