<?php
require_once 'Controller.php';

// courses and enlistment pages
class CourseController extends Controller {

    function offerings() {
        $this->needLogin();
        $q = strtolower(trim($_GET['q'] ?? ''));
        $mode = $_GET['mode'] ?? 'all';
        $avail = $_GET['avail'] ?? 'all';
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        $url = 'courses.php' . ($qs ? "?$qs" : '');

        if ($this->isPost()) {
            $this->enlist();
            $this->go($url);
        }

        $courses = [];
        foreach ($this->courses->all() as $c) {
            $c['sections'] = array_filter($c['sections'], function ($s) use ($c, $q, $mode, $avail) {
                if ($q != '' && stripos($c['code'] . ' ' . $c['name'] . ' ' . $s['prof'], $q) === false) return false;
                if ($mode != 'all' && !in_array($mode, $s['modes'])) return false;
                return $avail == 'all' || ($avail == 'open') == ($s['slots'] > 0);
            });
            if ($c['sections']) $courses[] = $c;
        }
        $mine = array_column($this->courses->enlisted($this->user['student_id']), 'section_id');
        return $this->data(['courses' => $courses, 'mine' => $mine, 'q' => $q, 'mode' => $mode, 'avail' => $avail, 'url' => $url]);
    }

    function enlistment() {
        $this->needRole('student');
        if ($this->isPost()) {
            if ($this->post('action') == 'drop') {
                $code = $this->courses->drop($this->user['student_id'], (int)$this->post('section_id'));
                if ($code) $this->flash('info', "Dropped $code");
            }
            else $this->enlist();
            $this->go('enlistment.php');
        }

        $courses = $this->courses->all();
        $secs = [];
        foreach ($courses as $c) {
            foreach ($c['sections'] as $s) $secs[$c['code']][] = ['id' => $s['section_id'], 'label' => "$s[sec] | $s[days] $s[time] | $s[slots] slots"];
        }
        $mine = $this->courses->enlisted($this->user['student_id']);
        return $this->data(['courses' => $courses, 'secs' => $secs, 'mine' => $mine, 'units' => array_sum(array_column($mine, 'units'))]);
    }

    private function enlist() {
        if ($this->user['role'] != 'student') return $this->flash('error', 'Only students can enlist');
        $id = (int)$this->post('section_id');
        $err = $this->courses->enlist($this->user['student_id'], $id);
        if ($err) return $this->flash('error', $err);
        $s = $this->courses->sections()[$id];
        $this->flash('success', 'Enlisted in ' . $s['code'] . ' ' . $s['sec']);
    }
}
