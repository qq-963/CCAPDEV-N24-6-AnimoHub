<?php
// router, every page goes through here
session_start();
require 'config/db.php';

function e($s) { return htmlspecialchars($s ?? ''); }

foreach (glob('models/*.php') as $f) require_once $f;
require_once 'controllers/Controller.php';
foreach (glob('controllers/*.php') as $f) require_once $f;

// page => [controller, method], add your routes here
$routes = [
    'login' => ['AuthController', 'login'],
    'signup' => ['AuthController', 'signup'],
    'logout' => ['AuthController', 'logout'],
    'admin' => ['AuthController', 'admin'],
    'dashboard' => ['DashboardController', 'index'],
    'organizations' => ['OrgController', 'index'],
    'org' => ['OrgController', 'show'],
    'adminorgs' => ['AdminOrgController', 'index'],
    'saveorg' => ['AdminOrgController', 'save'],
    'deleteorg' => ['AdminOrgController', 'delete'],
    'courses' => ['CourseController', 'index'],
    'enlistment' => ['EnlistmentController', 'index'],
    'schedule' => ['EnlistmentController', 'schedule'],
    'enlist' => ['EnlistmentController', 'enlist'],
    'drop' => ['EnlistmentController', 'drop'],
    'profile' => ['ProfileController', 'index'],
    'saveprofile' => ['ProfileController', 'save'],
    'apply' => ['RequestController', 'apply'],
    'resign' => ['RequestController', 'resign'],
    'requests' => ['RequestController', 'index'],
    'decide' => ['RequestController', 'decide']
];

$page = $_GET['page'] ?? 'login';
if (!isset($routes[$page])) $page = 'dashboard';

$c = $routes[$page][0];
$m = $routes[$page][1];
$controller = new $c($db);
$controller->$m();
