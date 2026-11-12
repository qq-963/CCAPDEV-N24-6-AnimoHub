<?php
require_once __DIR__ . '/../config/db.php';

class AnnouncementModel {
    
    // retrieves every announcementt; along with some filtering via visibility
    public static function getAll($visibility = null) {
        global $conn;
        if ($visibility) {
            $stmt = $conn->prepare("SELECT *
                                    FROM announcements
                                    WHERE visibility = ?
                                    ORDER BY created_at DESC");
            $stmt->bind_param("s", $visibility);
            $stmt->execute();
            $result = $stmt->get_result();
            return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        }
        
        $sql = "SELECT *
                FROM announcements
                ORDER BY created_at DESC";
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // retrieves a single announcement by idd
    public static function getById($announcementId) {
        global $conn;
        $stmt = $conn->prepare("SELECT *
                                FROM announcements
                                WHERE announcement_id = ?");
        $stmt->bind_param("i", $announcementId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // creates o7
    public static function create($data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO announcements (title, content, category, visibility, org_id)
                                VALUES (?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param(
            "ssssi", 
            $data['title'], 
            $data['content'], 
            $data['category'], 
            $data['visibility'], 
            $data['org_id']
        );
        
        $executed = $stmt->execute();
        return [
            "success" => $executed, 
            "announcement_id" => $executed ? $stmt->insert_id : null,
            "message" => $executed ? "Announcement published successfully." : $stmt->error
        ];
    }

    // updates an existing one o7
    public static function update($announcementId, $data) {
        global $conn;
        $stmt = $conn->prepare("UPDATE announcements
                                SET title = ?, content = ?, category = ?, visibility = ?
                                WHERE announcement_id = ?");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param(
            "ssssi", 
            $data['title'], 
            $data['content'], 
            $data['category'], 
            $data['visibility'], 
            $announcementId
        );
        
        $executed = $stmt->execute();
        return ["success" => $executed, "message" => $executed ? "Announcement updated successfully." : $stmt->error];
    }

    // deletes it umu
    public static function delete($announcementId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM announcements
                                WHERE announcement_id = ?");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param("i", $announcementId);
        $executed = $stmt->execute();
        return ["success" => $executed, "message" => $executed ? "Announcement deleted successfully." : $stmt->error];
    }
}
?>