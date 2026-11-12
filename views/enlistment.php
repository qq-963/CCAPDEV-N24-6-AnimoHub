<?php
$title = 'Enlistment';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Enlistment</h1>

  <div class="tabs">
    <a class="tab active" href="index.php?page=enlistment">My Enlistment</a>

    <a class="tab" href="index.php?page=courses">Course Offerings</a>

    <a class="tab" href="index.php?page=schedule">My Schedule</a>
  </div>

  <div class="card">
    <h3>Enlist a course</h3>

    <form method="POST" action="index.php?page=enlist">
      <input type="hidden" name="from" value="enlistment">

      <div class="field">
        <label for="secSel">Course and section</label>
        <select id="secSel" name="section_id">
          <?php foreach ($sections as $s): ?>
          <?php
            $days = '';
            foreach ($s['days'] as $d) $days .= $d['day'];
          ?>
          <option value="<?= $s['section_id'] ?>">
            <?= e($s['code']) ?> <?= e($s['section_code']) ?> | <?= $days ?> <?= e($s['time_slot']) ?> | <?= $s['slots'] ?> slots
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <button class="btn" type="submit">Enlist</button>
      <a class="btn outline" href="index.php?page=courses">Browse course offerings</a>
    </form>
  </div>

  <div class="card">
    <div class="row" style="border:none">
      <h3 style="margin:0">My enlisted courses</h3>
      <span class="badge ok">Total units: <?= $units ?> / <?= $maxUnits ?></span>
    </div>

    <div class="tablewrap">
      <table>
        <tr><th>Code</th><th>Course</th><th>Section</th><th>Schedule</th><th>Units</th><th></th></tr>

        <?php foreach ($enlisted as $e): ?>
        <tr>
          <td><?= e($e['code']) ?></td>
          <td><?= e($e['name']) ?></td>
          <td><?= e($e['section_code']) ?></td>
          <td><?= e($e['days']) ?> <?= e($e['time_slot']) ?></td>
          <td><?= $e['units'] ?></td>
          <td>
            <form method="POST" action="index.php?page=drop" onsubmit="return confirm('Drop <?= e($e['code']) ?>?')">
              <input type="hidden" name="section_id" value="<?= $e['section_id'] ?>">
              <button class="btn small red" type="submit">Drop</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($enlisted)): ?>
        <tr><td colspan="6" class="muted">You have not enlisted in any course yet.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
