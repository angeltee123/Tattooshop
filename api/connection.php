<?php
session_start();

$server="localhost";
$user="root";
$password="";
$db="njctattoodb";

$conn=mysqli_connect($server, $user, $password, $db);

if(!$conn){
    die("Failed to establish connection. ".mysqli_connect_error());
}
?>