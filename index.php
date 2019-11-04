<?php
declare(strict_types=1);
session_start();
error_reporting(-1);
ini_set('display_errors', 'On');

require_once __DIR__ . '/bootstrap.php';
echo router($_SERVER['REQUEST_URI']);