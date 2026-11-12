<?php
$title = 'Profile';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Profile</h1>

  <div class="tabs">
    <a class="tab <?= $tab == 'info' ? 'active' : '' ?>" href="index.php?page=profile&amp;tab=info">Information</a>

    <a class="tab <?= $tab == 'sched' ? 'active' : '' ?>" href="index.php?page=profile&amp;tab=sched">Schedule</a>

    <a class="tab <?= $tab == 'orgs' ? 'active' : '' ?>" href="index.php?page=profile&amp;tab=orgs">My Organizations</a>
  </div>

  <?php if ($tab == 'info'): ?>
  <div class="card">
    <form method="POST" action="index.php?page=saveprofile">
      <div class="field">
        <label for="pName">Name</label>
        <input type="text" id="pName" name="name" value="<?= e($user['name']) ?>" maxlength="100">
      </div>

      <div class="field">
        <label for="pEmail">Email</label>
        <input type="text" id="pEmail" value="<?= e($user['email']) ?>" disabled>
      </div>

      <div class="field">
        <label for="pRole">Account type</label>
        <input type="text" id="pRole" value="<?= e($user['role']) ?>" disabled>
      </div>

      <button class="btn" type="submit">Save changes</button>
    </form>
  </div>
  <?php endif; ?>

  <?php if ($tab == 'sched'): ?>
  <div class="card">
    <h3>Weekly schedule</h3>
    <?php if ($user['role'] == 'student'): ?>
      <?php require 'views/partials/schedule.php'; ?>
    <?php else: ?>
      <p class="muted">Schedules are only available for student accounts.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($tab == 'orgs'): ?>
  <div class="card">
    <h3>My organizations</h3>

    <?php foreach ($myOrgs as $o): ?>
    <div class="row">
      <a href="index.php?page=org&amp;id=<?= (int)$o['org_id'] ?>"><?= e($o['name']) ?></a>
      <span class="badge <?= e($o['css']) ?>"><?= e($o['role_name']) ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (empty($myOrgs)): ?>
    <p class="muted">You have not joined any organization yet.</p>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>
<?php require 'views/layout/footer.php'; ?>
