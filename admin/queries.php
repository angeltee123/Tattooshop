<?php
session_name("sess_id");
session_start();
require_once '../api/api.php';
$api = new api();

/******** RESERVATION TABLE UPDATE AND DELETE ********/

if(isset($_POST['update_item'])){
}

if(isset($_POST['remove_item'])){   
}

/******** WORKORDER TABLE UPDATE AND DELETE ********/

if(isset($_POST['update_item'])){
}

if(isset($_POST['remove_item'])){   
}

/******** CLIENT TABLE UPDATE AND DELETE ********/

if(isset($_POST['signup'])){
}

if(isset($_POST['login'])){
}

/******** USER TABLE UPDATE AND DELETE ********/

if(isset($_POST['update_item'])){
}

if(isset($_POST['remove_item'])){   
}

/******** ORDER MANAGEMENT ********/

/******** BOOKING MANAGEMENT ********/

/******** ILLEGAL ACCESS CATCHING ********/

if(empty($_POST)){
    Header("Location: admin.php");
    die();
}
?>