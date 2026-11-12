<?php
require_once __DIR__ . '/../config/db.php';

class EventModel {
    
    // these will be sorted by dateee
    public static function getAll() {
        global $conn;
        $sql = "SELECT *
                FROM events
                ORDER BY event_date ASC";
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // retrieves a single event through its ID
    public static function getById($eventId) {
        global $conn;
        $stmt = $conn->prepare("SELECT *
                                FROM events
                                WHERE event_id = ?");
        $stmt->bind_param("i", $eventId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result ? $result->fetch_assoc() : null;
    }

    // creates a new event recordd
    public static function create($data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO events (title, description, category, event_date, location, organizer_id)
                                VALUES (?, ?, ?, ?, ?, ?)");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param(
            "sssssi", 
            $data['title'], 
            $data['description'], 
            $data['category'], 
            $data['event_date'], 
            $data['location'], 
            $data['organizer_id']
        );
        
        $executed = $stmt->execute();
        return [
            "success" => $executed, 
            "event_id" => $executed ? $stmt->insert_id : null,
            "message" => $executed ? "Event created successfully." : $stmt->error
        ];
    }

    // updates an existing event recorddd
    public static function update($eventId, $data) {
        global $conn;
        $stmt = $conn->prepare("UPDATE events
                                SET title = ?, description = ?, category = ?, event_date = ?, location = ?
                                WHERE event_id = ?");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param(
            "sssssi", 
            $data['title'], 
            $data['description'], 
            $data['category'], 
            $data['event_date'], 
            $data['location'], 
            $eventId
        );
        
        $executed = $stmt->execute();
        return ["success" => $executed, "message" => $executed ? "Event updated successfully." : $stmt->error];
    }

    // deletes an event recorddd
    public static function delete($eventId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM events
                                WHERE event_id = ?");
        
        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed: " . $conn->error];
        }

        $stmt->bind_param("i", $eventId);
        $executed = $stmt->execute();
        return ["success" => $executed, "message" => $executed ? "Event deleted successfully." : $stmt->error];
    }
}
?>