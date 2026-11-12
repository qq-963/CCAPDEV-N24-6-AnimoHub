<?php
// needs $scheduleRows from EnlistmentModel getSchedule
$dayNames = ['M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat'];
$times = ['08:00-09:30', '09:45-11:15', '11:30-13:00', '13:15-14:45', '15:00-16:30', '16:45-18:15'];
?>
<div class="tablewrap">
  <table class="sched">
    <tr>
      <th>Time</th>
      <?php foreach ($dayNames as $name): ?>
      <th><?= $name ?></th>
      <?php endforeach; ?>
    </tr>

    <?php foreach ($times as $t): ?>
    <tr>
      <td><?= $t ?></td>

      <?php foreach ($dayNames as $d => $name): ?>
      <td>
        <?php foreach ($scheduleRows as $r): ?>
          <?php if ($r['time_slot'] == $t && $r['day'] == $d): ?>
          <div class="cls <?= strtolower($r['mode']) ?>">
            <b><?= e($r['code']) ?></b> <?= e($r['section_code']) ?><br>
            <?= e($r['mode']) ?><br>
            <?php if ($r['mode'] == 'Online'): ?>
              Online<br>
            <?php else: ?>
              <?= e($r['room_code']) ?><br>
            <?php endif; ?>
            <?= e($r['prof']) ?>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
</div>

<p class="muted"><span class="badge Onsite">Onsite</span> <span class="badge Online">Online</span></p>
