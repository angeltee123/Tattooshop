<?php
include_once 'connection.php';

/******** USER REGISTRATION ********/

if(isset($_POST['signup'])){
    $errors = array();

    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
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
    $unique_email = $conn->prepare("SELECT * FROM client WHERE email=?");
    $unique_email->bind_param("s", $email);
    $unique_email->execute();

    if (empty($email)) {
        $_SESSION['email_err'] = "Email is required. ";
        array_push($errors, $_SESSION['email_err']);
    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['email_err'] = "Invalid email. ";
        array_push($errors, $_SESSION['email_err']);
    }

    elseif ($unique_email->num_rows >= 1) { 
        $_SESSION['email_err'] = "Email is already taken. ";
        array_push($errors, $_SESSION['email_err']);
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
        password_hash($password, PASSWORD_BCRYPT);
        $id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

        try {
            $query = $conn->prepare("INSERT INTO `client` (`id`, `first_name`, `last_name`, `password`) VALUES(?,?,?,?)");
            if ($query===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $query->bind_param("sssss", $id, $first_name, $last_name, $email, $password);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $query->execute();
            if(!$query->error){
                Header("Location: index.php");
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
                Header("Location: signup.php");
            }

            $mysqli_checks = $query->close();
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

    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email)) {
        $_SESSION['email_err'] = "Please enter an email. ";
        array_push($errors, $_SESSION['email_err']);
    }

    if (empty($password)) {
        $_SESSION['password_err'] = "Please enter a password. ";
        array_push($errors, $_SESSION['password_err']);
    }
    // Server retrieval
    if(empty($errors)){
        try {
            $query = $conn->prepare("SELECT email FROM client WHERE email=? LIMIT 1");
            if ($query===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $query->bind_param("s", $email);
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $query->execute();
            if(!$query->error){
                if($query->num_rows > 0){
                    $row = $query->fetch_assoc();
                    $hash = $row[0]['password'];
        
                    if (password_verify($password, $hash)) {
                        setcookie("username", $row[0]['email'], time()+ 3600);
                        setcookie("password", $row[0]['password'], time()+ 3600);
                        setcookie("userType", $row[0]['user_type'], time()+ 3600);
                        Header("Location: orders.php");
                    }
                }
                
                else {
                    $_SESSION['res'] = "Invalid username or password. Please try again.";
                    Header("Location: index.php");
                }
            } else {
                throw new Exception('Execute error: The prepared statement could not be executed.');
                Header("Location: index.php");
            }

            $mysqli_checks = $query->close();
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

/******** ORDER MANAGEMENT ********/

if(isset($_POST['remove_item'])){
    $id = $_POST['item_id']; 
    // $delete = "DELETE FROM `order_item` WHERE `item_id`='$id'";
    // Header("Location: ../client/index.php");
}

/******** BOOKING MANAGEMENT ********/
?>