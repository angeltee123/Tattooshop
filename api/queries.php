<?php
session_name("sess_id");
session_start();
require_once 'api.php';
$api = new api();

/******** USER REGISTRATION ********/

if(isset($_POST['signup'])){
    $errors = array();

    $first_name = $api->clean(ucfirst($_POST['first_name']));
    $last_name = $api->clean(ucfirst($_POST['last_name']));
    $email = $api->clean($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);

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
        $query = $api->select();
        $query = $api->params($query, "*");
        $query = $api->from($query);
        $query = $api->table($query, "user");
        $query = $api->where($query, "user_email", "?");

        $unique_email = $api->prepare($query);
        if ($unique_email===false) {
            throw new Exception('prepare() error: The statement could not be prepared.');
        }

        $mysqli_checks = $api->bind_params($unique_email, "s", $email);
        if ($mysqli_checks===false) {
            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $mysqli_checks = $api->execute($unique_email);
        if($mysqli_checks===false) {
            throw new Exception('Execute error: The prepared statement could not be executed.');
        }

        $api->store_result($unique_email);
        if($api->num_rows($unique_email) >= 1) { 
            $_SESSION['email_err'] = "Email already taken. ";
            array_push($errors, $_SESSION['email_err']);
        }

        $api->free_result($unique_email);
        $mysqli_checks = $api->close($unique_email);
        if ($mysqli_checks===false) {
            throw new Exception('The prepared statement could not be closed.');
        }
    } catch (Exception $e) {
        $_SESSION['res'] = $e->getMessage();
        exit();
        Header("Location: ..client/register.php");
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
        $_SESSION['confirm_password_err'] = "Confirm password field must not be empty. ";
        array_push($errors, $_SESSION['confirm_password_err']);
    }

    if (strcasecmp($password, $confirm_password) != 0) {
        $_SESSION['confirm_password_err'] = "Passwords must match. ";
        array_push($errors, $_SESSION['confirm_password_err']);
    }

    // Server insertion upon successful validation
    if(count($errors)== 0){
        $cstrong = true;
        $password = password_hash($password, PASSWORD_BCRYPT);
        $id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
        $uid = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

        try {
            // Insert into client table
            $query = $api->insert();
            $query = $api->table($query, "client");
            $query = $api->columns($query, array("client_id","client_fname","client_lname"));
            $query = $api->values($query);
            $query = $api->columns($query, array("?","?","?"));

            $insert_client = $api->prepare($query);
            if ($insert_client===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($insert_client, "sss", array($id, $first_name, $last_name));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($insert_client);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $mysqli_checks = $api->close($insert_client);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }

            // Insert into user table
            $query = $api->insert();
            $query = $api->table($query, "user");
            $query = $api->columns($query, array("user_id","client_id","user_email","user_password","user_type"));
            $query = $api->values($query);
            $query = $api->columns($query, array("?","?","?","?","'User'"));

            $insert_user = $api->prepare($query);
            if ($insert_user===false) {
                throw new Exception('prepare() error: The statement could not be prepared.');
            }

            $mysqli_checks = $api->bind_params($insert_user, "ssss", array($uid, $id, $email, $password));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($insert_user);
            if($mysqli_checks===true){
                $_SESSION['user_id'] = $uid;
                $_SESSION['client_id'] = $id;
                $_SESSION['user_type'] = "User";
                Header("Location: ../client/index.php");
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $mysqli_checks = $api->close($insert_user);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ../client/register.php");
        }
    } else {
        Header("Location: ../client/register.php");
    }
}

/******** USER AUTH ********/

if(isset($_POST['login'])){
    $errors = array();

    $email = $api->clean($_POST['email']);
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

    // User retrieval from server
    if(empty($errors)){
        try {
            $query = $api->select();
            $query = $api->params($query, array("user_id","user_password","user_type"));
            $query = $api->from($query);
            $query = $api->table($query, "user");
            $query = $api->where($query, "user_email", "?");
            $query = $api->limit($query, 1);

            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: The statement could not be prepared.');
            }

            $mysqli_checks = $api->bind_params($statement, "s", $email);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($statement);
            if($mysqli_checks===true){
                $api->store_result($statement);
                if($api->num_rows($statement) > 0){
                    $_SESSION['user_id'] = $_SESSION['user_type']= "";
                    $res = $api->bind_result($statement, array($_SESSION['user_id'], $hash, $_SESSION['user_type']));
                    $api->get_bound_result($hash, $res[1]);

                    // User auth
                    if(password_verify($password, $hash)) {
                        $api->get_bound_result($_SESSION['user_id'], $res[0]);
                        $api->get_bound_result($_SESSION['user_type'], $res[2]);
                        if(strcasecmp($_SESSION['user_type'], 'User') == 0){
                            $api->change_user("user", "User@CIS2104.njctattoodb");

                            $query = $api->select();
                            $query = $api->params($query,"client_id");
                            $query = $api->from($query);
                            $query = $api->table($query, "user");
                            $query = $api->where($query, "user_id", "?");
                            $query = $api->limit($query, 1);

                            $get_client = $api->prepare($query);
                            if ($get_client===false) {
                                throw new Exception('prepare() error: The statement could not be prepared.');
                            }

                            $mysqli_checks = $api->bind_params($get_client, "s", array($_SESSION['user_id']));
                            if ($mysqli_checks===false) {
                                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                            }

                            $mysqli_checks = $api->execute($get_client);
                            if($mysqli_checks===true) {
                                $api->store_result($get_client);
                                $_SESSION['client_id'] = "";
                                
                                $client = $api->bind_result($get_client, array($_SESSION['client_id']));
                                $api->get_bound_result($_SESSION['client_id'], $client[0]);

                                Header("Location: ../client/index.php");
                            } else {
                                throw new Exception('Execute error: The prepared statement could not be executed.');
                            }

                            $api->free_result($get_client);
                            $mysqli_checks = $api->close($get_client);
                            if ($mysqli_checks===false) {
                                throw new Exception('The prepared statement could not be closed.');
                            }
                        } else {
                            $api->change_user("admin", "Admin@CIS2104.njctattoodb");
                            Header("Location: ../client/admin.php");
                        }
                    } else {
                        unset($_SESSION['user_id']);
                        unset($_SESSION['user_type']);
                        $_SESSION['res'] = "Incorrect password.";
                        Header("Location: ../client/login.php");
                    }
                } else {
                    $_SESSION['res'] = "User not found. Please try again.";
                    Header("Location: ../client/login.php");
                }
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $api->free_result($statement);
            $mysqli_checks = $api->close($statement);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ../client/login.php");
        }
    }
}

/******** USER LOGOUT ********/

if(isset($_POST['logout'])){
    setcookie(session_id(), "", time() - 3600);
    session_destroy();
    session_write_close();
}

/******** ORDERING TATTOOS ********/

if(isset($_POST['order_item'])){
    $cstrong = true;
    $errors = array();

    $id = $api->clean($_POST['tattoo_id']);
    $name = $api->clean($_POST['tattoo_name']);
    $width = intval($_POST['width']);
    $height = intval($_POST['height']);
    $quantity = intval($_POST['quantity']);
    $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
    $client_id = $_SESSION['client_id'];

    if(empty($width)) {
        $_SESSION['width_err'] = "Item width is required.";
        array_push($errors, $_SESSION['width_err']);
    }
    
    elseif (!is_int($width)) {
        $_SESSION['width_err'] = "Item width must be an integer.";
        array_push($errors, $_SESSION['width_err']);
    }
    
    elseif($width < 0){
        $_SESSION['width_err'] = "Item width must not be negative.";
        array_push($errors, $_SESSION['width_err']);
    }

    elseif($width > 24){
        $_SESSION['width_err'] = "Item width must not exceed 24 inches.";
        array_push($errors, $_SESSION['width_err']);
    }

    if(empty($height)) {
        $_SESSION['height_err'] = "Item height is required.";
        array_push($errors, $_SESSION['height_err']);
    }
    
    elseif (!is_int($height)) {
        $_SESSION['width_err'] = "Item height must be an integer.";
        array_push($errors, $_SESSION['height_err']);
    }
    
    elseif($height < 0){
        $_SESSION['height_err'] = "Item height must not be negative.";
        array_push($errors, $_SESSION['height_err']);
    }

    elseif($height > 36){
        $_SESSION['height_err'] = "Item width must not exceed 36 inches.";
        array_push($errors, $_SESSION['height_err']);
    }

    if(empty($quantity)) {
        $_SESSION['quantity_err'] = "Item quantity is required.";
        array_push($errors, $_SESSION['quantity_err']);
    }
    
    elseif (!is_int($quantity)) {
        $_SESSION['quantity_err'] = "Item quantity must be an integer.";
        array_push($errors, $_SESSION['quantity_err']);
    }
    
    elseif($quantity < 0){
        $_SESSION['quantity_err'] = "Item quantity must not be negative.";
        array_push($errors, $_SESSION['quantity_err']);
    }

    if(empty($errors)){
        try {
            // get existing order
            $get_order = $api->select();
            $get_order = $api->params($get_order, "order_id");
            $get_order = $api->from($get_order);
            $get_order = $api->table($get_order, "workorder");
            $get_order = $api->where($get_order, array("client_id", "status"), array("?", "?"));
            $get_order = $api->limit($get_order, 1);

            echo $get_order;

            $statement = $api->prepare($get_order);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "ss", array($client_id, "Ongoing"));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($statement);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $api->store_result($statement);
            $_SESSION['order_id'] = "";
            
            if($api->num_rows($statement) > 0){
                $res = $api->bind_result($statement, array($_SESSION['order_id']));
                $api->get_bound_result($_SESSION['order_id'], $res[0]);
            } else {
                unset($_SESSION['order_id']);
            }

            $mysqli_checks = $api->close($statement);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            } else {
                $statement = null;
            }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ../client/explore.php#".$name);
        }

        if(isset($_SESSION['order_id']) && !empty($_SESSION['order_id'])){
            $order_id = $_SESSION['order_id'];
            $total = (double) 0.00; 

            try {
                // creating order_item                
                $insert_order_item = $api->insert();
                $insert_order_item = $api->table($insert_order_item, "order_item");
                $insert_order_item = $api->columns($insert_order_item, array("item_id", "order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "item_status"));
                $insert_order_item = $api->values($insert_order_item);
                $insert_order_item = $api->columns($insert_order_item, array("?", "?", "?", "?", "?", "?", "?"));

                $statement = $api->prepare($insert_order_item);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "sssiiis", array($item_id, $order_id, $id, $quantity, $width, $height, "Standing"));
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
                } else {
                    $statement = null;
                }

                // update order amount_due_total
                $join = $api->join("", "order_item", "tattoo", "tattoo_id", "tattoo_id");
                $get_total = $api->select();
                $get_total = $api->params($get_total, array("tattoo_price", "tattoo_quantity"));
                $get_total = $api->from($get_total);
                $get_total = $api->table($get_total, $join);
                $get_total = $api->where($get_total, "order_id", "?");

                $statement = $api->prepare($get_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "s", $order_id);
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $api->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $res = $api->get_result($statement);
                if($res===false){
                    throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                if($api->num_rows($res)){
                    while($row = $api->fetch_assoc($res)){
                        $total += $row['tattoo_price'] * $row['tattoo_quantity'];
                    }
                }

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $statement = null;
                }

                $total = doubleval($total);

                $update_total = $api->update();
                $update_total = $api->table($update_total, "workorder");
                $update_total = $api->set($update_total, "amount_due_total", "?");
                $update_total = $api->where($update_total, "order_id", "?");

                $statement = $api->prepare($update_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ds", array($total, $order_id));
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
                Header("Location: ../client/explore.php#".$name);
            }
        } else {
            // creating new order
            $order_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
            $total = (double) 0.00; 

            $insert_workorder = $api->insert();
            $insert_workorder = $api->table($insert_workorder, "workorder");
            $insert_workorder = $api->columns($insert_workorder, array("order_id", "client_id", "incentive", "status"));
            $insert_workorder = $api->values($insert_workorder);
            $insert_workorder = $api->columns($insert_workorder, array("?", "?", "?", "?"));

            try {
                $statement = $api->prepare($insert_workorder);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ssss", array($order_id, $client_id, "None", "Ongoing"));
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
                } else {
                    $statement = null;
                }

                // creating order_item                
                $insert_order_item = $api->insert();
                $insert_order_item = $api->table($insert_order_item, "order_item");
                $insert_order_item = $api->columns($insert_order_item, array("item_id", "order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "item_status"));
                $insert_order_item = $api->values($insert_order_item);
                $insert_order_item = $api->columns($insert_order_item, array("?", "?", "?", "?", "?", "?", "?"));

                $statement = $api->prepare($insert_order_item);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "sssiiis", array($item_id, $order_id, $id, $quantity, $width, $height, "Standing"));
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
                } else {
                    $statement = null;
                }

                // update order amount_due_total
                $join = $api->join("", "order_item", "tattoo", "tattoo_id", "tattoo_id");
                $get_total = $api->select();
                $get_total = $api->params($get_total, array("tattoo_price", "tattoo_quantity"));
                $get_total = $api->from($get_total);
                $get_total = $api->table($get_total, $join);
                $get_total = $api->where($get_total, "order_id", "?");

                $statement = $api->prepare($get_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "s", $order_id);
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $api->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $res = $api->get_result($statement);
                if($res===false){
                    throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                if($api->num_rows($res)){
                    while($row = $api->fetch_assoc($res)){
                        $total += $row['tattoo_price'] * $row['tattoo_quantity'];
                    }
                }

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $statement = null;
                }

                $total = doubleval($total);

                $update_total = $api->update();
                $update_total = $api->table($update_total, "workorder");
                $update_total = $api->set($update_total, "amount_due_total", "?");
                $update_total = $api->where($update_total, "order_id", "?");

                $statement = $api->prepare($update_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ds", array($total, $order_id));
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

                $_SESSION['order_id'] = $order_id;
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ../client/explore.php#".$name);
            }
        }

        Header("Location: ../client/explore.php");
    } else {
        Header("Location: ../client/explore.php#".$name);
    }
}

/******** ORDER MANAGEMENT ********/

if(isset($_POST['update_item'])){
    try {
        $id = $api->clean($_POST['item_id']);
        $quantity = $_POST['quantity'];

        $query = $api->update();
        $query = $api->table($query, "order_item");
        $query = $api->set($query, "tattoo_quantity", "?");
        $query = $api->where($query, "client_id", "?");

        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $statement->errno . ' - ' . $statement->error);
        }

        $mysqli_checks = $api->bind_params($statement, "sis", array($id, $quantity, $_SESSION['client_id']));
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

        Header("Location: ../client/orders.php");
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ../client/orders.php");
    }
}

if(isset($_POST['remove_item'])){
    try {
        $id = $api->clean($_POST['item_id']);

        $query = $api->delete();
        $query = $api->from($query);
        $query = $api->table($query, "order_item");
        $query = $api->where($query, array("item_id", "client_id"), array("?","?"));

        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "ss", array($id, $_SESSION['client_id']));
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

        Header("Location: ../client/orders.php");
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ..client/orders.php");
    }
}

/******** BOOKING MANAGEMENT ********/

/******** ILLEGAL ACCESS CATCHING ********/

if(empty($_POST)){
    Header("Location: ../client/index.php");
    die();
}
?>