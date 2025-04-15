<?php
// config.php

// Database configuration settings
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');   // Replace with your database username
define('DB_PASSWORD', '');   // Replace with your database password
define('DB_NAME', 'foodiehub');

// Create the MySQLi connection
$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
date_default_timezone_set('Asia/Kolkata');

?>
