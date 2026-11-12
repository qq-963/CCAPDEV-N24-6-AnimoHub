<?php
$title = 'Requests';
require 'views/layout/header.php';
?>
<div class="container">
  <a href="index.php?page=org&amp;id=<?= (int)$org['org_id'] ?>">&larr; Back to <?= e($org['name']) ?></a>
  <br><br>

  <h1>Requests of <?= e($org['name']) ?></h1>

  <div class="card">
    <h3>Applications</h3>

    <?php foreach ($applications as $a): ?>
    <div class="row">
      <div>
        <b><?= e($a['name']) ?></b>
        <span class="badge <?= e(strtolower($a['role_name'])) ?>"><?= e($a['role_name']) ?></span><br>
        <span class="muted"><?= e($a['reason']) ?></span>
      </div>

      <div>
        <form method="POST" action="index.php?page=decide" style="display:inline">
          <input type="hidden" name="kind" value="application">
          <input type="hidden" name="id" value="<?= (int)$a['application_id'] ?>">
          <input type="hidden" name="answer" value="yes">
          <button class="btn small" type="submit">Approve</button>
        </form>

        <form method="POST" action="index.php?page=decide" style="display:inline">
          <input type="hidden" name="kind" value="application">
          <input type="hidden" name="id" value="<?= (int)$a['application_id'] ?>">
          <input type="hidden" name="answer" value="no">
          <button class="btn small red" type="submit">Reject</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($applications)): ?>
    <p class="muted">No pending applications.</p>
    <?php endif; ?>
  </div>

  <div class="card">
    <h3>Resignation forms</h3>

    <?php foreach ($resignations as $r): ?>
    <div class="row">
      <div>
        <b><?= e($r['name']) ?></b><br>
        <span class="muted"><?= e($r['reason']) ?></span>
      </div>

      <div>
        <form method="POST" action="index.php?page=decide" style="display:inline">
          <input type="hidden" name="kind" value="resignation">
          <input type="hidden" name="id" value="<?= (int)$r['resignation_id'] ?>">
          <input type="hidden" name="answer" value="yes">
          <button class="btn small" type="submit">Accept</button>
        </form>

        <form method="POST" action="index.php?page=decide" style="display:inline">
          <input type="hidden" name="kind" value="resignation">
          <input type="hidden" name="id" value="<?= (int)$r['resignation_id'] ?>">
          <input type="hidden" name="answer" value="no">
          <button class="btn small red" type="submit">Decline</button>
        </form>
      </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($resignations)): ?>
    <p class="muted">No resignation forms.</p>
    <?php endif; ?>
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
