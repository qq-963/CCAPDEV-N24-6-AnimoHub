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

      <?php // TODO Member 5: the "Apply to Join", "Apply for Officer" and "Request Resignation" buttons go here ?>

      <?php // the settings page belongs to Member 4, this button just links to it ?>
      <?php if ($canManage): ?>
        <a class="btn outline contrast" href="index.php?page=orgsettings&amp;id=<?= (int)$org['org_id'] ?>">Manage Organization</a>
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
