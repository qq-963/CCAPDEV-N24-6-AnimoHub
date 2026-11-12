<?php
require_once __DIR__ . '/../config/db.php';

// table: event (event_id, org_id, title, date, time, place)
class EventModel {

    // these will be sorted by dateee
    public static function getAll($orgId) {
        global $conn;
        $stmt = $conn->prepare("SELECT *
                FROM `event`
                WHERE org_id = ?
                ORDER BY `date` ASC");
        $stmt->execute([$orgId]);
        return $stmt->fetchAll();
    }

    // retrieves a single event through its ID
    public static function getById($eventId) {
        global $conn;
        $stmt = $conn->prepare("SELECT *
                                FROM `event`
                                WHERE event_id = ?");
        $stmt->execute([$eventId]);
        $row = $stmt->fetch();
        return $row ? $row : null;
    }

    // creates a new event recordd
    public static function create($data) {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO `event` (org_id, title, `date`, `time`, place)
                                VALUES (?, ?, ?, ?, ?)");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([
            $data['org_id'],
            $data['title'],
            $data['date'],
            $data['time'],
            $data['place']
        ]);
        return [
            "success" => $executed,
            "event_id" => $executed ? $conn->lastInsertId() : null,
            "message" => $executed ? "Event created successfully." : "Event could not be created."
        ];
    }

    // updates an existing event recorddd
    public static function update($eventId, $data) {
        global $conn;
        $stmt = $conn->prepare("UPDATE `event`
                                SET title = ?, `date` = ?, `time` = ?, place = ?
                                WHERE event_id = ?");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([
            $data['title'],
            $data['date'],
            $data['time'],
            $data['place'],
            $eventId
        ]);
        return ["success" => $executed, "message" => $executed ? "Event updated successfully." : "Event could not be updated."];
    }

    // deletes an event recorddd
    public static function delete($eventId) {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM `event`
                                WHERE event_id = ?");

        if (!$stmt) {
            return ["success" => false, "message" => "Prepare failed"];
        }

        $executed = $stmt->execute([$eventId]);
        return ["success" => $executed, "message" => $executed ? "Event deleted successfully." : "Event could not be deleted."];
    }
}
?>