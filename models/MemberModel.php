<?php
// woagh for member management,, ,
require_once __DIR__ . '/../config/db.php';

// membership uses student_id (not user_id), and the role is a role_id that points to org_role
// role ids: 1 President, 2 Vice President, 3 Secretary, 4 Treasurer, 5 Officer, 6 Member
class MemberModel {

    // o getting them
    public static function getOrgMembers($orgId) {
        global $conn;
        $stmt = $conn->prepare("SELECT s.student_id, u.user_id, u.name, m.role_id, r.role_name, r.can_manage
                FROM membership m
                JOIN student s ON s.student_id = m.student_id
                JOIN `user` u ON u.user_id = s.user_id
                JOIN org_role r ON r.role_id = m.role_id
                WHERE m.org_id = ?
                ORDER BY r.rank_level, u.name");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }

    // one member of one org, null if he is not in it
    public static function getMember($orgId, $studentId) {
        global $conn;
        $stmt = $conn->prepare("SELECT m.role_id, r.role_name
                FROM membership m
                JOIN org_role r ON r.role_id = m.role_id
                WHERE m.org_id = ? AND m.student_id = ?");
        $stmt->execute([$orgId, $studentId]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    // tis for changing roles o7
    public static function changeRole($orgId, $studentId, $newRoleId) {
        global $conn;
        $stmt = $conn->prepare("UPDATE membership SET role_id = ? WHERE org_id = ? AND student_id = ?");
        return $stmt->execute([$newRoleId, $orgId, $studentId]);
    }

    // well i mean it's for removing members
    public static function removeMember($orgId, $studentId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM membership WHERE org_id = ? AND student_id = ?");
        return $stmt->execute([$orgId, $studentId]);
    }

    // the orgs this user can open in org settings. admin gets every org
    public static function managedOrgs($user) {
        global $conn;
        if ($user['role'] == 'admin') {
            $stmt = $conn->query("SELECT org_id, name FROM organization ORDER BY name");
            return $stmt->fetchAll();
        }
        $stmt = $conn->prepare("SELECT o.org_id, o.name
                FROM membership m
                JOIN student s ON s.student_id = m.student_id
                JOIN org_role r ON r.role_id = m.role_id
                JOIN organization o ON o.org_id = m.org_id
                WHERE s.user_id = ? AND r.can_manage = 1
                ORDER BY o.name");
        $stmt->execute([$user['user_id']]);
        return $stmt->fetchAll();
    }
}
?>