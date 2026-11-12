<?php
$title = 'Admin';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Admin</h1>

  <div class="card">
    <h3>Create admin account</h3>
    <form method="post" action="index.php?page=admin">
      <div class="twocol">
        <div class="field"><label for="aName">Name</label><input type="text" id="aName" name="name" required></div>
        <div class="field"><label for="aEmail">Email</label><input type="email" id="aEmail" name="email" required></div>
      </div>
      <div class="field"><label for="aPass">Password</label><input type="password" id="aPass" name="password" required></div>
      <button class="btn" type="submit">Create</button>
    </form>
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
