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
    'dashboard' => ['DashboardController', 'index']
];

$page = $_GET['page'] ?? 'login';
if (!isset($routes[$page])) $page = 'dashboard';

$c = $routes[$page][0];
$m = $routes[$page][1];
$controller = new $c($db);
$controller->$m();
