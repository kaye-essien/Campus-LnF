<?php
$conn = mysqli_init();
$conn->real_connect('sql308.infinityfree.com', 'if0_42275581', '88kayeessieN', 'if0_42275581_campus_lnf', 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
