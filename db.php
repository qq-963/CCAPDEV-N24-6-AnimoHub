<?php
// database connection, change these if your mysql is different
$host = 'localhost';
$dbname = 'animohub';
$dbuser = 'root';
$dbpass = '';

$db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $dbuser, $dbpass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
]);
