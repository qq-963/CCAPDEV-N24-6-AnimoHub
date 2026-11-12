<?php
$cur = $_GET['page'] ?? '';
function lk($p, $t) {
    global $cur;
    return '<a href="index.php?page=' . $p . '"' . ($cur == $p ? ' class="active"' : '') . '>' . $t . '</a>';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>AnimoHub - <?= e($title ?? 'Home') ?></title>
<link rel="stylesheet" href="css/style.css">
</head>
<body class="<?= $bodyClass ?? '' ?>">
<?php if ($user): ?>
<div class="nav">
  <button id="menuBtn" onclick="toggleMenu()">&#9776;</button>
  <a class="logo" href="index.php?page=dashboard">AnimoHub</a>
  <div class="links">
    <?= lk('dashboard', 'Dashboard') ?>
    <?php if ($user['role'] == 'student'): ?>
    <div class="dropdown"><a href="#" class="dropbtn">Enlistment &#9662;</a>
      <div class="dropcontent"><?= lk('enlistment', 'My Enlistment') . lk('courses', 'Course Offerings') ?></div>
    </div>
    <?php else: echo lk('courses', 'Course Offerings'); endif; ?>
    <?= lk('organizations', 'Organizations') ?>
    <?php if ($manages) echo lk('orgsettings', 'Org Settings'); ?>
    <?php if ($user['role'] == 'admin') echo lk('admin', 'Admin'); ?>
    <?= lk('profile', 'Profile') ?>
  </div>
  <div class="right">
    <span class="who"><?= e($user['name']) ?></span>
    <button id="themeBtn" onclick="toggleTheme()">Dark mode</button>
    <form method="post" action="index.php?page=logout"><button type="submit">Logout</button></form>
  </div>
</div>
<?php endif; ?>
<div id="toasts">
<?php if (isset($_SESSION['flash'])): ?>
  <div class="toast <?= e($_SESSION['flash'][0]) ?>"><?= e($_SESSION['flash'][1]) ?></div>
<?php unset($_SESSION['flash']); endif; ?>
</div>
