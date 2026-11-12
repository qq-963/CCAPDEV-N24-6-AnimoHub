<?php
require_once __DIR__ . '/../models/AnnounceModel.php';

// announcements of an org, same idea as EventController (index.php?page=announce)
// the form sends _method = POST (add), PUT (edit) or DELETE
class AnnounceController extends Controller {
    public function handleRequest() {
        $user = $this->needLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('orgsettings');

        $method = $_POST['_method'] ?? 'POST';
        $id = (int)($_POST['announcement_id'] ?? 0);
        $orgId = (int)($_POST['org_id'] ?? 0);

        // when editing or deleting, take the org from the saved announcement
        if ($id) {
            $ann = AnnouncementModel::getById($id);
            if (!$ann) {
                $this->flash('error', 'Announcement not found');
                $this->go('orgsettings');
            }
            $orgId = $ann['org_id'];
        }

        // enforces role check: Only allow if the user can manage the organization
        if (!($user['role'] == 'admin' || canManageOrg($user['user_id'], $orgId))) {
            $this->flash('error', 'Access Denied: You do not have permission to manage this organization.');
            $this->go('org&id=' . $orgId);
        }

        $back = 'orgsettings&id=' . $orgId;

        $visibility = $_POST['visibility'] ?? 'public';
        if ($visibility != 'members') {
            $visibility = 'public';
        }

        $data = [
            'org_id' => $orgId,
            'title' => trim($_POST['title'] ?? ''),
            'content' => trim($_POST['content'] ?? ''),
            'visibility' => $visibility
        ];

        if ($method == 'POST' || $method == 'PUT') {
            if ($data['title'] == '' || $data['content'] == '') {
                $this->flash('error', 'Please fill in the title and the message');
                $this->go($back);
            }
        }

        switch ($method) {
            case 'POST':
                $result = AnnouncementModel::create($data);
                break;
            case 'PUT':
                $result = AnnouncementModel::update($id, $data);
                break;
            case 'DELETE':
                $result = AnnouncementModel::delete($id);
                break;
            default:
                $result = ["success" => false, "message" => "Unknown action"];
        }

        $this->flash($result['success'] ? 'success' : 'error', $result['message']);
        $this->go($back);
    }
}
?>