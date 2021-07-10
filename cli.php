<?php

if ($_SERVER['argc'] !== 3) {
	die('Usage: cli.php <user> <pass>' . PHP_EOL);
}

$user = $_SERVER['argv'][1];
$pass = $_SERVER['argv'][2];

require_once('Database.php');
Database::initDB($user, $pass);
