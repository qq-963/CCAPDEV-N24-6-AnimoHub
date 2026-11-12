<?php
require_once 'Controller.php';

class DashboardController extends Controller {

    function index() {
        $this->needLogin();
        $u = $this->user;
        $courses = $this->courses->all();
        $orgs = $this->orgs->all();
        $codes = array_column($courses, 'code');
        $secCount = array_map(fn($c) => count($c['sections']), $courses);

        if ($u['role'] == 'student') {
            $mine = $this->courses->enlisted($u['student_id']);
            $units = array_sum(array_column($mine, 'units'));
            $stats = [['Courses enlisted', count($mine)], ['Total units', $units], ['Units left', MAX_UNITS - $units], ['Organizations joined', count($this->orgs->myOrgs($u['student_id']))]];
            $perDay = array_fill_keys(array_keys(DAYS), 0);
            foreach ($mine as $s) {
                foreach (str_split($s['days']) as $d) $perDay[$d]++;
            }
            $title = 'My classes per day';
            $chart = [array_values(DAYS), array_values($perDay)];
        } elseif ($u['role'] == 'professor') {
            $stats = [['Courses offered', count($courses)], ['Total sections', array_sum($secCount)], ['Organizations', count($orgs)]];
            $title = 'Sections per course';
            $chart = [$codes, $secCount];
        } else {
            $stats = [['Organizations', count($orgs)], ['Registered users', $this->users->count()], ['Memberships', array_sum(array_column($orgs, 'members'))]];
            $title = 'Members per organization';
            $chart = [array_map(fn($o) => substr($o['name'], 0, 14), $orgs), array_column($orgs, 'members')];
        }

        // open slots per course
        $slots = array_map(fn($c) => array_sum(array_column($c['sections'], 'slots')), $courses);

        return $this->data(['stats' => $stats, 'chart1title' => $title, 'chart1' => $chart, 'chart2' => [$codes, $slots], 'events' => $this->orgs->upcoming()]);
    }
}
