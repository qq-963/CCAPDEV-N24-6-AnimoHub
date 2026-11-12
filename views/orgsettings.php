<?php
// org settings page (member 4). the data comes from OrgController::settings()
//   $org, $myOrgs (orgs i can manage), $events, $announcements, $members, $isPres
$title = 'Org Settings';
require 'views/layout/header.php';
?>
<div class="container">
  <h1>Organization Settings</h1>
  <p class="muted">Manage your organization's info, events, announcements and members.</p>

  <?php // pick which org to manage, GET because it only changes what we look at ?>
  <form method="GET" action="index.php" class="card">
    <input type="hidden" name="page" value="orgsettings">
    <div class="field">
      <label for="orgPick">Organization</label>
      <select id="orgPick" name="id">
        <?php foreach ($myOrgs as $o): ?>
        <option value="<?= (int)$o['org_id'] ?>" <?= $o['org_id'] == $org['org_id'] ? 'selected' : '' ?>><?= e($o['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button class="btn small" type="submit">Open</button>
    <a class="btn small outline" href="index.php?page=org&amp;id=<?= (int)$org['org_id'] ?>">View org page</a>
    <a class="btn small outline" href="index.php?page=requests&amp;id=<?= (int)$org['org_id'] ?>">Review requests</a>
  </form>

  <?php // ---------- info ---------- ?>
  <div class="card">
    <h3>Organization info</h3>
    <form method="POST" action="index.php?page=saveinfo">
      <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">

      <div class="field">
        <label for="infoDesc">Description</label>
        <textarea id="infoDesc" name="description" rows="3" required><?= e($org['description']) ?></textarea>
      </div>

      <div class="field">
        <label for="infoColor">Page color</label>
        <input type="color" id="infoColor" name="color" value="<?= e($org['color']) ?>">
      </div>

      <div class="field">
        <label><input type="checkbox" name="recruiting" <?= $org['recruiting'] ? 'checked' : '' ?>> Open for member applications</label>
      </div>

      <div class="field">
        <label><input type="checkbox" name="officer_hiring" <?= $org['officer_hiring'] ? 'checked' : '' ?>> Open for officer applications</label>
      </div>

      <button class="btn" type="submit">Save info</button>
    </form>
  </div>

  <?php // ---------- events ---------- ?>
  <div class="card">
    <h3>Events</h3>

    <?php foreach ($events as $ev): ?>
    <div class="row">
      <form method="POST" action="index.php?page=event" style="flex:1">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
        <div class="twocol">
          <div class="field"><label>Title</label><input type="text" name="title" maxlength="150" value="<?= e($ev['title']) ?>" required></div>
          <div class="field"><label>Place</label><input type="text" name="place" maxlength="150" value="<?= e($ev['place']) ?>" required></div>
          <div class="field"><label>Date</label><input type="date" name="date" value="<?= e($ev['date']) ?>" required></div>
          <div class="field"><label>Time</label><input type="time" name="time" value="<?= e($ev['time']) ?>"></div>
        </div>
        <button class="btn small" type="submit">Save</button>
      </form>

      <form method="POST" action="index.php?page=event" onsubmit="return confirm('Delete this event?')">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="event_id" value="<?= (int)$ev['event_id'] ?>">
        <button class="btn small red" type="submit">Delete</button>
      </form>
    </div>
    <?php endforeach; ?>

    <?php if (empty($events)): ?>
    <p class="muted">No events yet.</p>
    <?php endif; ?>

    <h3>Add an event</h3>
    <form method="POST" action="index.php?page=event">
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
      <div class="twocol">
        <div class="field"><label for="evTitle">Title</label><input type="text" id="evTitle" name="title" maxlength="150" required></div>
        <div class="field"><label for="evPlace">Place</label><input type="text" id="evPlace" name="place" maxlength="150" required></div>
        <div class="field"><label for="evDate">Date</label><input type="date" id="evDate" name="date" required></div>
        <div class="field"><label for="evTime">Time</label><input type="time" id="evTime" name="time"></div>
      </div>
      <button class="btn" type="submit">Add event</button>
    </form>
  </div>

  <?php // ---------- announcements ---------- ?>
  <div class="card">
    <h3>Announcements</h3>

    <?php foreach ($announcements as $an): ?>
    <div class="row">
      <form method="POST" action="index.php?page=announce" style="flex:1">
        <input type="hidden" name="_method" value="PUT">
        <input type="hidden" name="announcement_id" value="<?= (int)$an['announcement_id'] ?>">
        <div class="field"><label>Title</label><input type="text" name="title" maxlength="150" value="<?= e($an['title']) ?>" required></div>
        <div class="field"><label>Message</label><textarea name="content" rows="2" required><?= e($an['content']) ?></textarea></div>
        <div class="field">
          <label>Who can see it</label>
          <select name="visibility">
            <option value="public" <?= $an['visibility'] == 'public' ? 'selected' : '' ?>>Everyone</option>
            <option value="members" <?= $an['visibility'] == 'members' ? 'selected' : '' ?>>Members only</option>
          </select>
        </div>
        <span class="muted">Posted <?= e($an['publish_date']) ?></span>
        <button class="btn small" type="submit">Save</button>
      </form>

      <form method="POST" action="index.php?page=announce" onsubmit="return confirm('Delete this announcement?')">
        <input type="hidden" name="_method" value="DELETE">
        <input type="hidden" name="announcement_id" value="<?= (int)$an['announcement_id'] ?>">
        <button class="btn small red" type="submit">Delete</button>
      </form>
    </div>
    <?php endforeach; ?>

    <?php if (empty($announcements)): ?>
    <p class="muted">No announcements yet.</p>
    <?php endif; ?>

    <h3>Post an announcement</h3>
    <form method="POST" action="index.php?page=announce">
      <input type="hidden" name="_method" value="POST">
      <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
      <div class="field"><label for="anTitle">Title</label><input type="text" id="anTitle" name="title" maxlength="150" required></div>
      <div class="field"><label for="anContent">Message</label><textarea id="anContent" name="content" rows="3" required></textarea></div>
      <div class="field">
        <label for="anVis">Who can see it</label>
        <select id="anVis" name="visibility">
          <option value="public">Everyone</option>
          <option value="members">Members only</option>
        </select>
      </div>
      <button class="btn" type="submit">Post</button>
    </form>
  </div>

  <?php // ---------- members ---------- ?>
  <div class="card">
    <h3>Members (<?= count($members) ?>)</h3>
    <?php if (!$isPres): ?>
    <p class="muted">Only the president can change roles or remove members.</p>
    <?php endif; ?>

    <?php foreach ($members as $m): ?>
    <div class="row">
      <div>
        <b><?= e($m['name']) ?></b>
        <span class="badge <?= $m['role_name'] == 'President' ? 'president' : ($m['can_manage'] ? 'officer' : 'member') ?>"><?= e($m['role_name']) ?></span>
      </div>

      <?php if ($isPres && $m['role_name'] != 'President'): ?>
      <div>
        <form method="POST" action="index.php?page=changerole" style="display:inline">
          <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
          <input type="hidden" name="student_id" value="<?= (int)$m['student_id'] ?>">
          <?php if ($m['role_name'] == 'Member'): ?>
            <input type="hidden" name="role" value="officer">
            <button class="btn small" type="submit">Make officer</button>
          <?php else: ?>
            <input type="hidden" name="role" value="member">
            <button class="btn small outline" type="submit">Make member</button>
          <?php endif; ?>
        </form>

        <form method="POST" action="index.php?page=removemember" style="display:inline" onsubmit="return confirm('Remove this member?')">
          <input type="hidden" name="org_id" value="<?= (int)$org['org_id'] ?>">
          <input type="hidden" name="student_id" value="<?= (int)$m['student_id'] ?>">
          <button class="btn small red" type="submit">Remove</button>
        </form>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>

    <?php if (empty($members)): ?>
    <p class="muted">No members yet.</p>
    <?php endif; ?>
  </div>
</div>
<?php require 'views/layout/footer.php'; ?>
