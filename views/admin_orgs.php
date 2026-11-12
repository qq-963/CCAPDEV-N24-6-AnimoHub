<?php
// Admin: manage organizations
// replaces the organizations part of the old admin.html.
//   $orgList    = every org for the table
//   $form       = what to show inside the form (blank, filled in for editing, or what the admin just typed)
//   $editId     = 0 when adding a new org, the org_id when editing
$title = 'Manage Organizations';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Manage Organizations</h1>
  <p><a href="index.php?page=admin">&larr; Back to Admin</a></p>

  <div class="card">
    <h3><?= $editId ? 'Edit Organization' : 'Add Organization' ?></h3>

    <?php // method="post" because this form changes data. it goes to the saveorg route ?>
    <form method="post" action="index.php?page=saveorg">
      <?php // hidden field: empty = new org, a number = edit that org ?>
      <input type="hidden" name="org_id" value="<?= (int)$editId ?>">

      <div class="twocol">
        <div class="field">
          <label for="oName">Name</label>
          <input type="text" id="oName" name="name" maxlength="100" value="<?= e($form['name']) ?>" required>
        </div>
        <div class="field">
          <label for="oCategory">Category</label>
          <select id="oCategory" name="category">
            <?php foreach ($categories as $c): ?>
            <option value="<?= e($c) ?>" <?= $form['category'] == $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="field">
        <label for="oType">Organization type</label>
        <input type="text" id="oType" name="org_type" maxlength="50" placeholder="Academic Organization" value="<?= e($form['org_type']) ?>" required>
      </div>
      <div class="field">
        <label for="oDesc">Description</label>
        <textarea id="oDesc" name="description" rows="3" required><?= e($form['description']) ?></textarea>
      </div>
      <div class="field">
        <label for="oPres">President email</label>
        <input type="email" id="oPres" name="president_email" placeholder="first_last@dlsu.edu.ph" value="<?= e($form['president_email']) ?>" required>
        <div class="muted">Must be a registered student. If the org already has a president, they become a regular member.</div>
      </div>

      <button class="btn" type="submit"><?= $editId ? 'Save changes' : 'Add organization' ?></button>
      <?php if ($editId): ?>
        <a class="btn outline" href="index.php?page=adminorgs">Cancel</a>
      <?php endif; ?>
    </form>
  </div>

  <div class="card">
    <h3>Organizations (<?= count($orgList) ?>)</h3>
    <div class="tablewrap">
      <table>
        <tr><th>Name</th><th>Category</th><th>President</th><th>Members</th><th></th></tr>
        <?php foreach ($orgList as $o): ?>
        <tr>
          <td><?= e($o['name']) ?></td>
          <td><?= e($o['category']) ?></td>
          <td><?= e($o['president_name'] ?? '-') ?></td>
          <td><?= (int)$o['member_count'] ?></td>
          <td>
            <a class="btn small outline" href="index.php?page=adminorgs&amp;edit=<?= (int)$o['org_id'] ?>">Edit</a>
            <?php // delete is a POST form (not a link), admin.js asks "are you sure?" first. data-name is read by that script ?>
            <form class="deleteForm" method="post" action="index.php?page=deleteorg" data-name="<?= e($o['name']) ?>" style="display:inline">
              <input type="hidden" name="org_id" value="<?= (int)$o['org_id'] ?>">
              <button class="btn small red" type="submit">Delete</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (count($orgList) == 0): ?>
        <tr><td colspan="5" class="muted">No organizations yet.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
<script src="js/admin.js"></script>
<?php require 'views/layout/footer.php'; ?>
