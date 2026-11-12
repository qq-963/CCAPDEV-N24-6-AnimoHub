<?php
$title = $tab == 'login' ? 'Login' : 'Sign Up';
$bodyClass = 'loginpage';
require 'views/layout/header.php';
?>
<button id="themeBtn" class="btn outline small themefloat" onclick="toggleTheme()">Dark mode</button>

<div class="authbox">
  <h1>AnimoHub</h1>
  <div class="card">
    <div class="tabs">
      <a class="tab <?= $tab == 'login' ? 'active' : '' ?>" href="index.php?page=login">Login</a>
      <a class="tab <?= $tab == 'signup' ? 'active' : '' ?>" href="index.php?page=signup">Sign Up</a>
    </div>

    <?php if ($tab == 'login'): ?>
    <form method="post" action="index.php?page=login">
      <div class="field">
        <label for="liEmail">DLSU Email</label>
        <input type="email" id="liEmail" name="email" placeholder="first_last@dlsu.edu.ph" required>
      </div>
      <div class="field">
        <label for="liPass">Password</label>
        <input type="password" id="liPass" name="password" required>
      </div>
      <button class="btn" type="submit" style="width:100%">Login</button>
    </form>
    <?php else: ?>
    <form method="post" action="index.php?page=signup">
      <div class="field">
        <label for="suName">Full Name</label>
        <input type="text" id="suName" name="name" value="<?= e($_POST['name'] ?? '') ?>" required>
      </div>
      <div class="field">
        <label for="suEmail">DLSU Email</label>
        <input type="email" id="suEmail" name="email" value="<?= e($_POST['email'] ?? '') ?>" oninput="checkEmail()" placeholder="first_last@dlsu.edu.ph" required>
        <div class="muted" id="roleHint"></div>
      </div>
      <div class="field">
        <label for="suPass">Password</label>
        <input type="password" id="suPass" name="password" required>
      </div>
      <div class="field">
        <label for="suPass2">Confirm Password</label>
        <input type="password" id="suPass2" name="password2" required>
      </div>
      <button class="btn" type="submit" style="width:100%">Create Account</button>
    </form>
    <script>
    function checkEmail() {
      var email = document.getElementById('suEmail').value.trim().toLowerCase();
      var local = email.split('@')[0];
      var hint = document.getElementById('roleHint');
      var ok = /^[a-z0-9._-]+@dlsu\.edu\.ph$/.test(email);
      if (email == '') hint.innerText = '';
      else if (!ok || (local.indexOf('_') == -1 && local.indexOf('.') == -1)) hint.innerText = 'Use your DLSU email (student: first_last@dlsu.edu.ph, professor: first.last@dlsu.edu.ph)';
      else hint.innerText = 'Detected account type: ' + (local.indexOf('_') != -1 ? 'student' : 'professor');
    }
    checkEmail();
    </script>
    <?php endif; ?>
  </div>

  <div class="card muted">
    <b>Demo accounts</b><br>
    Admin: admin@dlsu.edu.ph / admin123<br>
    President: juan_delacruz@dlsu.edu.ph / pass123<br>
    Officer: maria_santos@dlsu.edu.ph / pass123<br>
    Member: pedro_reyes@dlsu.edu.ph / pass123<br>
    Non-member: ana_lim@dlsu.edu.ph / pass123<br>
    Professor: jose.rizal@dlsu.edu.ph / pass123
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
