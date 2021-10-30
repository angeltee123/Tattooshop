<?php
session_start();

$server="localhost";
$user="root";
$password="";
$db="njctattoodb";

$conn= new mysqli($server, $user, $password, $db);

if($conn->connect_error){
    die("Failed to establish connection. Error code " . $conn->connect_errno . " - " . $conn->connect_error );
}
?>