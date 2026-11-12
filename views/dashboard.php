<?php
$title = 'Dashboard';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Welcome back, <?= e($user['name']) ?>!</h1>

  <div class="grid stats">
    <?php foreach ($cards as $c): ?>
    <div class="card stat hover">
      <div class="num"><?= $c[1] ?></div>
      <div class="muted"><?= e($c[0]) ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="twocol">
    <div class="card">
      <h3><?= e($title1) ?></h3>
      <div id="chart1"></div>
    </div>

    <div class="card">
      <h3>Open slots per course</h3>
      <div id="chart2"></div>
    </div>
  </div>

  <div class="card">
    <h3>Upcoming events</h3>

    <?php foreach ($events as $ev): ?>
    <div class="row">
      <div>
        <b><?= e($ev['title']) ?></b><br>
        <span class="muted"><a href="index.php?page=org&amp;id=<?= (int)$ev['org_id'] ?>"><?= e($ev['org_name']) ?></a> | <?= e($ev['place']) ?></span>
      </div>
      <span class="badge ok"><?= e($ev['date']) ?></span>
    </div>
    <?php endforeach; ?>

    <?php if (empty($events)): ?>
    <p class="muted">No upcoming events.</p>
    <?php endif; ?>
  </div>
</div>

<script>
// drawBars is in main.js, that file loads after this so we wait for the page
document.addEventListener('DOMContentLoaded', function() {
  drawBars('chart1', <?= json_encode($labels1) ?>, <?= json_encode($values1) ?>);
  drawBars('chart2', <?= json_encode($labels2) ?>, <?= json_encode($values2) ?>);
});
</script>
<?php require 'views/layout/footer.php'; ?>
