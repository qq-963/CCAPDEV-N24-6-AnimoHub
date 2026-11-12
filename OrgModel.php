<?php
class OrgModel extends Model {
    private $cnt = "(SELECT COUNT(*) FROM membership m WHERE m.org_id = o.org_id) AS members";

    function all() { return $this->q("SELECT o.*, $this->cnt FROM organization o ORDER BY o.org_id")->fetchAll(); }

    function find($id) { return $this->q("SELECT o.*, $this->cnt FROM organization o WHERE o.org_id = ?", $id)->fetch(); }

    function add($name, $desc, $cat, $type, $adminId) {
        $this->q("INSERT INTO organization (created_by, name, description, category, org_type) VALUES (?, ?, ?, ?, ?)", $adminId, $name, $desc, $cat, $type);
        return $this->db->lastInsertId();
    }

    function edit($id, $name, $desc, $cat, $type) {
        $this->q("UPDATE organization SET name = ?, description = ?, category = ?, org_type = ? WHERE org_id = ?", $name, $desc, $cat, $type, $id);
    }

    function editInfo($id, $desc, $type, $cat, $color, $rec, $hire) {
        $this->q("UPDATE organization SET description = ?, org_type = ?, category = ?, color = ?, recruiting = ?, officer_hiring = ? WHERE org_id = ?", $desc, $type, $cat, $color, $rec, $hire, $id);
    }

    function remove($id) { $this->q("DELETE FROM organization WHERE org_id = ?", $id); }

    function roles() { return $this->q("SELECT * FROM org_role ORDER BY rank_level, role_id")->fetchAll(); }

    function roleId($name) { return $this->q("SELECT role_id FROM org_role WHERE role_name = ?", $name)->fetchColumn(); }

    // role row of a student in an org, false if not a member
    function roleOf($sid, $orgId) {
        return $this->q("SELECT r.* FROM membership m JOIN org_role r ON r.role_id = m.role_id WHERE m.student_id = ? AND m.org_id = ?", $sid, $orgId)->fetch();
    }

    // orgs the user can manage
    function managed($user) {
        if ($user['role'] == 'admin') return $this->all();
        return $this->q("SELECT o.*, $this->cnt FROM organization o JOIN membership m ON m.org_id = o.org_id JOIN org_role r ON r.role_id = m.role_id WHERE m.student_id = ? AND r.can_manage = 1 ORDER BY o.org_id", $user['student_id'])->fetchAll();
    }

    function myOrgs($sid) {
        return $this->q("SELECT o.org_id, o.name, r.role_name FROM membership m JOIN organization o ON o.org_id = m.org_id JOIN org_role r ON r.role_id = m.role_id WHERE m.student_id = ? ORDER BY o.org_id", $sid)->fetchAll();
    }

    function members($orgId) {
        return $this->q("SELECT m.student_id, m.role_id, r.role_name, u.name, u.email FROM membership m JOIN student s ON s.student_id = m.student_id JOIN `user` u ON u.user_id = s.user_id JOIN org_role r ON r.role_id = m.role_id WHERE m.org_id = ? ORDER BY r.rank_level, u.name", $orgId)->fetchAll();
    }

    function president($orgId) {
        return $this->q("SELECT u.name, u.email FROM membership m JOIN student s ON s.student_id = m.student_id JOIN `user` u ON u.user_id = s.user_id JOIN org_role r ON r.role_id = m.role_id WHERE m.org_id = ? AND r.role_name = 'President'", $orgId)->fetch();
    }

    // old president becomes a member
    function setPresident($orgId, $sid) {
        $pres = $this->roleId('President');
        $this->q("UPDATE membership SET role_id = ? WHERE org_id = ? AND role_id = ? AND student_id <> ?", $this->roleId('Member'), $orgId, $pres, $sid);
        if ($this->roleOf($sid, $orgId)) $this->q("UPDATE membership SET role_id = ? WHERE org_id = ? AND student_id = ?", $pres, $orgId, $sid);
        else $this->addMember($sid, $orgId, $pres);
    }

    function addMember($sid, $orgId, $roleId) {
        $this->q("INSERT INTO membership (student_id, org_id, role_id, date_joined) VALUES (?, ?, ?, CURDATE())", $sid, $orgId, $roleId);
    }

    function setRole($orgId, $sid, $roleId) {
        $this->q("UPDATE membership SET role_id = ? WHERE org_id = ? AND student_id = ? AND role_id <> ?", $roleId, $orgId, $sid, $this->roleId('President'));
    }

    function removeMember($orgId, $sid) {
        $this->q("DELETE FROM membership WHERE org_id = ? AND student_id = ? AND role_id <> ?", $orgId, $sid, $this->roleId('President'));
    }

    function events($orgId) { return $this->q("SELECT * FROM `event` WHERE org_id = ? ORDER BY `date`, `time`", $orgId)->fetchAll(); }

    // upcoming events, of one org or all of them if no id
    function upcoming($orgId = 0) {
        return $this->q("SELECT e.*, o.name AS org, o.color FROM `event` e JOIN organization o ON o.org_id = e.org_id WHERE e.`date` >= CURDATE() AND (? = 0 OR e.org_id = ?) ORDER BY e.`date`, e.`time`", $orgId, $orgId)->fetchAll();
    }

    function saveEvent($orgId, $id, $title, $date, $time, $place) {
        $time = $time ?: null;
        if ($id) $this->q("UPDATE `event` SET title = ?, `date` = ?, `time` = ?, place = ? WHERE event_id = ? AND org_id = ?", $title, $date, $time, $place, $id, $orgId);
        else $this->q("INSERT INTO `event` (org_id, title, `date`, `time`, place) VALUES (?, ?, ?, ?, ?)", $orgId, $title, $date, $time, $place);
    }

    function deleteEvent($orgId, $id) { $this->q("DELETE FROM `event` WHERE event_id = ? AND org_id = ?", $id, $orgId); }

    function announcements($orgId) {
        return $this->q("SELECT * FROM announcement WHERE org_id = ? ORDER BY publish_date DESC, announcement_id DESC", $orgId)->fetchAll();
    }

    function saveAnn($orgId, $id, $title, $content, $date, $vis) {
        if ($id) $this->q("UPDATE announcement SET title = ?, content = ?, publish_date = ?, visibility = ? WHERE announcement_id = ? AND org_id = ?", $title, $content, $date, $vis, $id, $orgId);
        else $this->q("INSERT INTO announcement (org_id, title, content, publish_date, visibility) VALUES (?, ?, ?, ?, ?)", $orgId, $title, $content, $date, $vis);
    }

    function deleteAnn($orgId, $id) { $this->q("DELETE FROM announcement WHERE announcement_id = ? AND org_id = ?", $id, $orgId); }

    function applications($orgId) {
        return $this->q("SELECT a.application_id, a.student_id, a.reason, r.role_name, u.name, u.email FROM application a JOIN student s ON s.student_id = a.student_id JOIN `user` u ON u.user_id = s.user_id JOIN org_role r ON r.role_id = a.role_id WHERE a.org_id = ? AND a.status = 'pending' ORDER BY a.application_id", $orgId)->fetchAll();
    }

    function resignations($orgId) {
        return $this->q("SELECT g.resignation_id, g.student_id, g.reason, u.name, u.email FROM resignation g JOIN student s ON s.student_id = g.student_id JOIN `user` u ON u.user_id = s.user_id WHERE g.org_id = ? AND g.status = 'pending' ORDER BY g.resignation_id", $orgId)->fetchAll();
    }

    // type is member, officer or resign
    function hasPending($sid, $orgId, $type) {
        if ($type == 'resign') return (bool)$this->q("SELECT 1 FROM resignation WHERE student_id = ? AND org_id = ? AND status = 'pending'", $sid, $orgId)->fetch();
        return (bool)$this->q("SELECT 1 FROM application WHERE student_id = ? AND org_id = ? AND role_id = ? AND status = 'pending'", $sid, $orgId, $this->roleId(ucfirst($type)))->fetch();
    }

    function apply($sid, $orgId, $type, $reason) {
        $this->q("INSERT INTO application (student_id, org_id, role_id, reason, date_submitted) VALUES (?, ?, ?, ?, CURDATE())", $sid, $orgId, $this->roleId(ucfirst($type)), $reason);
    }

    function resign($sid, $orgId, $reason) {
        $this->q("INSERT INTO resignation (student_id, org_id, reason, date_submitted) VALUES (?, ?, ?, CURDATE())", $sid, $orgId, $reason);
    }

    function decideApp($orgId, $id, $approve) {
        $a = $this->q("SELECT * FROM application WHERE application_id = ? AND org_id = ? AND status = 'pending'", $id, $orgId)->fetch();
        if (!$a) return;
        if ($approve) {
            $cur = $this->roleOf($a['student_id'], $orgId);
            if (!$cur) $this->addMember($a['student_id'], $orgId, $this->roleId('Member'));
            elseif ($cur['role_name'] == 'Member') $this->setRole($orgId, $a['student_id'], $a['role_id']);
        }
        $this->q("UPDATE application SET status = ? WHERE application_id = ?", $approve ? 'approved' : 'rejected', $id);
    }

    function decideRes($orgId, $id, $accept) {
        $r = $this->q("SELECT * FROM resignation WHERE resignation_id = ? AND org_id = ? AND status = 'pending'", $id, $orgId)->fetch();
        if (!$r) return;
        if ($accept) $this->removeMember($orgId, $r['student_id']);
        $this->q("UPDATE resignation SET status = ? WHERE resignation_id = ?", $accept ? 'accepted' : 'declined', $id);
    }
}
