<?php
require_once 'Controller.php';

// organizations, org and orgsettings pages
class OrgController extends Controller {

    function catalog() {
        $this->needLogin();
        $q = strtolower(trim($_GET['q'] ?? ''));
        $cat = $_GET['category'] ?? 'all';
        $rec = $_GET['rec'] ?? 'all';

        $mine = [];
        foreach ($this->orgs->myOrgs($this->user['student_id']) as $m) $mine[$m['org_id']] = strtolower($m['role_name']);

        $all = $this->orgs->all();
        $list = [];
        foreach ($all as $o) {
            if ($q != '' && stripos($o['name'] . ' ' . $o['description'], $q) === false) continue;
            if ($rec == 'open' && !$o['recruiting'] || $rec == 'closed' && $o['recruiting']) continue;
            if ($cat != 'all' && $o['category'] != $cat) continue;
            $o['role'] = $mine[$o['org_id']] ?? 'none';
            $list[] = $o;
        }
        $slides = array_slice(array_filter($all, fn($o) => $o['recruiting']), 0, 3);
        return $this->data(['slides' => $slides, 'list' => $list, 'q' => $q, 'cat' => $cat, 'rec' => $rec]);
    }

    function detail() {
        $this->needLogin();
        $id = (int)($_GET['id'] ?? 0);
        $org = $this->orgs->find($id);
        if (!$org) return $this->data(['org' => null]);

        $acc = $this->access($id);
        if ($this->isPost()) {
            $this->apply($org, $acc['role']);
            $this->go("org.php?id=$id");
        }

        $sid = $this->user['student_id'];
        $members = $this->orgs->members($id);
        $anns = array_filter($this->orgs->announcements($id), fn($a) => $a['visibility'] != 'members' || $acc['role'] != 'none' || $this->user['role'] == 'admin');
        return $this->data([
            'org' => $org,
            'my_role' => $acc['role'],
            'manager' => $acc['manager'],
            'president' => $acc['president'],
            'pendMember' => $this->orgs->hasPending($sid, $id, 'member'),
            'pendOfficer' => $this->orgs->hasPending($sid, $id, 'officer'),
            'pendResign' => $this->orgs->hasPending($sid, $id, 'resign'),
            'memberCount' => count($members),
            'leaders' => array_filter($members, fn($m) => $m['role_name'] != 'Member'),
            'events' => $this->orgs->events($id),
            'upcoming' => count($this->orgs->upcoming($id)),
            'anns' => $anns,
            'appCount' => count($this->orgs->applications($id)),
            'resCount' => count($this->orgs->resignations($id))
        ]);
    }

    // apply to join, apply for officer or resign
    private function apply($org, $role) {
        $type = $this->post('type');
        $reason = $this->post('reason');
        $sid = $this->user['student_id'];
        $id = $org['org_id'];

        if ($reason == '') return $this->flash('error', 'Please explain your request.');
        if (!$sid || !in_array($type, ['member', 'officer', 'resign'])) return $this->flash('error', 'This request is no longer available.');
        if ($this->orgs->hasPending($sid, $id, $type)) return $this->flash('error', 'You already submitted this request.');

        if ($type == 'resign' && $role != 'none' && $role != 'president') {
            $this->orgs->resign($sid, $id, $reason);
            $this->flash('success', 'Resignation request submitted.');
        } elseif (($type == 'member' && $role == 'none' && $org['recruiting']) || ($type == 'officer' && $role == 'member' && $org['officer_hiring'])) {
            $this->orgs->apply($sid, $id, $type, $reason);
            $this->flash('success', 'Application submitted for review.');
        } else {
            $this->flash('error', 'This request is no longer available.');
        }
    }

    function settings() {
        $this->needLogin();
        $list = $this->orgs->managed($this->user);
        if (!$list) {
            $this->flash('error', 'You dont have access to org settings');
            $this->go('dashboard.php');
        }
        $cur = $list[0]['org_id'];
        foreach ($list as $o) {
            if ($o['org_id'] == ($_GET['id'] ?? 0)) $cur = $o['org_id'];
        }
        if ($this->isPost()) {
            $this->settingsPost($cur);
            $this->go("orgsettings.php?id=$cur");
        }

        $acc = $this->access($cur);
        $members = $this->orgs->members($cur);
        $apps = $this->orgs->applications($cur);
        $anns = $this->orgs->announcements($cur);
        return $this->data([
            'list' => $list,
            'cur' => $cur,
            'org' => $this->orgs->find($cur),
            'myrole' => $this->user['role'] == 'admin' ? 'admin' : $acc['role'],
            'is_pres' => $acc['president'],
            'stats' => [['Members', count($members)], ['Pending applications', count($apps)], ['Upcoming events', count($this->orgs->upcoming($cur))], ['Announcements', count($anns)]],
            'events' => $this->orgs->events($cur),
            'anns' => $anns,
            'apps' => $apps,
            'resigns' => $this->orgs->resignations($cur),
            'members' => $members,
            'roles' => array_filter($this->orgs->roles(), fn($r) => $r['role_name'] != 'President')
        ]);
    }

    private function settingsPost($cur) {
        $acc = $this->access($cur);
        if (!$acc['manager']) return $this->flash('error', 'No management access.');
        $a = $this->post('action');
        $id = (int)$this->post('id');
        $title = $this->post('title');
        $date = $this->post('date');

        if ($a == 'saveinfo') {
            if (!$acc['president']) return $this->flash('error', 'Only the president or admin can change organization information.');
            $desc = $this->post('desc');
            $type = $this->post('orgType');
            if ($desc == '' || $type == '') return $this->flash('error', 'Description and organization type are required.');
            $this->orgs->editInfo($cur, $desc, $type, $this->post('category'), $this->post('color'), isset($_POST['recruiting']) ? 1 : 0, isset($_POST['hiring']) ? 1 : 0);
            $this->flash('success', 'Organization information updated.');
        }
        elseif ($a == 'saveevent') {
            $place = $this->post('place');
            if ($title == '' || $date == '' || $place == '') return $this->flash('error', 'Fill in all required event fields.');
            $this->orgs->saveEvent($cur, $id, $title, $date, $this->post('time'), $place);
            $this->flash('success', $id ? 'Event updated.' : 'Event added.');
        }
        elseif ($a == 'deleteevent') {
            $this->orgs->deleteEvent($cur, $id);
            $this->flash('info', 'Event deleted');
        }
        elseif ($a == 'saveann') {
            $content = $this->post('content');
            if ($title == '' || $content == '' || $date == '') return $this->flash('error', 'Complete the announcement fields.');
            $this->orgs->saveAnn($cur, $id, $title, $content, $date, $this->post('visibility'));
            $this->flash('success', $id ? 'Announcement updated.' : 'Announcement published.');
        }
        elseif ($a == 'deleteann') {
            $this->orgs->deleteAnn($cur, $id);
            $this->flash('info', 'Announcement deleted.');
        }
        elseif ($a == 'approve' || $a == 'reject') {
            $this->orgs->decideApp($cur, $id, $a == 'approve');
            $this->flash($a == 'approve' ? 'success' : 'info', 'Application ' . ($a == 'approve' ? 'approved' : 'rejected'));
        }
        elseif ($a == 'accept' || $a == 'decline') {
            $this->orgs->decideRes($cur, $id, $a == 'accept');
            $this->flash($a == 'accept' ? 'success' : 'info', 'Resignation ' . ($a == 'accept' ? 'accepted' : 'declined'));
        }
        elseif ($a == 'changerole') {
            $role = (int)$this->post('role_id');
            if (!$role || $role == $this->orgs->roleId('President')) return $this->flash('error', 'Invalid role.');
            $this->orgs->setRole($cur, $id, $role);
            $this->flash('success', 'Role updated');
        }
        elseif ($a == 'removemember') {
            $this->orgs->removeMember($cur, $id);
            $this->flash('info', 'Member removed');
        }
    }
}
