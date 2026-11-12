<?php
require_once __DIR__ . '/../models/EventModel.php';

// events of an org. the forms on the org settings page post here (index.php?page=event)
// html forms can only send GET or POST, so the form sends a hidden field _method
// with POST (add), PUT (edit) or DELETE, and the switch below reads that instead
class EventController extends Controller {
    public function handleRequest() {
        $user = $this->needLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('orgsettings');

        $method = $_POST['_method'] ?? 'POST';
        $id = (int)($_POST['event_id'] ?? 0);
        $orgId = (int)($_POST['org_id'] ?? 0);

        // when editing or deleting, take the org from the saved event, not from the form
        if ($id) {
            $event = EventModel::getById($id);
            if (!$event) {
                $this->flash('error', 'Event not found');
                $this->go('orgsettings');
            }
            $orgId = $event['org_id'];
        }

        // enforces role check: Only allow if the user can manage the organization
        if (!($user['role'] == 'admin' || canManageOrg($user['user_id'], $orgId))) {
            $this->flash('error', 'Access Denied: You do not have permission to manage this organization.');
            $this->go('org&id=' . $orgId);
        }

        $back = 'orgsettings&id=' . $orgId;

        $data = [
            'org_id' => $orgId,
            'title' => trim($_POST['title'] ?? ''),
            'date' => $_POST['date'] ?? '',
            'time' => ($_POST['time'] ?? '') != '' ? $_POST['time'] : null,
            'place' => trim($_POST['place'] ?? '')
        ];

        // add and edit need the fields filled in
        if ($method == 'POST' || $method == 'PUT') {
            if ($data['title'] == '' || $data['place'] == '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date'])) {
                $this->flash('error', 'Please fill in the title, date and place');
                $this->go($back);
            }
        }

        switch ($method) {
            case 'POST':
                $result = EventModel::create($data);
                break;
            case 'PUT':
                $result = EventModel::update($id, $data);
                break;
            case 'DELETE':
                $result = EventModel::delete($id);
                break;
            default:
                $result = ["success" => false, "message" => "Unknown action"];
        }

        $this->flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->go($back);
    }
}
?>