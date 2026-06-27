<?php
define('DB_HOST', 'sql308.infinityfree.com');
define('DB_USER', 'if0_42275581');
define('DB_PASS', '88kayeessieN');
define('DB_NAME', 'if0_42275581_campus_lnf');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
