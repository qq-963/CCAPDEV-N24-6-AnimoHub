<?php
require_once __DIR__ . '/../models/EventModel.php';

class EventController {
    public static function handleRequest($method, $id = null) {
        header('Content-Type: application/json');
        switch ($method) {
            case 'GET':
                if ($id) {
                    echo json_encode(EventModel::getById($id));
                } else {
                    echo json_encode(EventModel::getAll());
                }
                break;
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                $success = EventModel::create($data);
                echo json_encode(["success" => $success]);
                break;
            case 'PUT':
                $data = json_decode(file_get_contents("php://input"), true);
                $success = EventModel::update($id, $data);
                echo json_encode(["success" => $success]);
                break;
            case 'DELETE':
                $success = EventModel::delete($id);
                echo json_encode(["success" => $success]);
                break;
        }
    }
}
?>