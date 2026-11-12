<?php
// Organization catalogue + single organization page
// the 2 functions here are what the router in index.php calls:
//   index.php?page=organizations       -> index()
//   index.php?page=org&id=3            -> show()
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

        $this->view('org', [
            'org' => $org,
            'officers' => $this->orgs->officers($id),
            'myRole' => $myRole,
            'canManage' => $canManage
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
}
