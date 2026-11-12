<?php
require_once __DIR__ . '/../config/db.php';

// table: announcement (announcement_id, org_id, title, content, publish_date, visibility)
// visibility is 'public' or 'members'
class AnnouncementModel {

    // retrieves every announcementt; along with some filtering via visibility
    public static function getAll($orgId, $visibility = null) {
        global $conn;
        if ($visibility) {
            $stmt = $conn->prepare("SELECT *
                                    FROM announcement
                                    WHERE org_id = ? AND visibility = ?
                                    ORDER BY publish_date DESC");
            $stmt->execute([$orgId, $visibility]);
            return $stmt->fetchAll();
        }

        $stmt = $conn->prepare("SELECT *
                FROM announcement
                WHERE org_id = ?
                ORDER BY publish_date DESC");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }

    // retrieves a single announcement by idd
    public static function getById($announcementId) {
        global $conn;
        $stmt = $conn->prepare("SELECT *
                                FROM announcement
                                WHERE announcement_id = ?");
        $stmt->execute([$announcementId]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    // creates o7
    public static function create($data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO announcement (org_id, title, content, publish_date, visibility)
                                VALUES (?, ?, ?, CURDATE(), ?)");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([
            $data['org_id'],
            $data['title'],
            $data['content'],
            $data['visibility']
        ]);
        return [
            "success" => $executed,
            "announcement_id" => $executed ? $conn->lastInsertId() : null,
            "message" => $executed ? "Announcement published successfully." : "Announcement could not be published."
        ];
    }

    // updates an existing one o7
    public static function update($announcementId, $data) {
        global $conn;
        $stmt = $conn->prepare("UPDATE announcement
                                SET title = ?, content = ?, visibility = ?
                                WHERE announcement_id = ?");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([
            $data['title'],
            $data['content'],
            $data['visibility'],
            $announcementId
        ]);
        return ["success" => $executed, "message" => $executed ? "Announcement updated successfully." : "Announcement could not be updated."];
    }

    // deletes it umu
    public static function delete($announcementId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM announcement
                                WHERE announcement_id = ?");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([$announcementId]);
        return ["success" => $executed, "message" => $executed ? "Announcement deleted successfully." : "Announcement could not be deleted."];
    }
}
?>