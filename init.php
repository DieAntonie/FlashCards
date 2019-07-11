<?php
// Report all PHP errors (see changelog)
error_reporting(E_ALL);

// Report all PHP errors
error_reporting(-1);

session_start();

require './Query/Query.php';

//temporary, while on localhost
$url = 'localhost/flashcards';

$config = parse_ini_file('config.ini');

$host = $config["app_url"];
$username = $config["db_user"];
$passwd = $config["db_password"];
$dbname = $config["db_name"];

$mysqli = new mysqli($host, $username, $passwd, $dbname);

$query = new Query($mysqli);