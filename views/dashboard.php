<?php
$title = 'Dashboard';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Welcome back, <?= e($user['name']) ?>!</h1>
</div>
<?php require 'views/layout/footer.php'; ?>
