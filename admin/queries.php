<?php
session_name("sess_id");
session_start();
require_once '../api/api.php';
$api = new api();

/******** RESERVATION TABLE UPDATE AND DELETE ********/

// if(isset($_POST['update_item'])){
// }

// if(isset($_POST['remove_item'])){
// }

/******** WORKORDER TABLE UPDATE AND DELETE ********/

// if(isset($_POST['update_item'])){
// }

// if(isset($_POST['remove_item'])){   
// }

/******** CLIENT TABLE UPDATE AND DELETE ********/

if(isset($_POST['update_client'])){
    if(isset($_POST['item'])){
        foreach($_POST['item'] as $item){
            $errors = array();
            $index = array_search($item, $_POST['client_id']);

            $id = $api->clean($item);
            $client_fname = $api->clean(ucfirst($_POST['client_fname'][$index]));
            $client_mi = strtoupper($_POST['client_mi'][$index]);
            $client_lname = $api->clean(ucfirst($_POST['client_lname'][$index]));
            $age = intval($_POST['age'][$index]);
            $home_address = $api->clean($_POST['home_address'][$index]);
            $contact_number = $api->clean($_POST['contact_number'][$index]);

            // client_fname validation
            if(empty($client_fname)) {
                array_push($errors, "client_fname is required.");
            }
            
            elseif (mb_strlen($client_fname) < 2) {
                array_push($errors, "client_fname must be at least 2 characters long.");
            }
            
            elseif(ctype_space($client_fname) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $client_fname)){
                array_push($errors, "client_fname must not contain any spaces or special characters.");
            }

            // client_mi validation
            if(empty($client_mi)) {
                array_push($errors, "client_mi is required.");
            }
            
            elseif (mb_strlen($client_mi) > 1) {
                array_push($errors, "client_mi must be a character long.");
            }
            
            elseif(!ctype_alpha($client_mi) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $client_mi)){
                array_push($errors, "client_mi must not contain any spaces or special characters.");
            }

            // client_lname validation
            if(empty($client_lname)) {
                array_push($errors, "client_lname is required.");
            }
            
            elseif (mb_strlen($client_lname) < 2) {
                array_push($errors, "client_lname must be at least 2 characters long.");
            }
            
            elseif(ctype_space($client_lname) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $client_lname)){
                array_push($errors, "client_lname must not contain any spaces or special characters");
            }

            // age validation
            if(empty($age)) {
                array_push($errors, "age is required.");
            }

            if(!is_int($age)) {
                array_push($errors, "age must be an integer.");
            }

            // home_address validation
            if(empty($home_address)) {
                array_push($errors, "home_address is required.");
            }

            // contact_number validation
            if(empty($contact_number)) {
                array_push($errors, "contact_number is required.");
            }

            if(empty($errors)){
                try {   
                    $params = array($client_fname, $client_mi, $client_lname, $age, $home_address, $contact_number, $id);
    
                    $statement = $api->prepare("UPDATE client SET client_fname=?, client_mi=?, client_lname=?, age=?, home_address=?, contact_number=? WHERE client_id=?");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }
            
                    $mysqli_checks = $api->bind_params($statement, "sssisss", $params);
                    if ($mysqli_checks===false) {
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
            
                    $mysqli_checks = $api->execute($statement);
                    if($mysqli_checks===false) {
                        throw new Exception('Execute error: The prepared statement could not be executed.');
                    }
            
                    $mysqli_checks = $api->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    }
                } catch (Exception $e) {
                    exit();
                    $_SESSION['res'] = $e->getMessage();
                }
            } else {
                $_SESSION['res'] = $errors;
            }
        }
    } else {
        $_SESSION['res'] = "No rows selected.";
    }

    Header("Location: ./index.php");
}

if(isset($_POST['delete_client'])){
    if(isset($_POST['item'])){
        foreach($_POST['item'] as $item){
            try {
                $statement = $api->prepare("DELETE FROM user WHERE client_id=?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $statement->errno . ' - ' . $statement->error);
                }
        
                $mysqli_checks = $api->bind_params($statement, "s", $item);
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
        
                $mysqli_checks = $api->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }
        
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                }
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
            }
        }
    } else {
        $_SESSION['res'] = "No rows selected.";
    }

    // Header("Location: ./index.php");
}

/******** USER TABLE UPDATE AND DELETE ********/

// if(isset($_POST['update_item'])){
// }

// if(isset($_POST['remove_item'])){   
// }

/******** ORDER MANAGEMENT ********/

/******** BOOKING MANAGEMENT ********/

/******** ILLEGAL ACCESS CATCHING ********/

if(empty($_POST)){
    Header("Location: admin.php");
    die();
}
?>