<?php
// woagh for member management,, ,
require_once __DIR__ . '/../config/db.php';

class MemberModel {

    // o getting them
    public static function getOrgMembers($orgId) {
        global $conn;
        $stmt = $conn->prepare("SELECT u.user_id, u.username, m.role FROM org_members m JOIN users u ON m.user_id = u.user_id WHERE m.org_id = ?");
        $stmt->bind_param("i", $orgId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // tis for changing roles o7
    public static function changeRole($orgId, $userId, $newRole) {
        global $conn;
        $stmt = $conn->prepare("UPDATE org_members SET role = ? WHERE org_id = ? AND user_id = ?");
        $stmt->bind_param("sii", $newRole, $orgId, $userId);
        return $stmt->execute();
    }

    // well i mean it's for removing members
    public static function removeMember($orgId, $userId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM org_members WHERE org_id = ? AND user_id = ?");
        $stmt->bind_param("ii", $orgId, $userId);
        return $stmt->execute();
    }
}
?>