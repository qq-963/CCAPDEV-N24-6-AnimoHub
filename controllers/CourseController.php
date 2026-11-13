<?php
class CourseController extends Controller {
    private $courses;
    private $enlistments;

    function __construct($db) {
        parent::__construct($db);
        $this->courses = new CourseModel($db);
        $this->enlistments = new EnlistmentModel($db);
    }

    function index() {
        $user = $this->needLogin();

        // filters from the search form
        $q = '';
        $mode = 'all';
        $avail = 'all';
        if (isset($_GET['q'])) {
            $q = trim($_GET['q']);
        }
        if (isset($_GET['mode'])) {
            $mode = $_GET['mode'];
        }
        if (isset($_GET['avail'])) {
            $avail = $_GET['avail'];
        }

        $sections = $this->courses->getSections($q, $mode, $avail);

        // put the sections under their course
        $courses = [];
        foreach ($sections as $s) {
            $code = $s['code'];
            if (!isset($courses[$code])) {
                $courses[$code] = ['code' => $code, 'name' => $s['name'], 'units' => $s['units'], 'sections' => []];
            }
            $courses[$code]['sections'][] = $s;
        }

        // sections the student already enlisted in
        $enlistedIds = [];
        if ($user['role'] == 'student') {
            $studentId = $this->enlistments->getStudentId($user['user_id']);
            foreach ($this->enlistments->getEnlisted($studentId) as $e) {
                $enlistedIds[] = $e['section_id'];
            }
        }

        $this->view('courses', [
            'courses' => $courses,
            'enlistedIds' => $enlistedIds,
            'q' => $q,
            'mode' => $mode,
            'avail' => $avail
        ]);
    }
}
