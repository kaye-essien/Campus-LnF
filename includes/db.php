<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'ceiscy_eg361');
define('DB_PASS', '4#Tjh(4&<jhE');
define('DB_NAME', 'ceiscy_eg361');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
