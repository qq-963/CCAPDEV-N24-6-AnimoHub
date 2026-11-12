<?php
define('MAX_UNITS', 24);
define('DAYS', ['M' => 'Mon', 'T' => 'Tue', 'W' => 'Wed', 'H' => 'Thu', 'F' => 'Fri', 'S' => 'Sat']);
define('TIMES', ['08:00-09:30', '09:45-11:15', '11:30-13:00', '13:15-14:45', '15:00-16:30', '16:45-18:15']);

function e($s) { return htmlspecialchars((string)($s ?? '')); }

function dlsuEmail($email) { return preg_match('/^[a-z0-9._-]+@dlsu\.edu\.ph$/', $email); }

// css class for the role badge
function roleClass($role) {
    $r = strtolower($role);
    return in_array($r, ['president', 'member']) ? $r : 'officer';
}

// bar chart rows
function bars($labels, $values) {
    $max = max(array_merge([1], $values));
    $h = '';
    foreach ($labels as $i => $l) {
        $h .= '<div class="barrow"><span class="barlabel">' . e($l) . '</span><div class="bartrack"><div class="barfill" data-w="' . ($values[$i] / $max * 100) . '"></div></div><span class="barval">' . $values[$i] . '</span></div>';
    }
    return $h;
}
