<?php
// all the sql for the organization table lives in this file, the controllers
// never write sql themselves, they just call the functions below.
//
// a note on the "?" you will see in every query:
// the ? is a placeholder. we pass the real values separately in execute([...])
// and PDO puts them in safely, so nobody can sneak sql in through a form (sql injection).
class OrgModel {
    private $db;

    // same 4 categories as the ENUM in the .sql file
    public $categories = ['Academic', 'Technology', 'Community', 'Government'];

    function __construct($db) { $this->db = $db; }

    // ---------------------------------------------------------------
    // catalogue (the Organizations page)
    // ---------------------------------------------------------------

    // list orgs, with optional filters
    // $q = search text, $category = 'all' or one of the categories,
    // $recruiting = 'all', 'open' or 'closed'
    function search($q = '', $category = 'all', $recruiting = 'all') {
        // member_count is a small sub-query that counts the rows of membership for each org
        $sql = "SELECT o.*,
                       (SELECT COUNT(*) FROM membership m WHERE m.org_id = o.org_id) AS member_count
                FROM organization o
                WHERE 1 = 1";
        $params = [];

        // "WHERE 1 = 1" is always true, it just lets us add "AND ..." pieces below
        // without worrying if it is the first condition or not
        if ($q != '') {
            $sql .= " AND (o.name LIKE ? OR o.description LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        if (in_array($category, $this->categories)) {
            $sql .= " AND o.category = ?";
            $params[] = $category;
        }
        if ($recruiting == 'open') {
            $sql .= " AND o.recruiting = 1";
        } elseif ($recruiting == 'closed') {
            $sql .= " AND o.recruiting = 0";
        }

        $sql .= " ORDER BY o.name";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    // what role the logged in user has in EACH org, for the badges on the cards
    // returns something like [1 => ['role_name' => 'President', ...], 3 => [...]]
    // (the key is the org_id, so the page can look up an org quickly)
    function rolesOfUser($userId) {
        $st = $this->db->prepare("SELECT m.org_id, r.role_name, r.can_manage
                                  FROM membership m
                                  JOIN student s ON s.student_id = m.student_id
                                  JOIN org_role r ON r.role_id = m.role_id
                                  WHERE s.user_id = ?");
        $st->execute([$userId]);

        $map = [];
        foreach ($st->fetchAll() as $row) {
            $row['css'] = $this->badgeClass($row['role_name'], $row['can_manage']);
            $map[$row['org_id']] = $row;
        }
        return $map;
    }

    // ---------------------------------------------------------------
    // one organization (the org details page)
    // ---------------------------------------------------------------

    // the org itself plus its member count, or false if the id does not exist
    function findById($id) {
        $st = $this->db->prepare("SELECT o.*,
                                         (SELECT COUNT(*) FROM membership m WHERE m.org_id = o.org_id) AS member_count
                                  FROM organization o
                                  WHERE o.org_id = ?");
        $st->execute([$id]);
        return $st->fetch();
    }

    // president, vice president, officers... basically everybody who is not a plain Member
    // sorted so the president comes first
    function officers($orgId) {
        $st = $this->db->prepare("SELECT u.name, u.email, r.role_name, r.can_manage
                                  FROM membership m
                                  JOIN student s ON s.student_id = m.student_id
                                  JOIN `user` u ON u.user_id = s.user_id
                                  JOIN org_role r ON r.role_id = m.role_id
                                  WHERE m.org_id = ? AND r.role_name <> 'Member'
                                  ORDER BY r.rank_level, u.name");
        $st->execute([$orgId]);

        $list = $st->fetchAll();
        foreach ($list as $i => $row) {
            $list[$i]['css'] = $this->badgeClass($row['role_name'], $row['can_manage']);
        }
        return $list;
    }

    // the role of ONE user in ONE org
    // returns null if the user is not a member (admins and professors are never members)
    function roleOf($userId, $orgId) {
        $st = $this->db->prepare("SELECT r.role_name, r.can_manage
                                  FROM membership m
                                  JOIN student s ON s.student_id = m.student_id
                                  JOIN org_role r ON r.role_id = m.role_id
                                  WHERE s.user_id = ? AND m.org_id = ?");
        $st->execute([$userId, $orgId]);
        $row = $st->fetch();
        if (!$row) return null;
        $row['css'] = $this->badgeClass($row['role_name'], $row['can_manage']);
        return $row;
    }

    // picks the badge color from style.css: purple for president, blue for officers, green for members
    function badgeClass($roleName, $canManage) {
        if ($roleName == 'President') return 'president';
        if ($canManage) return 'officer';
        return 'member';
    }

    // ---------------------------------------------------------------
    // admin side (add / edit / delete / president)
    // ---------------------------------------------------------------

    // every org for the admin table, with the president's name and email
    function listForAdmin() {
        $st = $this->db->query("SELECT o.org_id, o.name, o.category,
                                       (SELECT COUNT(*) FROM membership m WHERE m.org_id = o.org_id) AS member_count,
                                       (SELECT u.name FROM membership m
                                          JOIN student s ON s.student_id = m.student_id
                                          JOIN `user` u ON u.user_id = s.user_id
                                          JOIN org_role r ON r.role_id = m.role_id
                                          WHERE m.org_id = o.org_id AND r.role_name = 'President'
                                          LIMIT 1) AS president_name
                                FROM organization o
                                ORDER BY o.name");
        return $st->fetchAll();
    }

    // email of the current president, '' if the org has none (used to fill in the edit form)
    function presidentEmail($orgId) {
        $st = $this->db->prepare("SELECT u.email
                                  FROM membership m
                                  JOIN student s ON s.student_id = m.student_id
                                  JOIN `user` u ON u.user_id = s.user_id
                                  JOIN org_role r ON r.role_id = m.role_id
                                  WHERE m.org_id = ? AND r.role_name = 'President'
                                  LIMIT 1");
        $st->execute([$orgId]);
        $email = $st->fetchColumn();
        return $email ? $email : '';
    }

    // finds the student row that belongs to an email, false if there is none
    // (only students can be presidents, admins and professors don't have a student row)
    function findStudentByEmail($email) {
        $st = $this->db->prepare("SELECT s.student_id, u.name
                                  FROM `user` u
                                  JOIN student s ON s.user_id = u.user_id
                                  WHERE u.email = ?");
        $st->execute([$email]);
        return $st->fetch();
    }

    // organization.created_by points to the admin table (admin_id), not to user_id,
    // so we have to look the admin_id up first
    function adminIdOf($userId) {
        $st = $this->db->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
        $st->execute([$userId]);
        $id = $st->fetchColumn();
        return $id ? $id : null;
    }

    // true if another org already uses this name (ignores upper/lower case)
    // $exceptId is the org we are editing right now, so it does not clash with itself
    function nameTaken($name, $exceptId = 0) {
        $st = $this->db->prepare("SELECT COUNT(*) FROM organization WHERE LOWER(name) = LOWER(?) AND org_id <> ?");
        $st->execute([$name, $exceptId]);
        return $st->fetchColumn() > 0;
    }

    // add a new org and make $presidentId (a student_id) its president
    // $f = array with name, category, org_type, description
    // returns the new org_id
    function create($f, $adminId, $presidentId) {
        // a transaction = all or nothing. if the president step fails,
        // the org insert is undone too, so we never end up with half-made data
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("INSERT INTO organization (created_by, name, description, category, org_type)
                                      VALUES (?, ?, ?, ?, ?)");
            $st->execute([$adminId, $f['name'], $f['description'], $f['category'], $f['org_type']]);
            $orgId = $this->db->lastInsertId();

            $this->setPresident($orgId, $presidentId);
            $this->db->commit();
            return $orgId;
        } catch (Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    // edit an org and (if it changed) change its president
    // color / recruiting / officer_hiring are not touched here, the president edits those later
    function update($id, $f, $presidentId) {
        $this->db->beginTransaction();
        try {
            $st = $this->db->prepare("UPDATE organization
                                      SET name = ?, description = ?, category = ?, org_type = ?
                                      WHERE org_id = ?");
            $st->execute([$f['name'], $f['description'], $f['category'], $f['org_type'], $id]);

            $this->setPresident($id, $presidentId);
            $this->db->commit();
        } catch (Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }

    // delete the org
    // we only delete the one row. the other tables (membership, application,
    // resignation, event, announcement) were made with ON DELETE CASCADE in the .sql file,
    // so MySQL automatically removes every row that belonged to this org.
    function delete($id) {
        $st = $this->db->prepare("DELETE FROM organization WHERE org_id = ?");
        $st->execute([$id]);
    }

    // makes this student the president of the org
    // private = only this class uses it. it has no transaction of its own because
    // create() and update() already opened one (PDO can't nest transactions)
    private function setPresident($orgId, $studentId) {
        $presidentRole = $this->roleId('President');
        $memberRole = $this->roleId('Member');

        // the old president (if it is somebody else) becomes a normal member
        $st = $this->db->prepare("UPDATE membership SET role_id = ?
                                  WHERE org_id = ? AND role_id = ? AND student_id <> ?");
        $st->execute([$memberRole, $orgId, $presidentRole, $studentId]);

        // is the new president already in the org?
        $st = $this->db->prepare("SELECT membership_id FROM membership WHERE org_id = ? AND student_id = ?");
        $st->execute([$orgId, $studentId]);
        $existing = $st->fetchColumn();

        if ($existing) {
            // yes, so just promote them
            $st = $this->db->prepare("UPDATE membership SET role_id = ? WHERE membership_id = ?");
            $st->execute([$presidentRole, $existing]);
        } else {
            // no, so add them as a member with the president role
            $st = $this->db->prepare("INSERT INTO membership (student_id, org_id, role_id, date_joined)
                                      VALUES (?, ?, ?, ?)");
            $st->execute([$studentId, $orgId, $presidentRole, date('Y-m-d')]);
        }
    }

    // looks up the id of a role by its name, so we don't hard code numbers like 1 or 6
    private function roleId($roleName) {
        $st = $this->db->prepare("SELECT role_id FROM org_role WHERE role_name = ?");
        $st->execute([$roleName]);
        return $st->fetchColumn();
    }

    // ---------------------------------------------------------------
    // member 4: the org info form on the org settings page
    // ---------------------------------------------------------------

    // updates what the president/officers can edit: description, color, recruiting, officer hiring
    // static and uses $conn like the other member 4 files (OrgController::updateInfo calls it)
    public static function updateInfo($orgId, $data) {
        global $conn;
        $stmt = $conn->prepare("UPDATE organization
                                SET description = ?, color = ?, recruiting = ?, officer_hiring = ?
                                WHERE org_id = ?");
        return $stmt->execute([
            $data['description'],
            $data['color'],
            $data['recruiting'],
            $data['officer_hiring'],
            $orgId
        ]);
    }
}
