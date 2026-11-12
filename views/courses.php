<?php
$title = 'Course Offerings';
$dayNames = ['M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat'];
require 'views/layout/header.php';
?>
<main class="container">
  <header class="page-header">
    <div>
      <h1>Course Offerings</h1>
      <p class="muted">Browse available subjects, schedules, and slots.</p>
    </div>

    <div class="header-actions">
      <a class="btn small outline" href="index.php?page=courses">&#8635; Refresh</a>
    </div>
  </header>

  <?php if ($user['role'] == 'student'): ?>
  <nav class="tabs">
    <a class="tab" href="index.php?page=enlistment">My Enlistment</a>

    <a class="tab active" href="index.php?page=courses">Course Offerings</a>

    <a class="tab" href="index.php?page=schedule">My Schedule</a>
  </nav>
  <?php endif; ?>

  <form class="filter-card" method="GET" action="index.php">
    <input type="hidden" name="page" value="courses">

    <div class="search-wrap">
      <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search by code, title, or instructor...">
    </div>

    <div class="filter-selects">
      <select name="mode">
        <option value="all">All Modes</option>
        <option value="Online" <?= $mode == 'Online' ? 'selected' : '' ?>>Online</option>
        <option value="Onsite" <?= $mode == 'Onsite' ? 'selected' : '' ?>>Onsite</option>
      </select>

      <select name="avail">
        <option value="all">All Status</option>
        <option value="open" <?= $avail == 'open' ? 'selected' : '' ?>>Available Slots</option>
        <option value="full" <?= $avail == 'full' ? 'selected' : '' ?>>Full / Closed</option>
      </select>

      <button class="btn small" type="submit">Search</button>
    </div>
  </form>

  <?php foreach ($courses as $c): ?>
  <div class="card">
    <h3><?= e($c['code']) ?> - <?= e($c['name']) ?> <span class="muted">(<?= $c['units'] ?> units)</span></h3>

    <div class="tablewrap">
      <table>
        <tr><th>Section</th><th>Days / Mode</th><th>Time</th><th>Room</th><th>Professor</th><th>Slots</th><th></th></tr>

        <?php foreach ($c['sections'] as $s): ?>
        <tr>
          <td><?= e($s['section_code']) ?></td>

          <td>
            <?php foreach ($s['days'] as $d): ?>
              <?= $dayNames[$d['day']] ?> <span class="badge <?= $d['mode'] ?>"><?= $d['mode'] ?></span><br>
            <?php endforeach; ?>
          </td>

          <td><?= e($s['time_slot']) ?></td>
          <td><?= e($s['room_code']) ?></td>
          <td><?= e($s['prof']) ?></td>
          <td><span class="badge <?= $s['slots'] > 0 ? 'ok' : 'full' ?>"><?= $s['slots'] ?>/<?= $s['max_slots'] ?></span></td>

          <td>
            <?php if ($user['role'] == 'student'): ?>
              <?php if (in_array($s['section_id'], $enlistedIds)): ?>
                <span class="badge ok">Enlisted</span>
              <?php elseif ($s['slots'] > 0): ?>
                <form method="POST" action="index.php?page=enlist">
                  <input type="hidden" name="section_id" value="<?= $s['section_id'] ?>">
                  <input type="hidden" name="from" value="courses">
                  <button class="btn small" type="submit">Enlist</button>
                </form>
              <?php else: ?>
                <button class="btn small" disabled>Full</button>
              <?php endif; ?>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
  <?php endforeach; ?>

  <?php if (empty($courses)): ?>
  <div class="card muted">No courses found.</div>
  <?php endif; ?>
</main>
<?php require 'views/layout/footer.php'; ?>
