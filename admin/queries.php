<?php
session_name("sess_id");
session_start();
require_once '../api/api.php';
$api = new api();

/******** TATTOO CATALOGUE MANAGEMENT ********/

if(isset($_POST['catalog_tattoo'])){
    if(isset($_FILES['image'])){
        $errors = array();

        // new catalog entry details
        $name = $api->clean($_POST['tattoo_name']);
        $price = doubleval($_POST['tattoo_price']);
        $width = intval($_POST['tattoo_width']);
        $height = intval($_POST['tattoo_height']);
        $description = $api->clean($_POST['tattoo_description']);
        $color_scheme = $api->clean($_POST['color_scheme']);
        $complexity = $api->clean($_POST['complexity_level']);

        // file upload
        $target_dir = "../images/uploads/";
        $allowed_formats = array('jpg','jpeg','png');

        $fileName = basename($_FILES['image']['name']);
        $target_file = $target_dir.$fileName;
        $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        $fileTmpName = $_FILES['image']['tmp_name'];
        $fileSize = $_FILES['image']['size'];
        $fileError = $_FILES['image']['error'];

        // validations
        if(empty($name)) {
            $_SESSION['name_err'] = "Tattoo name is required.";
            array_push($errors, $_SESSION['name_err']);
        }

        elseif(preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $name)){
            $_SESSION['name_err'] = "Tattoo name must not contain any special characters";
            array_push($errors, $_SESSION['name_err']);
        }

        elseif (mb_strlen($name) > 50) {
            $_SESSION['description_err'] = "Tattoo name must not exceed 50 characters. ";
            array_push($errors, $_SESSION['description_err']);
        }

        if(empty($price)) {
            $_SESSION['price_err'] = "Tattoo price is required. ";
            array_push($errors, $_SESSION['price_err']);
        }

        elseif(!is_numeric($price)) {
            $_SESSION['price_err'] = "Tattoo price must be a numeric value. ";
            array_push($errors, $_SESSION['price_err']);
        }

        elseif($price < 0){
            $_SESSION['price_err'] = "Tattoo price must not be negative.";
            array_push($errors, $_SESSION['price_err']);
        }

        if(empty($width)) {
            $_SESSION['width_err'] = "Tattoo width is required.";
            array_push($errors, $_SESSION['width_err']);
        }
        
        elseif (!is_int($width)) {
            $_SESSION['width_err'] = "Tattoo width must be an integer.";
            array_push($errors, $_SESSION['width_err']);
        }
        
        elseif($width < 0){
            $_SESSION['width_err'] = "Tattoo width must not be negative.";
            array_push($errors, $_SESSION['width_err']);
        }

        elseif($width > 24){
            $_SESSION['width_err'] = "Tattoo width must not exceed 24 inches.";
            array_push($errors, $_SESSION['width_err']);
        }

        if(empty($height)) {
            $_SESSION['height_err'] = "Tattoo height is required.";
            array_push($errors, $_SESSION['height_err']);
        }
        
        elseif (!is_int($height)) {
            $_SESSION['width_err'] = "Tattoo height must be an integer.";
            array_push($errors, $_SESSION['height_err']);
        }
        
        elseif($height < 0){
            $_SESSION['height_err'] = "Tattoo height must not be negative.";
            array_push($errors, $_SESSION['height_err']);
        }

        elseif($height > 36){
            $_SESSION['height_err'] = "Tattoo width must not exceed 36 inches.";
            array_push($errors, $_SESSION['height_err']);
        }

        if(mb_strlen($description) > 300) {
            $_SESSION['description_err'] = "Tattoo description must not exceed 300 characters. ";
            array_push($errors, $_SESSION['description_err']);
        }

        if(empty($color_scheme)){
            $_SESSION['color_scheme_err'] = "Tattoo color scheme is required.";
            array_push($errors, $_SESSION['color_scheme_err']);
        }

        elseif(!in_array($color_scheme, array("Monochrome", "Multicolor"))) {
            $_SESSION['color_scheme_err'] = "Tattoo color scheme must be either Monochrome or Multicolor. ";
            array_push($errors, $_SESSION['color_scheme_err']);
        }

        if(empty($complexity)){
            $_SESSION['complexity_level_err'] = "Tattoo complexity level is required.";
            array_push($errors, $_SESSION['complexity_level_err']);
        }

        elseif(!in_array($complexity, array("Simple", "Complex"))) {
            $_SESSION['complexity_level_err'] = "Tattoo complexity level must be either Simple or Complex. ";
            array_push($errors, $_SESSION['complexity_level_err']);
        }

        // image validation - check file format
        if(!in_array($fileType, $allowed_formats)){
            $_SESSION['tattoo_image_err'] = "You can't upload files of this type.";
            array_push($errors, $_SESSION['tattoo_image_err']);
        }

        // image validation - check image size
        if ($fileSize > 50000000) {
            $_SESSION['tattoo_image_err'] = "File size is too large.";
            array_push($errors, $_SESSION['tattoo_image_err']);
        }

        // image validation - check if error occured
        if($fileError){
            $_SESSION['tattoo_image_err'] = "An error occured. Please try again.";
            array_push($errors, $_SESSION['tattoo_image_err']);
        }

        if (empty($errors)) {
            try {
                if (move_uploaded_file($fileTmpName, $target_file)) {
                    if(empty($description)){
                        $description = "None";
                    }

                    $cstrong = true;
                    $tattoo_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

                    $query = $api->insert();
                    $query = $api->table($query, "tattoo");
                    $query = $api->columns($query, array("tattoo_id", "tattoo_name", "tattoo_price", "tattoo_width", "tattoo_height", "tattoo_image", "tattoo_description", "color_scheme", "complexity_level"));
                    $query = $api->values($query);
                    $query = $api->columns($query, array("?", "?", "?", "?", "?", "?", "?", "?", "?"));

                    $statement = $api->prepare($query);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }
            
                    $api->bind_params($statement, "ssdiissss", array($tattoo_id, $name, $price, $width, $height, $fileTmpName, $description, $color_scheme, $complexity));
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
                } else {
                    throw new Exception("Sorry, an error occured during the file upload, please try again later.");
                }
            } catch (Exception $e) {
                exit();
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ./catalogue.php");
            }
        }
    } else {
        $_SESSION['tattoo_image_err'] = "File to upload is required.";
    }

    Header("Location: ./catalogue.php");
}

if(isset($_POST['update_tattoo'])){
    $errors = array();

    $id = $api->clean($_POST['tattoo_id']);
    $name = $api->clean($_POST['tattoo_name']);
    $price = doubleval($_POST['tattoo_price']);
    $width = intval($_POST['tattoo_width']);
    $height = intval($_POST['tattoo_height']);
    $description = $api->clean($_POST['tattoo_description']);
    $color_scheme = $api->clean($_POST['color_scheme']);
    $complexity = $api->clean($_POST['complexity_level']);

    // validations
    if(empty($name)) {
        $_SESSION['name_err'] = "Tattoo name is required.";
        array_push($errors, $_SESSION['name_err']);
    }

    elseif(preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $name)){
        $_SESSION['name_err'] = "Tattoo name must not contain any special characters";
        array_push($errors, $_SESSION['name_err']);
    }

    elseif (mb_strlen($name) > 50) {
        $_SESSION['description_err'] = "Tattoo name must not exceed 50 characters. ";
        array_push($errors, $_SESSION['description_err']);
    }

    if(empty($price)) {
        $_SESSION['price_err'] = "Tattoo price is required. ";
        array_push($errors, $_SESSION['price_err']);
    }

    elseif(!is_numeric($price)) {
        $_SESSION['price_err'] = "Tattoo price must be a numeric value. ";
        array_push($errors, $_SESSION['price_err']);
    }

    elseif($price < 0){
        $_SESSION['price_err'] = "Tattoo price must not be negative.";
        array_push($errors, $_SESSION['price_err']);
    }

    if(empty($width)) {
        $_SESSION['width_err'] = "Tattoo width is required.";
        array_push($errors, $_SESSION['width_err']);
    }
    
    elseif (!is_int($width)) {
        $_SESSION['width_err'] = "Tattoo width must be an integer.";
        array_push($errors, $_SESSION['width_err']);
    }
    
    elseif($width < 0){
        $_SESSION['width_err'] = "Tattoo width must not be negative.";
        array_push($errors, $_SESSION['width_err']);
    }

    elseif($width > 24){
        $_SESSION['width_err'] = "Tattoo width must not exceed 24 inches.";
        array_push($errors, $_SESSION['width_err']);
    }

    if(empty($height)) {
        $_SESSION['height_err'] = "Tattoo height is required.";
        array_push($errors, $_SESSION['height_err']);
    }
    
    elseif (!is_int($height)) {
        $_SESSION['width_err'] = "Tattoo height must be an integer.";
        array_push($errors, $_SESSION['height_err']);
    }
    
    elseif($height < 0){
        $_SESSION['height_err'] = "Tattoo height must not be negative.";
        array_push($errors, $_SESSION['height_err']);
    }

    elseif($height > 36){
        $_SESSION['height_err'] = "Tattoo width must not exceed 36 inches.";
        array_push($errors, $_SESSION['height_err']);
    }
 
    if(empty($description)){
        $_SESSION['description_err'] = "Tattoo description is required.";
        array_push($errors, $_SESSION['description_err']);
    }

    elseif(mb_strlen($description) > 300) {
        $_SESSION['description_err'] = "Tattoo description must not exceed 300 characters. ";
        array_push($errors, $_SESSION['description_err']);
    }

    if(empty($color_scheme)){
        $_SESSION['color_scheme_err'] = "Tattoo color scheme is required.";
        array_push($errors, $_SESSION['color_scheme_err']);
    }

    elseif(!in_array($color_scheme, array("Monochrome", "Multicolor"))) {
        $_SESSION['color_scheme_err'] = "Tattoo color scheme must be either Monochrome or Multicolor. ";
        array_push($errors, $_SESSION['color_scheme_err']);
    }

    if(empty($complexity)){
        $_SESSION['complexity_level_err'] = "Tattoo complexity level is required.";
        array_push($errors, $_SESSION['complexity_level_err']);
    }

    elseif(!in_array($complexity, array("Simple", "Complex"))) {
        $_SESSION['complexity_level_err'] = "Tattoo complexity level must be either Simple or Complex. ";
        array_push($errors, $_SESSION['complexity_level_err']);
    }
    
    if(empty($errors)){
        try {
            $statement = $api->prepare("UPDATE tattoo SET tattoo_name=?, tattoo_price=?, tattoo_width=?, tattoo_height=?, tattoo_description=?, color_scheme=?, complexity_level=? WHERE tattoo_id=?");
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
    
            $mysqli_checks = $api->bind_params($statement, "sdiissss", array($name, $price, $width, $height, $description, $color_scheme, $complexity, $id));
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
            Header("Location: ./catalogue.php#".$name);
        }
    }
 
    Header("Location: ./catalogue.php");
}

if(isset($_POST['delete_tattoo'])){
    $id = $api->clean($_POST['tattoo_id']);

    try {
        $statement = $api->prepare("DELETE FROM tattoo WHERE tattoo_id=?");
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "s", $id);
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
        Header("Location: ./catalogue.php");
    }

    Header("Location: ./catalogue.php");
}

/******** ORDER MANAGEMENT ********/

if(isset($_POST['update_item'])){
    $errors = array();

    $item_id = $api->clean($_POST['item_id']);
    $width = intval($_POST['width']);
    $height = intval($_POST['height']);
    $quantity = intval($_POST['quantity']);

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
        try {
            // retrieve order item data
            $statement = $api->prepare("SELECT client_id, order_item.order_id, tattoo_id, paid, item_status FROM (order_item JOIN workorder ON order_item.order_id=workorder.order_id) WHERE item_id=?");
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "s", $item_id);
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
                    $item = $api->fetch_assoc($res);

                    $api->free_result($statement);
                    $mysqli_checks = $api->close($statement);
                    if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $res = null;
                        $statement = null;
                    }

                    // find existing order item
                    $statement = $api->prepare("SELECT item_id, tattoo_quantity FROM (order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE item_id!=? AND order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT 1");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $api->bind_params($statement, "ssssii", array($item_id, $item['order_id'], $item['paid'], $item['item_status'], $width, $height));
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
                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                            }
        
                            $quantity += $row['tattoo_quantity'];
                            $mysqli_checks = $api->bind_params($statement, "iss", array($quantity, $item['order_id'], $row['item_id']));
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
                            $statement = $api->prepare("DELETE FROM order_item WHERE order_id=? AND item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                            }
        
                            $mysqli_checks = $api->bind_params($statement, "ss", array($item['order_id'], $item_id));
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
        
                            $statement = $api->prepare("UPDATE order_item SET tattoo_width=?, tattoo_height=?, tattoo_quantity=? WHERE order_id=? AND item_id=?");
                            if ($statement===false) {
                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                            }
                    
                            $mysqli_checks = $api->bind_params($statement, "iiiss", array($width, $height, $quantity, $item['order_id'], $item_id));
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

                    // update amount due total for current order
                    $mysqli_checks = $api->update_total($item['order_id'], $item['client_id']);
                    if ($mysqli_checks===false) {
                        throw new Exception('Error: Updating amount due total of current order failed.');
                    }
                } else {
                    $api->free_result($statement);
                    $mysqli_checks = $api->close($statement);
                    if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $res = null;
                        $statement = null;
                    }
                    
                    throw new Exception('Retrieving order item with the given ID failed.');
                }
            }
        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./orders.php");
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ./orders.php");
}

if(isset($_POST['delete_item'])){
    $item_id = $api->clean($_POST['item_id']);

    try {
        // retrieve order item data
        $statement = $api->prepare("SELECT client_id, order_item.order_id FROM (order_item JOIN workorder ON order_item.order_id=workorder.order_id) WHERE item_id=?");
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "s", $item_id);
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
                $item = $api->fetch_assoc($res);

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
                } else {
                    $res = null;
                    $statement = null;
                }
    
                $statement = $api->prepare("DELETE FROM order_item WHERE order_id=? AND item_id=?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
        
                $mysqli_checks = $api->bind_params($statement, "ss", array($item['order_id'], $item_id));
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

                // update amount due total for current order
                $mysqli_checks = $api->update_total($item['order_id'], $item['client_id']);
                if ($mysqli_checks===false) {
                    throw new Exception('Error: Updating amount due total of current order failed.');
                }
            } else {
                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
                } else {
                    $res = null;
                    $statement = null;
                }
                
                throw new Exception('Retrieving order item with the given ID failed.');
            }
        }
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./orders.php");
    }

    Header("Location: ./orders.php");
}

/******** BOOKING MANAGEMENT ********/

if(isset($_POST['update_reservation'])){
    $errors = array();

    $reservation_id = $api->clean($_POST['reservation_id']);
    $item_id = $api->clean($_POST['item_id']);
    $service_type = $api->clean($_POST['service_type']);
    $amount_addon = doubleval($_POST['amount_addon']);
    $time = $_POST['scheduled_time'];
    $date = $_POST['scheduled_date'];   
    $address = $api->clean($_POST['reservation_address']);

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

    if(!is_numeric($amount_addon)) {
        $_SESSION['amount_addon_err'] = "Reservation addon amount must be a numeric value. ";
        array_push($errors, $_SESSION['amount_addon_err']);
    }

    if(empty($errors)){
        $date = date("Y:m:d", strtotime($date));

        try {
            $statement = $api->prepare("UPDATE reservation SET service_type=?, reservation_address=?, scheduled_date=?, scheduled_time=?, amount_addon=? WHERE reservation_id=? AND item_id=?");
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
    
            $mysqli_checks = $api->bind_params($statement, "ssssdss", array($service_type, $address, $date, $time, $amount_addon, $reservation_id, $item_id));
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
            Header("Location: ./reservations.php");
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ./reservations.php");
}

if(isset($_POST['start_worksession'])){
    $cstrong = true;

    $reservation_id = $api->clean($_POST['reservation_id']);
    $session_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
    $address = $api->clean($_POST['reservation_address']);

    if(empty($address)) {
        $_SESSION['address_err'] = "Session address is required.";
    }

    if(!isset($_SESSION['address_err'])){
        $time = date("G:i:s");
        $date = str_replace(":", "-", date("Y:m:d"));

        try {
            $query = $api->insert();
            $query = $api->table($query, "worksession");
            $query = $api->columns($query, array("session_id", "reservation_id", "session_date", "session_status", "session_start_time", "session_end_time", "session_address"));
            $query = $api->values($query);
            $query = $api->columns($query, array("?", "?", "?", "?", "?", "?", "?"));

            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }
    
            $mysqli_checks = $api->bind_params($statement, "sssssss", array($session_id, $reservation_id, $date, "In Session", $time, "00:00:00", $address));
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
            Header("Location: ./reservations.php");
        }
    }

    Header("Location: ./reservations.php");
}

if(isset($_POST['cancel_reservation'])){
    $order_id = $api->clean($_SESSION['order_id']);
    $reservation_id = $api->clean($_POST['reservation_id']);
    $item_id = $api->clean($_POST['item_id']);
    $quantity = intval($_POST['quantity']);
    $client_id = $api->clean($_POST['client_id']);

    try {
        // get order item
        $query = $api->select();
        $query = $api->params($query, array("tattoo_id", "order_id", "tattoo_width", "tattoo_height", "paid"));
        $query = $api->from($query);
        $query = $api->table($query, "order_item");
        $query = $api->where($query, array("item_id", "order_id"), array("?", "?"));
        $query = $api->limit($query, 1);

        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "ss", array($item_id, $order_id));
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
                $item = $api->fetch_assoc($res);
                echo "works";

                $api->free_result($res);
                $res = null;
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $statement = null;
                }

                // deleting reservation
                $statement = $api->prepare("DELETE FROM reservation WHERE reservation_id=? AND item_id=?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ss", array($reservation_id, $item_id));
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

                // updating order item quantity
                $statement = $api->prepare("SELECT item_id, tattoo_quantity FROM order_item WHERE order_id=? AND tattoo_width=? AND tattoo_height=? AND paid=? AND item_id!=? AND item_status!=? LIMIT ?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "siisssi", array($order_id, $item['tattoo_width'], $item['tattoo_height'], $item['paid'], $item_id, "Reserved", 1));
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
                        $row = $api->fetch_assoc($res);
        
                        $api->free_result($res);
                        $res = null;
                        $mysqli_checks = $api->close($statement);
                        if ($mysqli_checks===false) {
                            throw new Exception('The prepared statement could not be closed.');
                        } else {
                            $statement = null;
                        }

                        $row['tattoo_quantity'] += $quantity;
                        $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE item_id=?");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $api->bind_params($statement, "is", array($row['tattoo_quantity'], $row['item_id']));
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

                        $statement = $api->prepare("DELETE FROM order_item WHERE item_id=?");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $api->bind_params($statement, "s", $item_id);
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
                        $statement = $api->prepare("UPDATE order_item SET item_status=? WHERE item_id=?");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $api->bind_params($statement, "ss", array("Standing", $item_id));
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
                throw new Exception('No order item with the given ID could be found.');
            }
        }
        
        // update amount due total for current order
        $mysqli_checks = $api->update_total($order_id, $client_id);
        if ($mysqli_checks===false) {
            throw new Exception('Error: Updating amount due total of current order failed.');
        }
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./reservations.php");
    }

    Header("Location: ./reservations.php");
}

if(isset($_POST['finish_worksession'])){
    $item_id = $api->clean($_POST['item_id']);
    $reservation_id = $api->clean($_POST['reservation_id']);
    $session_id = $api->clean($_POST['session_id']);

    try {
        // retrieving session item data
        $retrieve_item = $api->select();
        $retrieve_item = $api->params($retrieve_item, array("order_id", "tattoo_id", "tattoo_quantity", "tattoo_width", "tattoo_height", "paid"));
        $retrieve_item = $api->from($retrieve_item);
        $retrieve_item = $api->table($retrieve_item, "order_item");
        $retrieve_item = $api->where($retrieve_item, "item_id", "?");
        $retrieve_item = $api->limit($retrieve_item, 1);

        $statement = $api->prepare($retrieve_item);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "s", $item_id);
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
                // item successfully retrieved
                $item = $api->fetch_assoc($res);

                $api->free_result($res);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $res = null;
                    $statement = null;
                }

                // Finding similar item
                $statement = $api->prepare("SELECT item_id, tattoo_quantity FROM (order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE item_id!=? AND order_item.order_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT ?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ssssiii", array($item_id, $item['order_id'], $item['paid'], "Applied", $item['tattoo_width'], $item['tattoo_height'], 1));
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
                    // Similar item found
                    $row = $api->fetch_assoc($res);

                    $api->free_result($res);
                    $mysqli_checks = $api->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }
                
                    // merging down applied item
                    $statement = $api->prepare("DELETE FROM order_item WHERE item_id=? AND order_id=?");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $api->bind_params($statement, "ss", array($item_id, $item['order_id']));
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

                    // updating found Applied item
                    $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE item_id=? AND order_id=?");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $row['tattoo_quantity'] += $item['tattoo_quantity'];
                    $mysqli_checks = $api->bind_params($statement, "iss", array($row['tattoo_quantity'], $row['item_id']), $item['order_id']);
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
                    // No similar item found
                    $api->free_result($res);

                    $mysqli_checks = $api->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }

                    // updating item status
                    $statement = $api->prepare("UPDATE order_item SET item_status=? WHERE item_id=? AND order_id=?");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $api->bind_params($statement, "sss", array("Applied", $item_id, $item['order_id']));
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

                // finishing session
                $end_time = date("G:i:s");

                $statement = $api->prepare("UPDATE worksession SET session_status=?, session_end_time=? WHERE session_id=? AND reservation_id=?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
        
                $mysqli_checks = $api->bind_params($statement, "ssss", array("Finished", $end_time, $session_id, $reservation_id));
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
            } else {
                throw new Exception('Retrieving order item with the given ID failed.');
            }
        }
    } catch (Exception $e) {
        exit();
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./reservations.php");
    }

    Header("Location: ./reservations.php");
}

/******** PAYMENT LOGGING ********/

if(isset($_POST['log_payment'])){
    if(isset($_POST['item'])){
        try {
            $errors = array();
            $cstrong = true;

            $order_id = $api->clean($_POST['order_id']);
            $client_id = $api->clean($_POST['client_id']);

            $first_name = $api->clean(ucfirst($_POST['first_name']));
            $last_name = $api->clean(ucfirst($_POST['last_name']));
            $street_address = $api->clean($_POST['street_address']);
            $city = $api->clean($_POST['city']);
            $province = $api->clean($_POST['province']);
            $zip = $api->clean($_POST['zip']);
            $amount_paid = doubleval($_POST['amount_paid']);
            $payment_method = $api->clean($_POST['payment_method']);

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
            if(empty($amount_paid)) {
                $_SESSION['amount_paid_err'] = "Payment amount is required. ";
                array_push($errors, $_SESSION['amount_paid_err']);
            }

            elseif(!is_numeric($amount_paid)) {
                $_SESSION['amount_paid_err'] = "Payment amount must be a numeric value. ";
                array_push($errors, $_SESSION['amount_paid_err']);
            }

            elseif($amount_paid < 0){
                $_SESSION['amount_paid_err'] = "Payment amount must not be negative. ";
                array_push($errors, $_SESSION['amount_paid_err']);
            }

            if(empty($payment_method)) {
                $_SESSION['payment_method_err'] = "Payment method is required. ";
                array_push($errors, $_SESSION['payment_method_err']);
            }

            elseif(!in_array($payment_method, array("Cash", "Check"))){
                $_SESSION['payment_method_err'] = "Payment method msut be either Cash or Check. ";
                array_push($errors, $_SESSION['payment_method_err']);
            }

            if(empty($errors)){
                $errors = [];
                $change = doubleval($amount_paid);

                foreach($_POST['item'] as $item){
                    $index = array_search($item, $_POST['index']);
                    
                    $checkout_quantity = intval($_POST['checkout_quantity'][$index]);
                    $quantity = intval($_POST['quantity'][$index]);

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

                    elseif($checkout_quantity > $quantity){
                        $_SESSION['quantity_err'] = "Checkout quantity must not exceed the quantity of the ordered item. ";
                        array_push($errors, $_SESSION['quantity_err']);
                    }

                    if(empty($errors)){
                        $statement = $api->prepare("SELECT order_item.tattoo_id, tattoo_price, order_item.tattoo_width, order_item.tattoo_height, paid, item_status, amount_addon FROM ((order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) LEFT JOIN reservation ON order_item.item_id=reservation.item_id) WHERE order_id=? AND order_item.item_id=? LIMIT 1");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $api->bind_params($statement, "ss", array($order_id, $item));
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

                            $api->free_result($statement);
                            $mysqli_checks = $api->close($statement);
                            if ($mysqli_checks===false) {
                            throw new Exception('The prepared statement could not be closed.');
                            } else {
                                $statement = null;
                            }

                            $tattoo_id = $api->clean($row['tattoo_id']);
                            $width = intval($row['tattoo_width']);
                            $height = intval($row['tattoo_height']);
                            $paid = $api->clean($row['paid']);
                            $item_status = $api->clean($row['item_status']);
                            $addon = (!empty($row['amount_addon']) && $row['amount_addon'] != 0) ? doubleval($row['amount_addon']) : 0.00;
                            $item_amount_due_total = doubleval($row['tattoo_price']) * $checkout_quantity;

                            if(in_array($item_status, array("Reserved", "Applied")) && strcasecmp($paid, "Partially Paid") == 0) {
                                $item_amount_due_total += doubleval($row['tattoo_price']) + $addon;
                            }

                            if ($change >= $item_amount_due_total){
                                $change -= $item_amount_due_total;
    
                                // Case - Item Standing Unpaid
                                if(strcasecmp($item_status, "Standing") == 0) {
                                    // Finding similar item
                                    $statement = $api->prepare("SELECT order_item.item_id, tattoo_quantity FROM (order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE order_id=? AND item_id!=? AND order_item.tattoo_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT 1");
                                    if ($statement===false) {
                                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                    }

                                    $mysqli_checks = $api->bind_params($statement, "sssssii", array($order_id, $item, $tattoo_id, "Partially Paid", $item_status, $width, $height));
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
                                        // Similar item found
                                        $successor = $api->fetch_assoc($res);
                                        $api->free_result($res);
    
                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $res = null;
                                            $statement = null;
                                        }
    
                                        if($checkout_quantity == $quantity){
                                            // merging down checkout item
                                            $statement = $api->prepare("DELETE FROM order_item WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "ss", array($order_id, $item));
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
                                            $quantity -= $checkout_quantity;
                                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "iss", array($quantity, $order_id, $item));
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
    
                                        // updating found Standing Partially Paid item
                                        $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                        }
    
                                        $successor['tattoo_quantity'] += $checkout_quantity;
                                        $mysqli_checks = $api->bind_params($statement, "iss", array($successor['tattoo_quantity'], $order_id, $successor['item_id']));
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
                                        // No similar item found
                                        $api->free_result($res);
    
                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }
    
                                        if($checkout_quantity == $quantity){
                                            // updating item payment status
                                            $statement = $api->prepare("UPDATE order_item SET paid=? WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "sss", array("Partially Paid", $order_id, $item));
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
                                            $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
                                            $quantity -= $checkout_quantity;
    
                                            $statement = $api->prepare("INSERT INTO order_item (item_id, order_id, tattoo_id, tattoo_quantity, tattoo_width, tattoo_height, paid, item_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "sssiiiss", array($item_id, $order_id, $tattoo_id, $checkout_quantity, $width, $height, "Partially Paid", "Standing"));
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
    
                                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_item=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "iss", array($quantity, $order_id, $item));
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
    
                                // Case - Reserved
                                elseif (strcasecmp($item_status, "Reserved") == 0){
                                    // updating item payment status
                                    $statement = $api->prepare("UPDATE order_item SET paid=? WHERE item_id=?");
                                    if ($statement===false) {
                                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                    }
    
                                    $mysqli_checks = $api->bind_params($statement, "ss", array("Fully Paid", $item));
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
    
                                // Case - Applied
                                elseif (strcasecmp($item_status, "Applied") == 0){
                                    // Finding similar item
                                    $statement = $api->prepare("SELECT order_item.item_id, tattoo_quantity FROM (order_item INNER JOIN tattoo ON order_item.tattoo_id=tattoo.tattoo_id) WHERE order_id=? AND item_id!=? AND order_item.tattoo_id=? AND paid=? AND item_status=? AND order_item.tattoo_width=? AND order_item.tattoo_height=? LIMIT 1");
                                    if ($statement===false) {
                                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                    }
    
                                    $mysqli_checks = $api->bind_params($statement, "sssssii", array($order_id, $item, $tattoo_id, "Fully Paid", $item_status, $width, $height));
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
                                        // Similar item found
                                        $successor = $api->fetch_assoc($res);
                                        $api->free_result($res);
    
                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }
    
                                        if($checkout_quantity == $quantity){
                                            // updating reservation foreign key
                                            $statement = $api->prepare("UPDATE reservation SET item_id=? WHERE item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "ss", array($successor['item_id'], $item));
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
                                            $statement = $api->prepare("DELETE FROM order_item WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "ss", array($order_id, $item));
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
                                            $quantity -= $checkout_quantity;
                                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "iss", array($item_quantity, $order_id, $item));
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
    
                                        // updating found Applied Fully Paid item
                                        $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                                        if ($statement===false) {
                                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                        }
    
                                        $successor['tattoo_quantity'] += $checkout_quantity;
                                        $mysqli_checks = $api->bind_params($statement, "iss", array($successor['tattoo_quantity'], $order_id, $successor['item_id']));
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
                                        // No similar item found
                                        $api->free_result($res);
    
                                        $mysqli_checks = $api->close($statement);
                                        if ($mysqli_checks===false) {
                                            throw new Exception('The prepared statement could not be closed.');
                                        } else {
                                            $statement = null;
                                        }
    
                                        if($checkout_quantity == $quantity){
                                            // updating item payment status
                                            $statement = $api->prepare("UPDATE order_item SET paid=? WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "sss", array("Fully Paid", $order_id, $item));
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
                                            $item_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
                                            $quantity -= $checkout_quantity;
    
                                            $statement = $api->prepare("INSERT INTO order_item (item_id, order_id, tattoo_id, tattoo_quantity, tattoo_width, tattoo_height, paid, item_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "sssiiiss", array($item_id, $order_id, $tattoo_id, $checkout_quantity, $width, $height, "Fully Paid", "Applied"));
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
    
                                            $statement = $api->prepare("UPDATE order_item SET tattoo_quantity=? WHERE order_id=? AND item_id=?");
                                            if ($statement===false) {
                                                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                                            }
    
                                            $mysqli_checks = $api->bind_params($statement, "iss", array($quantity, $order_id, $item));
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
                            }
                        } else {
                            throw new Exception('No order item under the given IDs found.');
                        }
                    } else {
                        $errors = [];
                    }
                }

                // update amount due total for current order
                $mysqli_checks = $api->update_total($order_id, $client_id);
                if ($mysqli_checks===false) {
                    throw new Exception('Error: Updating amount due total of current order failed.');
                }

                // logging transaction - inserting in payment table
                $payment_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));

                $statement = $api->prepare("INSERT INTO payment (payment_id, order_id, amount_paid, payment_method, payment_change, client_fname, client_lname, street_address, city, province, zip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "ssdsdssssss", array($payment_id, $order_id, $amount_paid, $payment_method, $change, $first_name, $last_name, $street_address, $city, $province, $zip));
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

        } catch (Exception $e) {
            exit();
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ../client/checkout.php");
        }
    } else {
        $_SESSION['res'] = "No items selected.";
    }

    Header("Location: ./orders.php");
}

/******** ILLEGAL ACCESS CATCHING ********/

if(empty($_POST)){
    Header("Location: ./index.php");
    die();
}
?>