<?php
// middlewares/RoleMiddleware.php
require_once __DIR__ . '/../config/db.php';

// the rest of the app calls the connection $db (PDO). these member 4 files use $conn,
// so $conn just points to the same connection
$conn = $db;

/**
 * Checks if a specific user is the president of the given organization.
 *
 * @param int $userId
 * @param int $orgId
 * @return bool
 */
function isPresident($userId, $orgId) {
    global $conn;

    // membership uses student_id, so we go user -> student -> membership -> org_role
    $stmt = $conn->prepare("SELECT r.role_name
                            FROM membership m
                            JOIN student s ON s.student_id = m.student_id
                            JOIN org_role r ON r.role_id = m.role_id
                            WHERE s.user_id = ?
                            AND m.org_id = ?");
    if (!$stmt) {
        return false;
    }

    $stmt->execute([$userId, $orgId]);
    $row = $stmt->fetch();

    // verifies if record exists, and validates if role is 'president'
    return ($row && isset($row['role_name']) && $row['role_name'] === 'President');
}

/**
 * validates if a user has management privileges (pres or officer) over the orgg
 *
 * @param int $userId
 * @param int $orgId
 * @return bool
 */
function canManageOrg($userId, $orgId) {
    global $conn;

    $stmt = $conn->prepare("SELECT r.role_name, r.can_manage
                            FROM membership m
                            JOIN student s ON s.student_id = m.student_id
                            JOIN org_role r ON r.role_id = m.role_id
                            WHERE s.user_id = ?
                            AND m.org_id = ?");
    if (!$stmt) {
        return false;
    }

    $stmt->execute([$userId, $orgId]);
    $row = $stmt->fetch();

    // verifies if record exists and role has administrative rights o7
    // (president, vice president, secretary, treasurer and officer all have can_manage = 1)
    if ($row && isset($row['can_manage'])) {
        return ($row['can_manage'] == 1);
    }

    return false;
}
?>