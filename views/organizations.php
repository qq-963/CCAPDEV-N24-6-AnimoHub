<?php
// Organizations catalogue page
// replaces organizations.html. the data comes from OrgController::index()
// e() is the helper from index.php, it escapes text so users can't inject html into the page
$title = 'Organizations';
require 'views/layout/header.php';
?>
<div class="container">
  <div class="section-heading">
    <span class="eyebrow">CCS COMMUNITY</span>
    <h1>Find your organization.</h1>
    <p class="muted">Explore College of Computer Studies organizations, events and recruitment opportunities.</p>
  </div>

  <?php // the carousel is only shown if at least one org is recruiting ?>
  <?php if (count($featured) > 0): ?>
  <div class="carousel" id="carousel">
    <div id="slides">
      <?php foreach ($featured as $o): ?>
      <div class="slide" style="background:<?= e($o['color']) ?>">
        <p>Now recruiting</p>
        <h2><?= e($o['name']) ?></h2>
        <p><?= e($o['short']) ?></p>
        <a class="btn" href="index.php?page=org&amp;id=<?= (int)$o['org_id'] ?>">View organization</a>
      </div>
      <?php endforeach; ?>
    </div>
    <button class="prev" type="button">&#10094;</button>
    <button class="next" type="button">&#10095;</button>
  </div>
  <?php endif; ?>

  <?php // search + filters. method="get" because we are only reading data, the values end up in the url ?>
  <form class="filters" method="get" action="index.php">
    <input type="hidden" name="page" value="organizations">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search organizations..." aria-label="Search organizations">

    <select name="category" aria-label="Filter by category">
      <option value="all">All categories</option>
      <?php foreach ($categories as $c): ?>
      <option value="<?= e($c) ?>" <?= $category == $c ? 'selected' : '' ?>><?= e($c) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="recruiting" aria-label="Filter by recruitment">
      <option value="all" <?= $recruiting == 'all' ? 'selected' : '' ?>>All organizations</option>
      <option value="open" <?= $recruiting == 'open' ? 'selected' : '' ?>>Recruiting</option>
      <option value="closed" <?= $recruiting == 'closed' ? 'selected' : '' ?>>Not recruiting</option>
    </select>

    <button class="btn" type="submit">Search</button>
    <a class="btn outline" href="index.php?page=organizations">Clear</a>
  </form>

  <div class="row" style="border:none">
    <h2>Student Organizations</h2>
    <span class="muted" aria-live="polite"><?= count($list) ?> organization<?= count($list) == 1 ? '' : 's' ?> found</span>
  </div>

  <div class="grid">
    <?php foreach ($list as $o): ?>
    <?php // $mine is my role in this org, or null if i'm not a member ?>
    <?php $mine = $myRoles[$o['org_id']] ?? null; ?>
    <div class="card hover">
      <div class="stripe" style="background:<?= e($o['color']) ?>"></div>
      <div class="muted"><?= e($o['category']) ?> &middot; <?= e($o['org_type']) ?></div>
      <h3><?= e($o['name']) ?></h3>
      <?php if ($mine): ?>
        <span class="badge <?= e($mine['css']) ?>"><?= e($mine['role_name']) ?></span>
      <?php endif; ?>
      <p class="muted"><?= e($o['short']) ?></p>
      <p>
        <span class="badge <?= $o['recruiting'] ? 'ok' : 'full' ?>"><?= $o['recruiting'] ? 'Recruiting' : 'Closed' ?></span>
        <span class="muted"><?= (int)$o['member_count'] ?> members</span>
      </p>
      <a class="btn" href="index.php?page=org&amp;id=<?= (int)$o['org_id'] ?>">View</a>
    </div>
    <?php endforeach; ?>

    <?php if (count($list) == 0): ?>
    <div class="card muted">No organizations found.</div>
    <?php endif; ?>
  </div>
</div>
<script src="js/orgs.js"></script>
<?php require 'views/layout/footer.php'; ?>
