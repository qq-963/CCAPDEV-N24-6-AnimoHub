<?php
// middlewares/RoleMiddleware.php
require_once __DIR__ . '/../config/db.php';

/**
 * Checks if a specific user is the president of the given organization.
 * 
 * @param int $userId
 * @param int $orgId
 * @return bool
 */
function isPresident($userId, $orgId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT role
                            FROM org_members
                            WHERE user_id = ?
                            AND org_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $userId, $orgId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // verifies if record exists, and validates if role is 'president'
    return ($row && isset($row['role']) && $row['role'] === 'president');
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
    
    $stmt = $conn->prepare("SELECT role
                            FROM org_members
                            WHERE user_id = ?
                            AND org_id = ?");
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ii", $userId, $orgId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    // verifies if record exists and role has administrative rights o7
    if ($row && isset($row['role'])) {
        $role = $row['role'];
        return ($role === 'president' || $role === 'officer');
    }
    
    return false;
}
?>