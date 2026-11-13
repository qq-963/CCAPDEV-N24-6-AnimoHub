<?php
// Organization catalogue + single organization page
// the 2 functions here are what the router in index.php calls:
//   index.php?page=organizations       -> index()
//   index.php?page=org&id=3            -> show()
//
// member 4 added the org settings part at the bottom:
//   index.php?page=orgsettings&id=3    -> settings()
//   index.php?page=saveinfo            -> updateInfo()     (POST)
//   index.php?page=changerole          -> changeRole()     (POST)
//   index.php?page=removemember        -> removeMember()   (POST)
class OrgController extends Controller {
    private $orgs;

    function __construct($db) {
        // the parent constructor sets up $this->db and $this->users for us
        parent::__construct($db);
        $this->orgs = new OrgModel($db);
    }

    // the catalogue: carousel + search + filters + the grid of org cards
    function index() {
        $user = $this->needLogin();

        // the search form uses method="get", so everything it sends is in $_GET
        // "?? ''" means: if it is missing, use an empty value instead of crashing
        $q = trim($_GET['q'] ?? '');
        $category = $_GET['category'] ?? 'all';
        $recruiting = $_GET['recruiting'] ?? 'all';

        $list = $this->orgs->search($q, $category, $recruiting);

        // short version of the description for the cards
        foreach ($list as $i => $o) {
            $list[$i]['short'] = $this->shorten($o['description'], 90);
        }

        // carousel = first 3 orgs that are recruiting (ignores the filters, like in phase 1)
        $featured = array_slice($this->orgs->search('', 'all', 'open'), 0, 3);
        foreach ($featured as $i => $o) {
            $featured[$i]['short'] = $this->shorten($o['description'], 100);
        }

        $this->view('organizations', [
            'list' => $list,
            'featured' => $featured,
            'myRoles' => $this->orgs->rolesOfUser($user['user_id']),
            'categories' => $this->orgs->categories,
            // send the filters back so the form still shows what the user picked
            'q' => $q,
            'category' => $category,
            'recruiting' => $recruiting
        ]);
    }

    // one organization: details, officers, member count and my own role
    function show() {
        $user = $this->needLogin();

        // (int) turns the text from the url into a number, so "abc" becomes 0
        $id = (int)($_GET['id'] ?? 0);
        $org = $this->orgs->findById($id);

        // wrong id? go back to the catalogue with an error toast
        if (!$org) {
            $this->flash('error', 'Organization not found');
            $this->go('organizations');
        }

        // null when the user is not a member of this org
        $myRole = $this->orgs->roleOf($user['user_id'], $id);

        // admins can manage every org, students only if their role has can_manage
        $canManage = $user['role'] == 'admin' || ($myRole && $myRole['can_manage']);

        // member 4: events + announcements cards. members-only announcements are only for members and admins
        if ($myRole || $user['role'] == 'admin') {
            $announcements = AnnouncementModel::getAll($id);
        } else {
            $announcements = AnnouncementModel::getAll($id, 'public');
        }

        $this->view('org', [
            'org' => $org,
            'officers' => $this->orgs->officers($id),
            'myRole' => $myRole,
            'canManage' => $canManage,
            'events' => EventModel::getAll($id),
            'announcements' => $announcements
        ]);
    }

    // cuts long text and adds ... at the end
    private function shorten($text, $max) {
        if (strlen($text) <= $max) return $text;
        // mb_substr doesn't cut special characters in half, but not every server has it,
        // so we fall back to normal substr
        $cut = function_exists('mb_substr') ? mb_substr($text, 0, $max) : substr($text, 0, $max);
        return $cut . '...';
    }

    // ---------------------------------------------------------------
    // member 4: org settings
    // ---------------------------------------------------------------

    // the org settings page: info, events, announcements and members of one org
    function settings() {
        $user = $this->needLogin();

        $myOrgs = MemberModel::managedOrgs($user);
        if (count($myOrgs) == 0) {
            $this->flash('error', 'You do not manage any organization');
            $this->go('organizations');
        }

        // no id in the url (navbar link)? open the first org he can manage
        $id = (int)($_GET['id'] ?? 0);
        if ($id == 0) {
            $id = $myOrgs[0]['org_id'];
        }

        $org = $this->orgs->findById($id);
        if (!$org) {
            $this->flash('error', 'Organization not found');
            $this->go('organizations');
        }

        // enforces role check: Only allow if the user can manage the organization
        if (!($user['role'] == 'admin' || canManageOrg($user['user_id'], $id))) {
            $this->flash('error', 'Access Denied: You do not have permission to manage this organization.');
            $this->go('org&id=' . $id);
        }

        $this->view('orgsettings', [
            'org' => $org,
            'myOrgs' => $myOrgs,
            'events' => EventModel::getAll($id),
            'announcements' => AnnouncementModel::getAll($id),
            'members' => MemberModel::getOrgMembers($id),
            // only the president (or an admin) can change roles and remove members
            'isPres' => $user['role'] == 'admin' || isPresident($user['user_id'], $id)
        ]);
    }

    // the info form on the settings page (POST)
    // this used to read json from php://input, now it reads the form fields from $_POST
    function updateInfo() {
        $user = $this->needLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('orgsettings');

        $orgId = (int)($_POST['org_id'] ?? 0);
        $userId = $user['user_id'];

        // enforces role check: Only allow if the user can manage the organization
        if (!($user['role'] == 'admin' || canManageOrg($userId, $orgId))) {
            $this->flash('error', 'Access Denied: You do not have permission to manage this organization.');
            $this->go('org&id=' . $orgId);
        }

        $data = [
            'description' => trim($_POST['description'] ?? ''),
            'color' => $_POST['color'] ?? '#0b6b3a',
            'recruiting' => isset($_POST['recruiting']) ? 1 : 0,
            'officer_hiring' => isset($_POST['officer_hiring']) ? 1 : 0
        ];

        if ($data['description'] == '') {
            $this->flash('error', 'Description cannot be empty');
            $this->go('orgsettings&id=' . $orgId);
        }
        // color must look like #0b6b3a
        if (!preg_match('/^#[0-9a-fA-F]{6}$/', $data['color'])) {
            $data['color'] = '#0b6b3a';
        }

        // proceeds with updating info if authorized o7
        $success = OrgModel::updateInfo($orgId, $data);

        $this->flash($success ? 'success' : 'error', $success ? "Organization info updated successfully." : "Update failed.");
        $this->go('orgsettings&id=' . $orgId);
    }

    // member -> officer or officer -> member (POST), president or admin only
    function changeRole() {
        $user = $this->needLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('orgsettings');

        $orgId = (int)($_POST['org_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $role = $_POST['role'] ?? '';
        $back = 'orgsettings&id=' . $orgId;

        if (!($user['role'] == 'admin' || isPresident($user['user_id'], $orgId))) {
            $this->flash('error', 'Only the president can change roles');
            $this->go($back);
        }

        $member = MemberModel::getMember($orgId, $studentId);
        if (!$member) {
            $this->flash('error', 'Member not found');
            $this->go($back);
        }
        if ($member['role_name'] == 'President') {
            $this->flash('error', 'You cannot change the role of the president');
            $this->go($back);
        }

        // 5 = Officer, 6 = Member
        if ($role == 'officer') {
            $newRoleId = 5;
        } else if ($role == 'member') {
            $newRoleId = 6;
        } else {
            $this->flash('error', 'Pick a valid role');
            $this->go($back);
        }

        MemberModel::changeRole($orgId, $studentId, $newRoleId);
        $this->flash('success', 'Role changed');
        $this->go($back);
    }

    // removes someone from the org (POST), president or admin only
    function removeMember() {
        $user = $this->needLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') $this->go('orgsettings');

        $orgId = (int)($_POST['org_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $back = 'orgsettings&id=' . $orgId;

        if (!($user['role'] == 'admin' || isPresident($user['user_id'], $orgId))) {
            $this->flash('error', 'Only the president can remove members');
            $this->go($back);
        }

        $member = MemberModel::getMember($orgId, $studentId);
        if (!$member) {
            $this->flash('error', 'Member not found');
            $this->go($back);
        }
        if ($member['role_name'] == 'President') {
            $this->flash('error', 'You cannot remove the president');
            $this->go($back);
        }

        MemberModel::removeMember($orgId, $studentId);
        $this->flash('info', 'Member removed');
        $this->go($back);
    }
}
