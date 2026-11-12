<?php
// Single organization page, display part
// replaces the render() part of the old org.js. the data comes from OrgController::show()
//   $org       = the organization (with member_count)
//   $officers  = president, vice president, officers...
//   $myRole    = my role in this org, or null if i'm not a member
//   $canManage = true for admins and for presidents / officers
$title = $org['name'];
require 'views/layout/header.php';
?>
<div class="container">
  <a href="index.php?page=organizations">&larr; Back to organizations</a>
  <br><br>

  <section class="orgbanner" style="background:<?= e($org['color']) ?>">
    <span class="eyebrow"><?= e($org['category']) ?> / <?= e($org['org_type']) ?></span>
    <h1><?= e($org['name']) ?></h1>
    <p><?= (int)$org['member_count'] ?> members &middot; <?= $org['recruiting'] ? 'Recruiting' : 'Not recruiting' ?></p>

    <div class="org-actions">
      <?php if ($myRole): ?>
        <span class="badge <?= e($myRole['css']) ?>"><?= e($myRole['role_name']) ?></span> Active member
      <?php endif; ?>

      <?php if ($user['role'] == 'student'): ?>
        <?php if (!$myRole && $org['recruiting']): ?>
          <a class="btn outline contrast" href="#applyBox">Apply to Join</a>
        <?php endif; ?>

        <?php if ($myRole && $myRole['role_name'] == 'Member' && $org['officer_hiring']): ?>
          <a class="btn outline contrast" href="#officerBox">Apply for Officer</a>
        <?php endif; ?>

        <?php if ($myRole && $myRole['role_name'] != 'President'): ?>
          <a class="btn outline contrast" href="#resignBox">Request Resignation</a>
        <?php endif; ?>
      <?php endif; ?>

      <?php // the settings page belongs to Member 4, this button just links to it ?>
      <?php if ($canManage): ?>
        <a class="btn outline contrast" href="index.php?page=orgsettings&amp;id=<?= (int)$org['org_id'] ?>">Manage Organization</a>
        <a class="btn outline contrast" href="index.php?page=requests&amp;id=<?= (int)$org['org_id'] ?>">Review Requests</a>
      <?php endif; ?>
    </div>
  </section>

  <div class="grid org-stats">
    <div class="card stat"><div class="num"><?= (int)$org['member_count'] ?></div><div class="muted">Members</div></div>
    <div class="card stat"><div class="num"><?= count($officers) ?></div><div class="muted">Officers</div></div>
  </div>

  <div class="org-detail-grid">
    <div>
      <section class="card">
        <h2>About <?= e($org['name']) ?></h2>
        <p><?= e($org['description']) ?></p>
      </section>

      <?php if ($user['role'] == 'student'): ?>

        <?php if (!$myRole && $org['recruiting']): ?>
        <section class="card" id="applyBox">
          <h3>Apply to join</h3>
          <form method="POST" action="index.php?page=apply">
            <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
            <input type="hidden" name="type" value="member">
            <div class="field">
              <label for="applyReason">Why do you want to join?</label>
              <textarea id="applyReason" name="reason" rows="3" required></textarea>
            </div>
            <button class="btn" type="submit">Submit application</button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($myRole && $myRole['role_name'] == 'Member' && $org['officer_hiring']): ?>
        <section class="card" id="officerBox">
          <h3>Apply for officer</h3>
          <form method="POST" action="index.php?page=apply">
            <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
            <input type="hidden" name="type" value="officer">
            <div class="field">
              <label for="officerReason">Why do you want to be an officer?</label>
              <textarea id="officerReason" name="reason" rows="3" required></textarea>
            </div>
            <button class="btn" type="submit">Submit application</button>
          </form>
        </section>
        <?php endif; ?>

        <?php if ($myRole && $myRole['role_name'] != 'President'): ?>
        <section class="card" id="resignBox">
          <h3>Request resignation</h3>
          <form method="POST" action="index.php?page=resign">
            <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
            <div class="field">
              <label for="resignReason">Why do you want to leave?</label>
              <textarea id="resignReason" name="reason" rows="3" required></textarea>
            </div>
            <button class="btn red" type="submit">Send resignation form</button>
          </form>
        </section>
        <?php endif; ?>

      <?php endif; ?>

      <?php if ($myRole): ?>
      <section class="card">
        <h2>My Membership</h2>
        <div class="row"><span>Status: Active</span><span class="badge <?= e($myRole['css']) ?>"><?= e($myRole['role_name']) ?></span></div>
      </section>
      <?php endif; ?>

      <?php // TODO Member 4: the Announcements and Events cards go here ?>
    </div>

    <aside>
      <section class="card">
        <h3>Organization Information</h3>
        <div class="row"><span>College</span><b>College of Computer Studies</b></div>
        <div class="row"><span>Category</span><b><?= e($org['category']) ?></b></div>
        <div class="row"><span>Type</span><b><?= e($org['org_type']) ?></b></div>
        <div class="row"><span>Recruitment</span><b><?= $org['recruiting'] ? 'Open' : 'Closed' ?></b></div>
        <div class="row"><span>Officer hiring</span><b><?= $org['officer_hiring'] ? 'Open' : 'Closed' ?></b></div>
      </section>

      <section class="card">
        <h3>Organization Officers</h3>
        <?php foreach ($officers as $off): ?>
          <div class="row">
            <span><?= e($off['name']) ?></span>
            <span class="badge <?= e($off['css']) ?>"><?= e($off['role_name']) ?></span>
          </div>
        <?php endforeach; ?>
        <?php if (count($officers) == 0): ?>
          <p class="muted">No officers listed.</p>
        <?php endif; ?>
      </section>
    </aside>
  </div>
</div>
<script src="js/org.js"></script>
<?php require 'views/layout/footer.php'; ?>
