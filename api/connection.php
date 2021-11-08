<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$server="localhost";
$user="root";
$password="";
$db="njctattoodb";
$port = 3306;

$conn = new mysqli($server, $user, $password, $password, $port);
$conn->connect_error ? die("Failed to establish connection. Error code " . $conn->connect_errno . " - " . $conn->connect_error ) : $conn->set_charset('utf8mb4');

unset($server, $user, $password, $db, $port);
?>