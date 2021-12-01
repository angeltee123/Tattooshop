<?php
require_once 'api.php';
$_SESSION['api'] = new api();

/******** HELPER FUNCTIONS ********/

function clean($data){
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/******** USER REGISTRATION ********/

if(isset($_POST['signup'])){
    $errors = array();

    $first_name = clean($_POST['first_name']);
    $last_name = clean($_POST['last_name']);
    $email = clean($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // first name validation
    if(empty($first_name)) {
        $_SESSION['first_name_err'] = "First name is required. ";
        array_push($errors, $_SESSION['first_name_err']);
    }
    
    elseif (mb_strlen($first_name) < 2) {
        $_SESSION['first_name_err'] = "First name must be at least 2 characters long. ";
        array_push($errors, $_SESSION['first_name_err']);
    }
    
    elseif(ctype_space($first_name) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $first_name)){
        $_SESSION['first_name_err'] = "First name must not contain any spaces or special characters.";
        array_push($errors, $_SESSION['first_name_err']);
    }

    // last name validation
    if(empty($last_name)) {
        $_SESSION['last_name_err'] = "Last name is required. ";
        array_push($errors, $_SESSION['last_name_err']);
    }
    
    elseif (mb_strlen($last_name) < 2) {
        $_SESSION['last_name_err'] = "Last name must be at least 2 characters long. ";
        array_push($errors, $_SESSION['last_name_err']);
    }
    
    elseif(ctype_space($last_name) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $last_name)){
        $_SESSION['last_name_err'] = "Last name must not contain any spaces or special characters";
        array_push($errors, $_SESSION['last_name_err']);
    }

    // email validation
    if (empty($email)) {
        $_SESSION['email_err'] = "Email is required. ";
        array_push($errors, $_SESSION['email_err']);
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email_err'] = "Invalid email. ";
        array_push($errors, $_SESSION['email_err']);
    }

    try {
        $unique_email = $conn->prepare("SELECT * FROM user WHERE `user_email`=?");
        if ($unique_email===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $unique_email->bind_param("s", $email);
        if ($mysqli_checks===false) {
            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $unique_email->execute();
        if($unique_email->error) {
            throw new Exception('Execute error: The prepared statement could not be executed.');
            Header("Location: signup.php");
        }

        if($unique_email->num_rows >= 1) { 
            $_SESSION['email_err'] = "Email already taken. ";
            array_push($errors, $_SESSION['email_err']);
        }

        $mysqli_checks = $unique_email->close();
        if ($mysqli_checks===false) {
            throw new Exception('The prepared statement could not be closed.');
        }
    } catch (mysqli_sql_exception $e) {
        echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
        exit();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
    
    // password validation
    if (empty($password)) {
        $_SESSION['password_err'] = "Password is required. ";
        array_push($errors, $_SESSION['password_err']);
    }

    elseif(ctype_space($password)){
        $_SESSION['password_err'] = "Password must not contain any spaces. ";
        array_push($errors, $_SESSION['password_err']);
    }

    elseif(!preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $password)){
        $_SESSION['password_err'] = "Password must contain at least one special character. ";
        array_push($errors, $_SESSION['password_err']);
    }

    elseif(!preg_match('/[A-Z]/', $password)){
        $_SESSION['password_err'] = "Password must contain at least one capital letter. ";
        array_push($errors, $_SESSION['password_err']);
    }

    elseif(!preg_match('/[0-9]+/', $password)) {
        $_SESSION['password_err'] = "Password must contain at least one numeric character. ";
        array_push($errors, $_SESSION['password_err']);
    }

    if (empty($confirm_password)) {
        $_SESSION['confrm_password_err'] = "Confirm password field must not be empty. ";
        array_push($errors, $_SESSION['confrm_password_err']);
    }

    if (strcasecmp($password, $confirm_password) != 0) {
        $_SESSION['confrm_password_err'] = "Passwords must match. ";
        array_push($errors, $_SESSION['confrm_password_err']);
    }

    // Server insertion upon successful validation
    if(empty($errors)){
        $cstrong = true;
        $password = password_hash($password, PASSWORD_BCRYPT);
        $id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
        $uid = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

        try {
            // Insert into client table
            $insert_client = $conn->prepare("INSERT INTO `client` (`client_id`, `client_fname`, `client_lname`) VALUES(?,?,?)");
            if ($insert_client===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $insert_client->bind_param("sss", $id, $first_name, $last_name);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $insert_client->execute();
            if($insert_client->error) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
                Header("Location: signup.php");
            }

            $mysqli_checks = $insert_client->close();
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }

            // Insert into user table
            $insert_user = $conn->prepare("INSERT INTO `user` (`user_id`, `client_id`, `user_email`, `user_password`, `user_type`) VALUES(?,?,?,?,'User')");
            if ($insert_user===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $insert_user->bind_param("ssss", $uid, $id, $email, $password);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $insert_user->execute();
            if(!$insert_user->error){
                Header("Location: ../client/index.php");
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
                Header("Location: ../client/signup.php");
            }

            $mysqli_checks = $insert_user->close();
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }
        } catch (mysqli_sql_exception $e) {
            echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }
}

/******** USER AUTH ********/

if(isset($_POST['login'])){
    $errors = array();

    $email = clean($_POST['email']);
    $password = trim($_POST['password']);
    $hash = "";

    if (empty($email)) {
        $_SESSION['email_err'] = "Please enter an email. ";
        array_push($errors, $_SESSION['email_err']);
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email_err'] = "Invalid email. ";
        array_push($errors, $_SESSION['email_err']);
    }

    if (empty($password)) {
        $_SESSION['password_err'] = "Please enter a password. ";
        array_push($errors, $_SESSION['password_err']);
    }

    // Server retrieval
    if(empty($errors)){
        try {
            $query = $_SESSION['api']->select();
            $query = $_SESSION['api']->params($query, array("user_id","user_password","user_type"));
            $query = $_SESSION['api']->from($query);
            $query = $_SESSION['api']->table($query, "user");
            $query = $_SESSION['api']->where($query, array("user_email"), array("?"));
            $query = $_SESSION['api']->limit($query, 1);
            $statement = $_SESSION['api']->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $_SESSION['api']->bind_params($statement, "s", array($email));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $_SESSION['api']->execute($statement);
            if($mysqli_checks===true){
                $_SESSION['api']->store_result($statement);
                $_SESSION['user_id'] = $_SESSION['user_type'] = $hash = "";

                $res = $_SESSION['api']->bind_result($statement, array($_SESSION['user_id'], $hash, $_SESSION['user_type']));
                $_SESSION['api']->get_bound_result($_SESSION['user_id'], $res[0]);
                $_SESSION['api']->get_bound_result($hash, $res[1]);
                $_SESSION['api']->get_bound_result($_SESSION['user_type'], $res[2]);
                // if($query->num_rows > 0){
                //     if (password_verify($password, $hash)) {
                //         if($_SESSION['user_type'] == 'User'){
                //             $conn->change_user("user", "User@CIS2104.njctattoodb", $db);

                //             $get_client = $conn->prepare("SELECT `client_id` FROM `user` WHERE `user`.`user_id`=?");
                //             if ($get_client===false) {
                //                 throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                //             }

                //             $mysqli_checks = $get_client->bind_param("s", $_SESSION['user_id']);
                //             if ($mysqli_checks===false) {
                //                 throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                //             }

                //             $get_client->execute();
                //             $get_client->store_result();
                //             $get_client->bind_result($_SESSION['client_id']);
                //             $get_client->fetch();
                //             if($get_client->error) {
                //                 throw new Exception('Execute error: The prepared statement could not be executed.');
                //                 Header("Location: ../client/login.php");
                //             }

                //             $mysqli_checks = $get_client->close();
                //             if ($mysqli_checks===false) {
                //                 throw new Exception('The prepared statement could not be closed.');
                //             }

                //             Header("Location: ../client/index.php");
                //         } else {
                //             $conn->change_user("admin", "Admin@CIS2104.njctattoodb", $db);
                //             Header("Location: ../client/admin.php");
                //         }
                //     }
                // } else {
                //     $_SESSION['res'] = "User not found. Please try again.";
                //     print_r($_SESSION);
                //     // Header("Location: ../client/login.php");
                // }
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
                // Header("Location: ../client/login.php");
            }

            // $mysqli_checks = $query->close();
            // if ($mysqli_checks===false) {
            //     throw new Exception('The prepared statement could not be closed.');
            // }
        } catch (mysqli_sql_exception $e) {
            echo 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
            exit();
        } catch (Exception $e) {
            echo $e->getMessage();
            exit();
        }
    }
}

/******** ORDER MANAGEMENT ********/

if(isset($_POST['remove_item'])){
    $id = $_POST['item_id']; 
    // $delete = "DELETE FROM `order_item` WHERE `item_id`='$id'";
    // Header("Location: ../client/index.php");
}

/******** BOOKING MANAGEMENT ********/
?>