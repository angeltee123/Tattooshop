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
    Header("Location: ../client/login.php");
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
    $client_id = $_SESSION['client_id'];
    $order_id = "";

    // validations
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
        if(isset($_SESSION['order_id']) && !empty($_SESSION['order_id'])){
            $order_id = $_SESSION['order_id'];
            $total = (double) 0.00; 

            try {
                // find existing order item
                $left = $api->join("INNER", "workorder", "order_item", "workorder.order_id", "order_item.order_id");
                $join = $api->join("INNER", $left, "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");

                $get_existing = $api->select();
                $get_existing = $api->params($get_existing, array("order_item.item_id", "tattoo_quantity"));
                $get_existing = $api->from($get_existing);
                $get_existing = $api->table($get_existing, $join);
                $get_existing = $api->where($get_existing, array("client_id", "workorder.order_id", "paid", "item_status", "order_item.tattoo_width", "order_item.tattoo_height"), array("?", "?", "?", "?", "?", "?"));
                $get_existing = $api->limit($get_existing, 1);

                $statement = $api->prepare($get_existing);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ssssii", array($_SESSION['client_id'], $order_id, "Unpaid", "Standing", $width, $height));
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
                } else {
                    if($api->num_rows($res) > 0){
                        // existing similar order item found
                        $row = $api->fetch_assoc($res);

                        $api->free_result($statement);
                        $mysqli_checks = $api->close($statement);
                        if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                        } else {
                            $statement = null;
                        }

                        // updating existing order item
                        $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE item_id=?");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $quantity += $row['tattoo_quantity'];
                        $mysqli_checks = $api->bind_params($statement, "is", array($quantity, $row['item_id']));
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
                    } else {
                        // no existing similar order item found, creating new order item
                        $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

                        $api->free_result($statement);
                        $mysqli_checks = $api->close($statement);
                        if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                        } else {
                            $statement = null;
                        }

                        // creating order_item                
                        $insert_order_item = $api->insert();
                        $insert_order_item = $api->table($insert_order_item, "order_item");
                        $insert_order_item = $api->columns($insert_order_item, array("item_id", "order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "item_status", "paid"));
                        $insert_order_item = $api->values($insert_order_item);
                        $insert_order_item = $api->columns($insert_order_item, array("?", "?", "?", "?", "?", "?", "?", "?"));

                        $statement = $api->prepare($insert_order_item);
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $api->bind_params($statement, "sssiiiss", array($item_id, $order_id, $id, $quantity, $width, $height, "Standing", "Unpaid"));
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
                    }
                }
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ../client/explore.php#".$name);
            }
        } else {
            // creating new order
            $order_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
            $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
            $_SESSION['order_id'] = $order_id;
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
                $insert_order_item = $api->columns($insert_order_item, array("item_id", "order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "item_status", "paid"));
                $insert_order_item = $api->values($insert_order_item);
                $insert_order_item = $api->columns($insert_order_item, array("?", "?", "?", "?", "?", "?", "?", "?"));

                $statement = $api->prepare($insert_order_item);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "sssiiiss", array($item_id, $order_id, $id, $quantity, $width, $height, "Standing", "Paid"));
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
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ../client/explore.php#".$name);
            }
        }

        try {
            // update order amount_due_total
            $join = $api->join("INNER", "order_item", "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");
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

            if($api->num_rows($res) > 0){
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
            Header("Location: ../client/explore.php");
        }
    }

    Header("Location: ../client/explore.php");
}

/******** ORDER MANAGEMENT ********/

if(isset($_POST['update_items'])){
    if(isset($_POST['item'])){
        $total = (double) 0.00;
        $order_id = $_SESSION['order_id'];

        foreach($_POST['item'] as $item){
            $errors = array();

            $item = $api->clean($item);
            $index = array_search($item, $_POST['index']);

            $width = intval($_POST['width'][$index]);
            $height = intval($_POST['height'][$index]);
            $quantity = intval($_POST['quantity'][$index]);
            $paid = $api->clean($_POST['paid'][$index]);
            $status = $api->clean($_POST['status'][$index]);

            // validations
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

            if(empty($paid)) {
                $_SESSION['paid_err'] = "Payment status is required. ";
                array_push($errors, $_SESSION['paid_err']);
            }
    
            elseif(!in_array($paid, array("Unpaid", "Partially Paid"))){
                $_SESSION['paid_err'] = "Payment status must be either Unpaid or Partially Paid. ";
                array_push($errors, $_SESSION['paid_err']);
            }

            if(empty($status)) {
                $_SESSION['status_err'] = "Item status is required. ";
                array_push($errors, $_SESSION['status_err']);
            }
    
            elseif(strcasecmp($status, "Standing")){
                $_SESSION['status_err'] = "Item status must be Standing. ";
                array_push($errors, $_SESSION['status_err']);
            }

            if(empty($errors)){
                try {
                    // find existing order item
                    $left = $api->join("INNER", "workorder", "order_item", "workorder.order_id", "order_item.order_id");
                    $join = $api->join("INNER", $left, "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");

                    $query = $api->select();
                    $query = $api->params($query, array("order_item.item_id", "tattoo_quantity"));
                    $query = $api->from($query);
                    $query = $api->table($query, $join);
                    $query = $api->where($query, array("client_id", "workorder.order_id", "paid", "item_status", "order_item.tattoo_width", "order_item.tattoo_height"), array("?", "?", "?", "?", "?", "?"));
                    $not = "AND item_id !=? ";
                    $query = $query . $not;
                    $query = $api->limit($query, 1);

                    $statement = $api->prepare($query);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $api->bind_params($statement, "ssssiis", array($_SESSION['client_id'], $order_id, $paid, $status, $width, $height, $item));
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
                    } else {
                        if($api->num_rows($res) > 0){
                            // existing similar order item found
                            $row = $api->fetch_assoc($res);

                            $api->free_result($statement);
                            $mysqli_checks = $api->close($statement);
                            if ($mysqli_checks===false) {
                            throw new Exception('The prepared statement could not be closed.');
                            } else {
                                $statement = null;
                            }

                            // updating existing order item
                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                            }

                            $quantity += $row['tattoo_quantity'];
                            $mysqli_checks = $api->bind_params($statement, "is", array($quantity, $row['item_id']));
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

                            // merging down order item
                            $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
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
                            } else {
                                $statement = null;
                            }
                        } else {
                            // no existing similar order item found
                            $api->free_result($statement);
                            $mysqli_checks = $api->close($statement);
                            if ($mysqli_checks===false) {
                            throw new Exception('The prepared statement could not be closed.');
                            } else {
                                $statement = null;
                            }

                            $statement = $api->prepare("UPDATE order_item SET tattoo_width=?, tattoo_height=?, tattoo_quantity=? WHERE item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                            }
                    
                            $mysqli_checks = $api->bind_params($statement, "iiis", array($width, $height, $quantity, $item));
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
                        }
                    }
                } catch (Exception $e) {
                    exit();
                    $_SESSION['res'] = $e->getMessage();
                    Header("Location: ../client/orders.php");
                }
            } else {
                $_SESSION['res'] = $errors;
            }
        }

        try {
            // update order amount_due_total
            $join = $api->join("INNER", "order_item", "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");
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

            if($api->num_rows($res) > 0){
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
            Header("Location: ../client/orders.php");
        }
    } else {
        $_SESSION['res'] = "No rows selected.";
    }

    Header("Location: ../client/orders.php");
}

if(isset($_POST['remove_items'])){
    if(isset($_POST['item'])){

        // calculating updated total price
        $old_total = (double) 0.00;
        $deductions = (double) 0.00;

        foreach($_POST['index'] as $item){
            $index = array_search($item, $_POST['index']);
            $old_total += (intval($_POST['price'][$index]) * intval($_POST['quantity'][$index]));
        }

        foreach($_POST['item'] as $item){
            $item = $api->clean($item);
            $index = array_search($item, $_POST['index']);

            $deductions += (intval($_POST['price'][$index]) * intval($_POST['quantity'][$index]));

            try {
                $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
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
                } else {
                    $statement = null;
                }
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ../client/orders.php");
            }
        }

        $order_id = $_SESSION['order_id'];
        $new_total = $old_total - $deductions;
        try {
            $new_total = doubleval($new_total);

            $update_total = $api->update();
            $update_total = $api->table($update_total, "workorder");
            $update_total = $api->set($update_total, "amount_due_total", "?");
            $update_total = $api->where($update_total, "order_id", "?");

            $statement = $api->prepare($update_total);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "ds", array($new_total, $order_id));
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
            Header("Location: ../client/orders.php");
        }
    } else {
        $_SESSION['res'] = "No rows selected.";
    }

    Header("Location: ../client/orders.php");
}

/******** RESERVATION MANAGEMENT ********/

if(isset($_POST['book']) && isset($_SESSION['order_id'])){
    $errors = array();
    $cstrong = true;

    $reservation_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
    $order_id = $api->clean($_SESSION['order_id']);
    $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
    $predecessor_id = $api->clean($_POST['item_id']);

    $quantity = intval($_POST['quantity']);
    $original_quantity = intval($_POST['original_quantity']);
    $service_type = $api->clean($_POST['service_type']);
    $scheduled_time = $_POST['scheduled_time'];
    $scheduled_date = $_POST['scheduled_date'];
    $address = $api->clean($_POST['address']);
    $reservation_description = $api->clean($_POST['description']);

    // validations
    if(empty($quantity)) {
        $_SESSION['quantity_err'] = "Reserved item quantity is required.";
        array_push($errors, $_SESSION['quantity_err']);
    }
    
    elseif (!is_int($quantity)) {
        $_SESSION['quantity_err'] = "Reserved item quantity must be an integer.";
        array_push($errors, $_SESSION['quantity_err']);
    }
    
    elseif($quantity < 0){
        $_SESSION['quantity_err'] = "Reserved item quantity must not be negative.";
        array_push($errors, $_SESSION['quantity_err']);
    }

    elseif($quantity > $original_quantity){
        $_SESSION['quantity_err'] = "Reserved item quantity must not exceed the item's ordered quantity.";
        array_push($errors, $_SESSION['quantity_err']);
    }

    if(empty($service_type)) {
        $_SESSION['service_type_err'] = "Service type is required.";
        array_push($errors, $_SESSION['service_type_err']);
    }

    elseif(!in_array($service_type, array("Walk-in", "Home Service"))){
        $_SESSION['service_type_err'] = "Service type must be either home service or walk-in.";
        array_push($errors, $_SESSION['service_type_err']);
    }

    elseif(strcasecmp($service_type, 'Home Service') == 0 && empty($address)) {
        $_SESSION['address_err'] = "Address for home service is required";
        array_push($errors, $_SESSION['address_err']);
    }

    if(!$api->is_valid_date($scheduled_date)) {
        $_SESSION['scheduled_date_err'] = "Invalid date. ";
        array_push($errors, $_SESSION['scheduled_date_err']);
    }

    if(!$api->is_valid_time($scheduled_time)) {
        $_SESSION['scheduled_time_err'] = "Invalid time.";
        array_push($errors, $_SESSION['scheduled_time_err']);
    }

    if(empty($errors)){
        $scheduled_date = date("Y:m:d", strtotime($scheduled_date));

        try {
            // retrieving predecessor
            $get_predecessor = $api->select();
            $get_predecessor = $api->params($get_predecessor, "*");
            $get_predecessor = $api->from($get_predecessor);
            $get_predecessor = $api->table($get_predecessor, "order_item");
            $get_predecessor = $api->where($get_predecessor, "item_id", "?");
            $get_predecessor = $api->limit($get_predecessor, 1);

            $statement = $api->prepare($get_predecessor);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "s", $predecessor_id);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($statement);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $res = $api->get_result($statement);
            if($api->num_rows($res) > 0){
                $item = $api->fetch_assoc($res);
            } else {
                throw new Exception('No record with the given ID found.');
            }

            $api->free_result($statement);
            $mysqli_checks = $api->close($statement);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            } else {
                $statement = null;
            }

            // creating reserved item
            $insert_reserved_item = $api->insert();
            $insert_reserved_item = $api->table($insert_reserved_item, "order_item");
            $insert_reserved_item = $api->columns($insert_reserved_item, array("item_id", "order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "paid", "item_status", "predecessor_id"));
            $insert_reserved_item = $api->values($insert_reserved_item);
            $insert_reserved_item = $api->columns($insert_reserved_item, array("?", "?", "?", "?", "?", "?", "?", "?", "?"));

            $statement = $api->prepare($insert_reserved_item);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "sssiiisss", array($item_id, $order_id, $item['tattoo_id'], $quantity, $item['tattoo_width'], $item['tattoo_height'], $item['paid'], "Reserved", $predecessor_id));
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

            // // creating reservation
            $insert_reservation = $api->insert();
            $insert_reservation = $api->table($insert_reservation, "reservation");
            $insert_reservation = $api->columns($insert_reservation, array("reservation_id", "workorder_id", "item_id", "reservation_description", "reservation_status", "service_type", "reservation_address", "scheduled_date", "scheduled_time", "amount_addon"));
            $insert_reservation = $api->values($insert_reservation);
            $insert_reservation = $api->columns($insert_reservation, array("?", "?", "?", "?", "?", "?", "?", "?", "?", "?"));

            $statement = $api->prepare($insert_reservation);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "sssssssssi", array($reservation_id, $order_id, $item_id, $reservation_description, "Pending", $service_type, $address, $scheduled_date, $scheduled_time, 300.00));
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

            // update predecessor item quantity
            $item['tattoo_quantity'] -= $quantity;
            $update_quantity = $api->update();
            $update_quantity = $api->table($update_quantity, "order_item");
            $update_quantity = $api->set($update_quantity, "tattoo_quantity", "?");
            $update_quantity = $api->where($update_quantity, array("item_id", "order_id"), array("?", "?"));

            $statement = $api->prepare($update_quantity);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "iss", array($item['tattoo_quantity'], $predecessor_id, $_SESSION['order_id']));
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

            /* MISSING */
            /* Updating total price */
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ../client/reservations.php");
        }
    }

    Header("Location: ../client/reservations.php");
}

if(isset($_POST['update_reservation'])){
    $errors = array();

    $id = $api->clean($_POST['reservation_id']);
    $service_type = $api->clean($_POST['service_type']);
    $time = $_POST['scheduled_time'];
    $date = $_POST['scheduled_date'];   
    $address = $api->clean($_POST['reservation_address']);
    $demands = $api->clean($_POST['reservation_demands']);

    if(empty($address)) {
        $_SESSION['address_err'] = "Reservation address is required.";
        array_push($errors, $_SESSION['address_err']);
    }

    if(empty($service_type)) {
        $_SESSION['service_type_err'] = "Service type is required.";
        array_push($errors, $_SESSION['service_type_err']);
    }

    if(!$api->is_valid_date($date)) {
        $_SESSION['scheduled_date_err'] = "Invalid date. ";
        array_push($errors, $_SESSION['scheduled_date_err']);
    }

    if(!$api->is_valid_time($time)) {
        $_SESSION['scheduled_time_err'] = "Invalid time.";
        array_push($errors, $_SESSION['scheduled_time_err']);
    }

    if(empty($errors)){
        $date = date("Y:m:d", strtotime($date));

        try {
            $query = $api->update();
            $query = $api->table($query, "reservation");
            $query = $api->set($query, array("service_type", "reservation_address", "reservation_description", "scheduled_date", "scheduled_time"), array("?", "?", "?", "?", "?"));
            $query = $api->where($query, "reservation_id", "?");

            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
    
            $mysqli_checks = $api->bind_params($statement, "ssssss", array($service_type, $address, $demands, $date, $time, $id));
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
            Header("Location: ../client/reservations.php");
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ../client/reservations.php");
}

if(isset($_POST['confirm_reservation'])){
    $errors = array();

    $reservation_id = $api->clean($_POST['reservation_id']);
    $service_type = $api->clean($_POST['service_type']);
    $scheduled_time = $_POST['scheduled_time'];
    $scheduled_date = $_POST['scheduled_date'];
    $address = $api->clean($_POST['reservation_address']);

    if(empty($address)) {
        $_SESSION['address_err'] = "Reservation address is required.";
        array_push($errors, $_SESSION['address_err']);
    }

    if(empty($service_type)) {
        $_SESSION['service_type_err'] = "Service type is required.";
        array_push($errors, $_SESSION['service_type_err']);
    }

    if(!$api->is_valid_date($scheduled_date)) {
        $_SESSION['scheduled_date_err'] = "Invalid date. ";
        array_push($errors, $_SESSION['scheduled_date_err']);
    }

    if(!$api->is_valid_time($scheduled_time)) {
        $_SESSION['scheduled_time_err'] = "Invalid time.";
        array_push($errors, $_SESSION['scheduled_time_err']);
    }

    if(empty($errors)){
        try {
            $query = $api->update();
            $query = $api->table($query, "reservation");
            $query = $api->set($query, "reservation_status", "?");
            $query = $api->where($query, "reservation_id", "?");

            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
    
            $mysqli_checks = $api->bind_params($statement, "ss", array("Confirmed", $reservation_id));
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
            Header("Location: ../client/reservations.php");
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ../client/reservations.php");
}

if(isset($_POST['cancel_reservation'])){
    $reservation_id = $api->clean($_POST['reservation_id']);
    $item_id = $api->clean($_POST['item_id']);
    $predecessor_id = $api->clean($_POST['predecessor_id']);
    $quantity = intval($_POST['quantity']);
    $new_quantity = 0;

    try {
        /*  MISSING - DELETIONS BASED ON PREDEDECESSOR
            CASES:
                PREDECESSOR EXISTS - UPDATE PREDECESSOR QUANTITY
                PREDECESSOR DOES NOT EXIST - FIND STANDING ORDER ITEM WITH THE SAME TATTOO (SORT BY LEAST RECENT DATE)
                NO RELATED STANDING ORDER ITEM FOUND - MAKE PREDECESSOR ID NULL AND MAKE ITEM THE PREDECESSOR
        */

        // getting order item quantity
        $get_item_quantity = $api->prepare("SELECT tattoo_quantity FROM order_item WHERE item_id=?");
        if ($get_item_quantity===false) {
            throw new Exception('prepare() error: ' . $statement->errno . ' - ' . $statement->error);
        }
    
        $mysqli_checks = $api->bind_params($get_item_quantity, "s", $predecessor_id);
        if ($mysqli_checks===false) {
            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        }

        $mysqli_checks = $api->execute($get_item_quantity);
        if($mysqli_checks===false) {
            throw new Exception('Execute error: The prepared statement could not be executed.');
        }

        $api->store_result($get_item_quantity);
        if($api->num_rows($get_item_quantity) > 0){
            $res = $api->bind_result($get_item_quantity, array($new_quantity));
            $api->get_bound_result($new_quantity, $res[0]);
            $new_quantity += $quantity;

            // updating order item quantity
            $update_quantity = $api->update();
            $update_quantity = $api->table($update_quantity, "order_item");
            $update_quantity = $api->set($update_quantity, "tattoo_quantity", "?");
            $update_quantity = $api->where($update_quantity, "item_id", "?");

            $statement = $api->prepare($update_quantity);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "ds", array($new_quantity, $predecessor_id));
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
        }

        // deleting reservation
        $query = $api->delete();
        $query = $api->from($query);
        $query = $api->table($query, "reservation");
        $query = $api->where($query, "reservation_id", "?");

        $statement = null;
        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $statement->errno . ' - ' . $statement->error);
        }

        $mysqli_checks = $api->bind_params($statement, "s", $reservation_id);
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

        $api->free_result($get_item_quantity);
        $mysqli_checks = $api->close($get_item_quantity);
        if ($mysqli_checks===false) {
            throw new Exception('The prepared statement could not be closed.');
        }
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ../client/reservations.php");
    }

    Header("Location: ../client/reservations.php");
}

/******** ORDER CHECKOUT ********/

if(isset($_POST['checkout']) && isset($_SESSION['order_id']) && !empty($_SESSION['order_id'])){
    if(isset($_POST['item'])){
        $errors = array();

        $first_name = $api->clean(ucfirst($_POST['first_name']));
        $last_name = $api->clean(ucfirst($_POST['last_name']));
        $street_address = $api->clean($_POST['street_address']);
        $city = $api->clean($_POST['city']);
        $province = $api->clean($_POST['province']);
        $zip = $api->clean($_POST['zip']);
        $payment_amount = doubleval($_POST['payment_amount']);
        $payment_method = $api->clean($_POST['payment_method']);
        $card_number = $api->clean($_POST['card_number']);
        $pin = $api->clean($_POST['pin']);
        $bank_name = $api->clean($_POST['bank_name']);

        // validations
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

        // billing address validations
        if(empty($street_address)) {
            $_SESSION['street_address_err'] = "Street address is required. ";
            array_push($errors, $_SESSION['street_address_err']);
        }

        elseif (mb_strlen($street_address) > 255) {
            $_SESSION['street_address_err'] = "Street address must not exceed 255 characters. ";
            array_push($errors, $_SESSION['street_address_err']);
        }

        if(empty($city)) {
            $_SESSION['city_err'] = "City name is required. ";
            array_push($errors, $_SESSION['city_err']);
        }

        elseif (mb_strlen($city) > 35) {
            $_SESSION['city_err'] = "City name must not exceed 35 characters. ";
            array_push($errors, $_SESSION['city_err']);
        }

        if(empty($province)) {
            $_SESSION['province_err'] = "Province name is required. ";
            array_push($errors, $_SESSION['province_err']);
        }

        elseif (mb_strlen($province) > 35) {
            $_SESSION['province_err'] = "Provice name must not exceed 35 characters. ";
            array_push($errors, $_SESSION['province_err']);
        }

        if(empty($zip)) {
            $_SESSION['zip_err'] = "ZIP code is required. ";
            array_push($errors, $_SESSION['zip_err']);
        }

        elseif (mb_strlen($zip) > 4) {
            $_SESSION['zip_err'] = "ZIP code 4 must not exceed characters. ";
            array_push($errors, $_SESSION['zip_err']);
        }

        elseif (!is_int(intval($zip))) {
            $_SESSION['zip_err'] = "ZIP code must be an integer. ";
            array_push($errors, $_SESSION['zip_err']);
        }

        // payment validations
        if(empty($payment_amount)) {
            $_SESSION['payment_amount_err'] = "Payment amount is required. ";
            array_push($errors, $_SESSION['payment_amount_err']);
        }

        elseif(!is_numeric($payment_amount)) {
            $_SESSION['payment_amount_err'] = "Payment amount must be a numeric value. ";
            array_push($errors, $_SESSION['payment_amount_err']);
        }

        elseif($payment_amount < 0){
            $_SESSION['payment_amount_err'] = "Payment amount must not be negative. ";
            array_push($errors, $_SESSION['payment_amount_err']);
        }

        if(empty($payment_method)) {
            $_SESSION['payment_method_err'] = "Payment method is required. ";
            array_push($errors, $_SESSION['payment_method_err']);
        }

        elseif(!in_array($payment_method, array("Debit", "Credit", "Prepaid"))){
            $_SESSION['payment_method_err'] = "Card type must be Debit, Credit, or Prepaid. ";
            array_push($errors, $_SESSION['payment_method_err']);
        }

        // card validations
        if(empty($card_number)) {
            $_SESSION['card_number_err'] = "Card number is required. ";
            array_push($errors, $_SESSION['card_number_err']);
        }

        if(!is_numeric(intval($card_number))) {
            $_SESSION['card_number_err'] = "Card number must be numeric. ";
            array_push($errors, $_SESSION['card_number_err']);
        }

        if(empty($pin)) {
            $_SESSION['pin_err'] = "Card PIN is required. ";
            array_push($errors, $_SESSION['pin_err']);
        }

        if(!is_numeric(intval($card_number))) {
            $_SESSION['pin_err'] = "Card PIN must be numeric. ";
            array_push($errors, $_SESSION['pin_err']);
        }

        if(empty($bank_name)) {
            $_SESSION['bank_name_err'] = "Bank name is required. ";
            array_push($errors, $_SESSION['bank_name_err']);
        }

        if(empty($errors)){
            $errors = [];
            $total = (double) 0.00;

            foreach($_POST['index'] as $item){
                $index = array_search($item, $_POST['index']);
                $total += ($_POST['price'][$index] * intval($_POST['quantity'][$index]));
            }

            foreach($_POST['item'] as $item){
                $item = $api->clean($item);
                $index = array_search($item, $_POST['index']);
                
                $checkout_quantity = intval($_POST['checkout_quantity'][$index]);
                $item_quantity = intval($_POST['quantity'][$index]);

                if(empty($checkout_quantity)) {
                    $_SESSION['quantity_err'] = "Checkout quantity is required. ";
                    array_push($errors, $_SESSION['quantity_err']);
                }
        
                elseif(!is_int($checkout_quantity)) {
                    $_SESSION['quantity_err'] = "Checkout quantity must be an integer. ";
                    array_push($errors, $_SESSION['quantity_err']);
                }
        
                elseif($checkout_quantity < 0){
                    $_SESSION['quantity_err'] = "Checkout quantity must not be negative. ";
                    array_push($errors, $_SESSION['quantity_err']);
                }

                elseif($checkout_quantity > $item_quantity){
                    $_SESSION['quantity_err'] = "Checkout quantity must not exceed the quantity of the ordered item. ";
                    array_push($errors, $_SESSION['quantity_err']);
                }

                print_r($errors);
                if(empty($errors)){
                    $item_amount_due_total = intval($_POST['price'][$index]) * $checkout_quantity;
                    if ($payment_amount >= $item_amount_due_total){
                        $payment_amount -= $item_amount_due_total;

                        $predecessor_id = $_POST['predecessor'][$index];
                        $item_status = $_POST['status'][$index];
                        $payment_status = $_POST['paid'][$index];
                        $width = $_POST['width'][$index];
                        $height = $_POST['height'][$index];
                        
                        /*  
                            PAYMENT CASES:
                                User orders more than the ordered quantity
                                User orders equal to the ordered quantity
                                User orders less than the ordered quantity

                            QUANTITY CASES
                                Standing Unpaid
                                    Item has no predecessor
                                        Find Standing Partially Paid item with the same tattoo_id and merge
                                            No Standing Partially Paid item found - Update item to Standing Partially Paid
                                    Item has predecessor_id
                                        Predecessor found - Update Predecessor
                                            Predecessor quantity is 0 - Merge with predecessor and update item to Standing Partially Paid
                                            Predecessor quantity is not 0 - find Standing Partially Paid item with the same tattoo_id and merge
                                                No Standing Partially Paid item found - Make predecessor id null and update to Standing Partially Paid

                                Reserved Unpaid
                                    Item has no predecessor
                                        Find Reserved Fully Paid item with the same tattoo_id and merge
                                            No Reserved Fully Paid item found - Update item to Reserved Fully Paid
                                    Item has predecessor_id
                                        Predecessor found - Update Predecessor
                                            Predecessor quantity is 0 - Merge with predecessor
                                            Predecessor quantity is not 0 - find Reserved Fully Paid item with the same tattoo_id and merge
                                                No related standing order item found - Make predecessor id null and updated item to Reserved Fully Paid

                                Reserved Partially Paid
                                    Item has no predecessor
                                        Find Reserved Fully Paid item with the same tattoo_id and merge
                                            No Reserved Fully Paid item found - Update item to Reserved Fully Paid
                                    Item has predecessor_id
                                        Predecessor found - Update Predecessor
                                            Predecessor quantity is 0 - Merge with predecessor
                                            Predecessor quantity is not 0 - find Reserved Fully Paid item with the same tattoo_id and merge
                                                No related standing order item found - Make predecessor id null and update item to Reserved Fully Paid

                                Applied Unpaid
                                    Item has no predecessor
                                        Find Applied Fully Paid item with the same tattoo_id and merge
                                            No Applied Fully Paid item found - Update item to Applied Fully Paid
                                    Item has predecessor_id
                                        Predecessor found - Update Predecessor
                                            Predecessor quantity is 0 - Merge with predecessor
                                            Predecessor quantity is not 0 - find Applied Fully Paid item with the same tattoo_id and merge
                                                No Applied Fully Paid item found - Make predecessor id null and update item to Applied Fully Paid

                                Applied Partially Paid
                                    Item has no predecessor
                                        Find Reserved Fully Paid item with the same tattoo_id and merge
                                            No Reserved Fully Paid item found - Update item to Applied Fully Paid
                                    Item has predecessor_id
                                        Predecessor found - Update Predecessor
                                            Predecessor quantity is 0 - Merge with predecessor
                                            Predecessor quantity is not 0 - find Applied Fully Paid item with the same tattoo_id and merge
                                                No related standing order item found - Make predecessor id null and update item to Applied Fully Paid
                        */

                        if($checkout_quantity == $item_quantity) {
                            // Case - Item Standing Unpaid
                            if(strcasecmp($item_status, "Standing") == 0 && strcasecmp($payment_status, "Unpaid") == 0) {
                                // Case - No predecessor
                                if(strcasecmp($predecessor_id, "null") == 0){
                                    // Finding Standing Partially Paid item
                                    $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? AND predecessor_id=? LIMIT ?");
                                    if ($statement===false) {
                                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                    }

                                    $mysqli_checks = $api->bind_params($statement, "ssssiisi", array($_SESSION['client_id'], $_SESSION['order_id'], "Partially Paid", "Standing", $width, $height, "", 1));
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

                                    if($api->num_rows($res) > 0){
                                        $row = $api->fetch_assoc($res);
                                        
                                        $api->free_result($res);

                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }

                                        // updating found Standing Partially Paid item
                                        $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                        }

                                        $checkout_quantity += $row['tattoo_quantity'];
                                        $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $row['item_id']));
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

                                        // merging down checkout item
                                        $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
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
                                        } else {
                                            $statement = null;
                                        }
                                    } else {
                                        $api->free_result($res);

                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }

                                        // updating item
                                        $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                        }

                                        $checkout_quantity += $row['tattoo_quantity'];
                                        $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
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
                                    }
                                } else {
                                    // Finding predecessor
                                    $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND order_item.item_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT ?");
                                    if ($statement===false) {
                                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                    }

                                    $mysqli_checks = $api->bind_params($statement, "sssssiii", array($_SESSION['client_id'], $_SESSION['order_id'], $predecessor_id, "Partially Paid", "Standing", $width, $height, 1));
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

                                    if($api->num_rows($res) > 0){
                                        // Predecessor exists
                                        $row = $api->fetch_assoc($res);

                                        $api->free_result($res);

                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }

                                        if($row['tattoo_quantity'] == 0){
                                            $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }

                                            $checkout_quantity += $row['tattoo_quantity'];
                                            $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $predecessor_id));
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

                                            // merging down checkout item
                                            $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
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
                                            } else {
                                                $statement = null;
                                            }
                                        } else {
                                            $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? AND predecessor_id=? LIMIT ?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }

                                            $mysqli_checks = $api->bind_params($statement, "ssssiisi", array($_SESSION['client_id'], $_SESSION['order_id'], "Partially Paid", "Standing", $width, $height, "", 1));
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

                                            if($api->num_rows($res) > 0){
                                                $row = $api->fetch_assoc($res);
                                                
                                                $api->free_result($res);
        
                                                $mysqli_checks = $api->close($statement);
                                                if ($mysqli_checks===false) {
                                                    throw new Exception('The prepared statement could not be closed.');
                                                } else {
                                                    $statement = null;
                                                }
        
                                                // updating found Standing Partially Paid item
                                                $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                                if ($statement===false) {
                                                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                                }
        
                                                $checkout_quantity += $row['tattoo_quantity'];
                                                $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $row['item_id']));
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
        
                                                // merging down checkout item
                                                $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                                if ($statement===false) {
                                                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
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
                                                } else {
                                                    $statement = null;
                                                }
                                            } else {
                                                $api->free_result($res);
        
                                                $mysqli_checks = $api->close($statement);
                                                if ($mysqli_checks===false) {
                                                    throw new Exception('The prepared statement could not be closed.');
                                                } else {
                                                    $statement = null;
                                                }
        
                                                // updating item
                                                $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                                if ($statement===false) {
                                                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                                }
        
                                                $checkout_quantity += $row['tattoo_quantity'];
                                                $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
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
                                            }
                                        }
                                    } else {
                                        // Predecessor does not exist
                                        $api->free_result($res);

                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }

                                        // updating item
                                        $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                        }

                                        $checkout_quantity += $row['tattoo_quantity'];
                                        $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
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
                                    }
                                }
                            }

                            // Case - Item Standing Unpaid, Predecessor Found
                                // elseif(strcasecmp($item_status, "Reserved") == 0 && strcasecmp($payment_status, "Unpaid") == 0 || strcasecmp($payment_status, "Partially Paid") == 0) {   
                                //     // Case - No predecessor
                                //     if(strcasecmp($predecessor_id, "null") == 0){
                                //         // Finding Standing Partially Paid item
                                //         $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? AND predecessor_id=? LIMIT ?");
                                //         if ($statement===false) {
                                //             throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //         }

                                //         $mysqli_checks = $api->bind_params($statement, "ssssiisi", array($_SESSION['client_id'], $_SESSION['order_id'], "Partially Paid", "Standing", $width, $height, "", 1));
                                //         if ($mysqli_checks===false) {
                                //             throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //         }

                                //         $mysqli_checks = $api->execute($statement);
                                //         if($mysqli_checks===false) {
                                //             throw new Exception('Execute error: The prepared statement could not be executed.');
                                //         }

                                //         $res = $api->get_result($statement);
                                //         if($res===false){
                                //             throw new Exception('get_result() error: Getting result set from statement failed.');
                                //         }

                                //         if($api->num_rows($res) > 0){
                                //             $row = $api->fetch_assoc($res);
                                            
                                //             $api->free_result($res);

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }

                                //             // updating found Standing Partially Paid item
                                //             $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //             if ($statement===false) {
                                //                 throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //             }

                                //             $checkout_quantity += $row['tattoo_quantity'];
                                //             $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $row['item_id']));
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //             }

                                //             $mysqli_checks = $api->execute($statement);
                                //             if($mysqli_checks===false) {
                                //                 throw new Exception('Execute error: The prepared statement could not be executed.');
                                //             }

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }

                                //             // merging down checkout item
                                //             $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                //             if ($statement===false) {
                                //                 throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //             }

                                //             $mysqli_checks = $api->bind_params($statement, "s", $item);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //             }

                                //             $mysqli_checks = $api->execute($statement);
                                //             if($mysqli_checks===false) {
                                //                 throw new Exception('Execute error: The prepared statement could not be executed.');
                                //             }

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }
                                //         } else {
                                //             $api->free_result($res);

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }

                                //             // updating item
                                //             $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //             if ($statement===false) {
                                //                 throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //             }

                                //             $checkout_quantity += $row['tattoo_quantity'];
                                //             $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //             }

                                //             $mysqli_checks = $api->execute($statement);
                                //             if($mysqli_checks===false) {
                                //                 throw new Exception('Execute error: The prepared statement could not be executed.');
                                //             }

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }
                                //         }
                                //     } else {
                                //         // Finding predecessor
                                //         $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND order_item.item_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT ?");
                                //         if ($statement===false) {
                                //             throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //         }

                                //         $mysqli_checks = $api->bind_params($statement, "sssssiii", array($_SESSION['client_id'], $_SESSION['order_id'], $predecessor_id, "Partially Paid", "Standing", $width, $height, 1));
                                //         if ($mysqli_checks===false) {
                                //             throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //         }

                                //         $mysqli_checks = $api->execute($statement);
                                //         if($mysqli_checks===false) {
                                //             throw new Exception('Execute error: The prepared statement could not be executed.');
                                //         }

                                //         $res = $api->get_result($statement);
                                //         if($res===false){
                                //             throw new Exception('get_result() error: Getting result set from statement failed.');
                                //         }

                                //         if($api->num_rows($res) > 0){
                                //             // Predecessor exists
                                //             $row = $api->fetch_assoc($res);

                                //             $api->free_result($res);

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }

                                //             if($row['tattoo_quantity'] == 0){
                                //                 $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //                 if ($statement===false) {
                                //                     throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                 }

                                //                 $checkout_quantity += $row['tattoo_quantity'];
                                //                 $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $predecessor_id));
                                //                 if ($mysqli_checks===false) {
                                //                     throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                 }

                                //                 $mysqli_checks = $api->execute($statement);
                                //                 if($mysqli_checks===false) {
                                //                     throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                 }

                                //                 $mysqli_checks = $api->close($statement);
                                //                 if ($mysqli_checks===false) {
                                //                     throw new Exception('The prepared statement could not be closed.');
                                //                 } else {
                                //                     $statement = null;
                                //                 }

                                //                 // merging down checkout item
                                //                 $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                //                 if ($statement===false) {
                                //                     throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                 }

                                //                 $mysqli_checks = $api->bind_params($statement, "s", $item);
                                //                 if ($mysqli_checks===false) {
                                //                     throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                 }

                                //                 $mysqli_checks = $api->execute($statement);
                                //                 if($mysqli_checks===false) {
                                //                     throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                 }

                                //                 $mysqli_checks = $api->close($statement);
                                //                 if ($mysqli_checks===false) {
                                //                     throw new Exception('The prepared statement could not be closed.');
                                //                 } else {
                                //                     $statement = null;
                                //                 }
                                //             } else {
                                //                 $statement = $api->prepare("SELECT order_item.item_id, order_item.tattoo_quantity FROM ((workorder INNER JOIN order_item ON workorder.order_id=order_item.order_id) INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE client_id=? AND workorder.order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? AND predecessor_id=? LIMIT ?");
                                //                 if ($statement===false) {
                                //                     throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                 }

                                //                 $mysqli_checks = $api->bind_params($statement, "ssssiisi", array($_SESSION['client_id'], $_SESSION['order_id'], "Partially Paid", "Standing", $width, $height, "", 1));
                                //                 if ($mysqli_checks===false) {
                                //                     throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                 }

                                //                 $mysqli_checks = $api->execute($statement);
                                //                 if($mysqli_checks===false) {
                                //                     throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                 }

                                //                 $res = $api->get_result($statement);
                                //                 if($res===false){
                                //                     throw new Exception('get_result() error: Getting result set from statement failed.');
                                //                 }

                                //                 if($api->num_rows($res) > 0){
                                //                     $row = $api->fetch_assoc($res);
                                                    
                                //                     $api->free_result($res);
            
                                //                     $mysqli_checks = $api->close($statement);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('The prepared statement could not be closed.');
                                //                     } else {
                                //                         $statement = null;
                                //                     }
            
                                //                     // updating found Standing Partially Paid item
                                //                     $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //                     if ($statement===false) {
                                //                         throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                     }
            
                                //                     $checkout_quantity += $row['tattoo_quantity'];
                                //                     $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $row['item_id']));
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                     }
            
                                //                     $mysqli_checks = $api->execute($statement);
                                //                     if($mysqli_checks===false) {
                                //                         throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                     }
            
                                //                     $mysqli_checks = $api->close($statement);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('The prepared statement could not be closed.');
                                //                     } else {
                                //                         $statement = null;
                                //                     }
            
                                //                     // merging down checkout item
                                //                     $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                                //                     if ($statement===false) {
                                //                         throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                     }
            
                                //                     $mysqli_checks = $api->bind_params($statement, "s", $item);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                     }
            
                                //                     $mysqli_checks = $api->execute($statement);
                                //                     if($mysqli_checks===false) {
                                //                         throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                     }
            
                                //                     $mysqli_checks = $api->close($statement);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('The prepared statement could not be closed.');
                                //                     } else {
                                //                         $statement = null;
                                //                     }
                                //                 } else {
                                //                     $api->free_result($res);
            
                                //                     $mysqli_checks = $api->close($statement);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('The prepared statement could not be closed.');
                                //                     } else {
                                //                         $statement = null;
                                //                     }
            
                                //                     // updating item
                                //                     $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //                     if ($statement===false) {
                                //                         throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //                     }
            
                                //                     $checkout_quantity += $row['tattoo_quantity'];
                                //                     $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //                     }
            
                                //                     $mysqli_checks = $api->execute($statement);
                                //                     if($mysqli_checks===false) {
                                //                         throw new Exception('Execute error: The prepared statement could not be executed.');
                                //                     }
            
                                //                     $mysqli_checks = $api->close($statement);
                                //                     if ($mysqli_checks===false) {
                                //                         throw new Exception('The prepared statement could not be closed.');
                                //                     } else {
                                //                         $statement = null;
                                //                     }
                                //                 }
                                //             }
                                //         } else {
                                //             // Predecessor does not exist
                                //             $api->free_result($res);

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }

                                //             // updating item
                                //             $statement = $api->prepare("UPDATE order_item SET item_status=?, paid=?, tattoo_quantity=? WHERE item_id=?");
                                //             if ($statement===false) {
                                //                 throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                //             }

                                //             $checkout_quantity += $row['tattoo_quantity'];
                                //             $mysqli_checks = $api->bind_params($statement, "ssis", array("Standing", "Partially Paid", $checkout_quantity, $item));
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                                //             }

                                //             $mysqli_checks = $api->execute($statement);
                                //             if($mysqli_checks===false) {
                                //                 throw new Exception('Execute error: The prepared statement could not be executed.');
                                //             }

                                //             $mysqli_checks = $api->close($statement);
                                //             if ($mysqli_checks===false) {
                                //                 throw new Exception('The prepared statement could not be closed.');
                                //             } else {
                                //                 $statement = null;
                                //             }
                                //         }
                                //     }
                                // }
                        }
                    }
                } else {
                    $errors = [];
                }
            }

        //     $order_id = $_SESSION['order_id'];
        //     $new_total = $old_total - $deductions;
        //     try {
        //         $new_total = doubleval($new_total);

        //         $update_total = $api->update();
        //         $update_total = $api->table($update_total, "workorder");
        //         $update_total = $api->set($update_total, "amount_due_total", "?");
        //         $update_total = $api->where($update_total, "order_id", "?");

        //         $statement = $api->prepare($update_total);
        //         if ($statement===false) {
        //             throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        //         }

        //         $mysqli_checks = $api->bind_params($statement, "ds", array($new_total, $order_id));
        //         if ($mysqli_checks===false) {
        //             throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
        //         }

        //         $mysqli_checks = $api->execute($statement);
        //         if($mysqli_checks===false) {
        //             throw new Exception('Execute error: The prepared statement could not be executed.');
        //         }

        //         $mysqli_checks = $api->close($statement);
        //         if ($mysqli_checks===false) {
        //             throw new Exception('The prepared statement could not be closed.');
        //         }
        //     } catch (Exception $e) {
        //         exit();
        //         $_SESSION['res'] = $e->getMessage();
        //         Header("Location: ../client/checkout.php");
        //     }
        }
    } else {
        $_SESSION['res'] = "No items selected.";
    }

    // Header("Location: ../client/checkout.php");
}

/******** ILLEGAL ACCESS CATCHING ********/

if(empty($_POST)){
    Header("Location: ../client/index.php");
    die();
}
?>