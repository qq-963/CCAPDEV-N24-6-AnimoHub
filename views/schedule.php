<?php
$title = 'My Schedule';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Enlistment</h1>

  <div class="tabs">
    <a class="tab" href="index.php?page=enlistment">My Enlistment</a>

    <a class="tab" href="index.php?page=courses">Course Offerings</a>

    <a class="tab active" href="index.php?page=schedule">My Schedule</a>
  </div>

  <div class="card">
    <h3>Weekly schedule</h3>
    <?php require 'views/partials/schedule.php'; ?>
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
