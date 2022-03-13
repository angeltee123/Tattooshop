<?php
session_name("sess_id");
session_start();
require_once '../api/api.php';
$api = new api();

/******** TATTOO CATALOGUE MANAGEMENT ********/

// creating new tattoo catalog entry
if(isset($_POST['catalog_tattoo'])){
    if(isset($_FILES['image'])){
        $errors = array();

        // new catalog entry details
        $name = $api->sanitize_data($_POST['tattoo_name'], "string");
        $price = $api->sanitize_data($_POST['tattoo_price'], "float");
        $width = $api->sanitize_data($_POST['tattoo_width'], "int");
        $height = $api->sanitize_data($_POST['tattoo_height'], "int");
        $description = $api->sanitize_data($_POST['tattoo_description'], "string");
        $color_scheme = $api->sanitize_data($_POST['color_scheme'], "string");
        $complexity = $api->sanitize_data($_POST['complexity_level'], "string");

        // file upload
        $path = "../images/uploads/";
        $ext_whitelist= array('jpg','jpeg','png','gif');
        $type_whitelist = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif');

        $file_name = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_type = strtolower($_FILES['image']['type']);

        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_error = $_FILES['image']['error'];

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
        
        elseif (!$api->validate_data($width, 'int')) {
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
        
        elseif (!$api->validate_data($height, 'int')) {
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

        // file validations - path check
        if (!$path) {
            $_SESSION['tattoo_image_err'] = "Please specify a valid upload path.";
            array_push($errors, $_SESSION['tattoo_image_err']);        
        }

        // file validations - check if there is a file
        if((!empty($_FILES['image'])) && ($file_error == 0)) {
            // file validations - check file extension
            if(!in_array($file_ext, $ext_whitelist)){
                $_SESSION['tattoo_image_err'] = "Uploaded file has invalid extension.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

            // file validations - check if file is a valid image
            if(!getimagesize($file_tmp_name)) {
                $_SESSION['tattoo_image_err'] = "Uploaded file is not a valid image.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

             // file validations - check file type
            if(!in_array($file_type, $type_whitelist)){
                $_SESSION['tattoo_image_err'] = "You can't upload files of this type.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

            // file validations - check if file exceeds image size limit
            if ($file_size > 50000000) {
                $_SESSION['tattoo_image_err'] = "File size is too large.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }
        } else {
            $_SESSION['tattoo_image_err'] = "An error occured while uploading your file. Please try again.";
            array_push($errors, $_SESSION['tattoo_image_err']);
        }

        if(empty($errors)) {
            $target_file = $path.$file_name;

            try {
                if (move_uploaded_file($file_tmp_name, $target_file)) {
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
            
                    $mysqli_checks = $api->bind_params($statement, "ssdiissss", array($tattoo_id, $name, $price, $width, $height, $target_file, $description, $color_scheme, $complexity));
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
                $_SESSION['res'] = $e->getMessage();
                Header("Location: ./catalogue.php");
                exit();
            }
        }
    } else {
        $_SESSION['tattoo_image_err'] = "File to upload is required.";
    }

    Header("Location: ./catalogue.php");
}

// update tattoo catalog entry
if(isset($_POST['update_tattoo'])){
    $errors = array();

    $id = $api->sanitize_data($_POST['tattoo_id'], "string");
    $name = $api->sanitize_data($_POST['tattoo_name'], "string");
    $price = $api->sanitize_data($_POST['tattoo_price'], "float");
    $width = $api->sanitize_data($_POST['tattoo_width'], "int");
    $height = $api->sanitize_data($_POST['tattoo_height'], "int");
    $description = $api->sanitize_data($_POST['tattoo_description'], "string");
    $color_scheme = $api->sanitize_data($_POST['color_scheme'], "string");
    $complexity = $api->sanitize_data($_POST['complexity_level'], "string");
    
    
    $upload_file = boolval(!empty($_FILES['image']['name']) && !empty($_FILES['image']['type']));
    if($upload_file){
        // file upload
        $path = "../images/uploads/";
        $ext_whitelist= array('jpg','jpeg','png','gif');
        $type_whitelist = array('image/jpg', 'image/jpeg', 'image/png', 'image/gif');

        $file_name = basename($_FILES['image']['name']);
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $file_type = strtolower($_FILES['image']['type']);

        $file_tmp_name = $_FILES['image']['tmp_name'];
        $file_size = $_FILES['image']['size'];
        $file_error = $_FILES['image']['error'];
    }

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
    
    elseif (!$api->validate_data($width, 'int')) {
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
    
    elseif (!$api->validate_data($height, 'int')) {
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

    if($upload_file){
        // file validations - path check
        if (!$path) {
            $_SESSION['tattoo_image_err'] = "Please specify a valid upload path.";
            array_push($errors, $_SESSION['tattoo_image_err']);        
        }

        // file validations - check if there is a file
        if((!empty($_FILES['image'])) && ($file_error == 0)) {
            // file validations - check file extension
            if(!in_array($file_ext, $ext_whitelist)){
                $_SESSION['tattoo_image_err'] = "Uploaded file has invalid extension.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

            // file validations - check if file is a valid image
            if(!getimagesize($file_tmp_name)) {
                $_SESSION['tattoo_image_err'] = "Uploaded file is not a valid image.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

             // file validations - check file type
            if(!in_array($file_type, $type_whitelist)){
                $_SESSION['tattoo_image_err'] = "You can't upload files of this type.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }

            // file validations - check if file exceeds image size limit
            if ($file_size > 50000000) {
                $_SESSION['tattoo_image_err'] = "File size is too large.";
                array_push($errors, $_SESSION['tattoo_image_err']);
            }
        } else {
            $_SESSION['tattoo_image_err'] = "An error occured while uploading your file. Please try again.";
            array_push($errors, $_SESSION['tattoo_image_err']);
        }
    }
  
    if(empty($errors)){
        try {
            // check if image has been changed
            if($upload_file){
                $target_file = $path.$file_name;

                if (move_uploaded_file($file_tmp_name, $target_file)) {
                    $statement = $api->prepare("UPDATE tattoo SET tattoo_name=?, tattoo_price=?, tattoo_width=?, tattoo_height=?, tattoo_image=?, tattoo_description=?, color_scheme=?, complexity_level=? WHERE tattoo_id=?");
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }
            
                    $mysqli_checks = $api->bind_params($statement, "sdiisssss", array($name, $price, $width, $height, $target_file, $description, $color_scheme, $complexity, $id));
                    if ($mysqli_checks===false) {
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
                } else {
                    throw new Exception("Sorry, an error occured during the file upload, please try again later.");
                }                
            } else {
                $statement = $api->prepare("UPDATE tattoo SET tattoo_name=?, tattoo_price=?, tattoo_width=?, tattoo_height=?, tattoo_description=?, color_scheme=?, complexity_level=? WHERE tattoo_id=?");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
        
                $mysqli_checks = $api->bind_params($statement, "sdiissss", array($name, $price, $width, $height, $description, $color_scheme, $complexity, $id));
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
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
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./catalogue.php#".$name);
            exit();
        }
    }
 
    Header("Location: ./catalogue.php");
}

// update tattoo catalog entry
if(isset($_POST['delete_tattoo'])){
    $id = $api->sanitize_data($_POST['tattoo_id'], "string");

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
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./catalogue.php");
        exit();
    }

    Header("Location: ./catalogue.php");
}

/******** ORDER MANAGEMENT ********/

// update client orders
if(isset($_POST['update_item'])){
    $errors = array();

    $item_id = $api->sanitize_data($_POST['item_id'], "string");
    $width = $api->sanitize_data($_POST['width'], "int");
    $height = $api->sanitize_data($_POST['height'], "int");
    $quantity = $api->sanitize_data($_POST['quantity'], "int");

    // validations
    if(empty($width)) {
        $_SESSION['width_err'] = "Item width is required.";
        array_push($errors, $_SESSION['width_err']);
    }
    
    elseif (!$api->validate_data($width, 'int')) {
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
    
    elseif (!$api->validate_data($height, 'int')) {
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
    
    elseif (!$api->validate_data($quantity, 'int')) {
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
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./orders.php");
            exit();
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ./orders.php");
}

// remove client orders
if(isset($_POST['delete_item'])){
    $item_id = $api->sanitize_data($_POST['item_id'], "string");

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
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./orders.php");
        exit();
    }

    Header("Location: ./orders.php");
}

/******** REFERRAL MANAGEMENT ********/

// update client referral details
if(isset($_POST['update_referral'])){
    $errors = array();

    try {
        $referral_id = $api->sanitize_data($_POST['referral_id'], "string");
        $first_name = $api->sanitize_data($_POST['referral_fname'], "string");
        $mi = $api->sanitize_data($_POST['referral_mi'], "string");
        $last_name = $api->sanitize_data($_POST['referral_lname'], "string");
        $age = $api->sanitize_data($_POST['referral_age'], "int");
        $email = $api->sanitize_data($_POST['referral_email'], "email");
        $contact_number = $api->sanitize_data($_POST['referral_contact_no'], "int");
        $confirmation_status = $api->sanitize_data($_POST['confirmation_status'], "string");

        // retrieving referral data
        $statement = $api->prepare("SELECT client_id, order_id, referral_fname FROM referral WHERE referral_id=?");
        if ($statement===false) {
            throw new Exception('prepare() error: The statement could not be prepared.');
        }

        $mysqli_checks = $api->bind_params($statement, "s", $referral_id);
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

        $referral_data = $api->fetch_assoc($res);
        if($referral_data===false) {
            throw new Exception('get_result() error: Getting result set from statement failed.');
        }

        $api->free_result($statement);
        $mysqli_checks = $api->close($statement);
        if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
        } else {
            $statement = null;
        }

        // first name validation
        if(empty($first_name)) {
            $_SESSION['first_name_err'] = "Referral first name is required. ";
            array_push($errors, $_SESSION['first_name_err']);
        }

        elseif (mb_strlen($first_name) < 2) {
            $_SESSION['first_name_err'] = "Referral first name must be at least 2 characters long. ";
            array_push($errors, $_SESSION['first_name_err']);
        }

        elseif(ctype_space($first_name) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $first_name)){
            $_SESSION['first_name_err'] = "Referral first name must not contain any spaces or special characters.";
            array_push($errors, $_SESSION['first_name_err']);
        }

        // last name validation
        if(empty($last_name)) {
            $_SESSION['last_name_err'] = "Referral last name is required. ";
            array_push($errors, $_SESSION['last_name_err']);
        }

        elseif (mb_strlen($last_name) < 2) {
            $_SESSION['last_name_err'] = "Referral last must be at least 2 characters long. ";
            array_push($errors, $_SESSION['last_name_err']);
        }

        elseif(ctype_space($last_name) || preg_match("/['^£$%&*()}{@#~?><>,|=_+¬-]/", $last_name)){
            $_SESSION['last_name_err'] = "Referral last must not contain any spaces or special characters";
            array_push($errors, $_SESSION['last_name_err']);
        }

        // check if referral name has been changed
        if(strcasecmp($referral_data['referral_fname'], $first_name) != 0){
            // referral uniqueness check
            $statement = $api->prepare("SELECT * FROM referral WHERE client_id=? AND order_id=? AND referral_fname=? AND referral_lname=?");
            if ($statement===false) {
                throw new Exception('prepare() error: The statement could not be prepared.');
            }

            $mysqli_checks = $api->bind_params($statement, "ssss", array($referral_data['client_id'], $referral_data['order_id'], $first_name, $last_name));
            if ($mysqli_checks===false) {
                throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
            }

            $mysqli_checks = $api->execute($statement);
            if($mysqli_checks===false) {
                throw new Exception('Execute error: The prepared statement could not be executed.');
            }

            $api->store_result($statement);
            if($api->num_rows($statement) > 0) { 
                $_SESSION['referral_err'] = "You cannot make a referral to the same person for your current workorder more than once!";
                array_push($errors, $_SESSION['referral_err']);
            }

            $api->free_result($statement);
            $mysqli_checks = $api->close($statement);
            if ($mysqli_checks===false) {
                throw new Exception('The prepared statement could not be closed.');
            } else {
                $statement = null;
            }
        }

        // email validation
        if (empty($email)) {
            $_SESSION['email_err'] = "Referral email is required. ";
            array_push($errors, $_SESSION['email_err']);
        }

        elseif (!$api->validate_data($email, 'email')) {
            $_SESSION['email_err'] = "Invalid email. ";
            array_push($errors, $_SESSION['email_err']);
        }

        // age validation
        if(empty($age)) {
            $_SESSION['age_err'] = "Referral age is required.";
            array_push($errors, $_SESSION['age_err']);
        }
        
        elseif (!$api->validate_data($age, 'int')) {
            $_SESSION['age_err'] = "Referral age must be an integer.";
            array_push($errors, $_SESSION['age_err']);
        }
        
        elseif($age < 0){
            $_SESSION['age_err'] = "Referral age must not be negative.";
            array_push($errors, $_SESSION['age_err']);
        }

        elseif ($age < 17) {
            $_SESSION['age_err'] = "Referral age must be at least 17 years old.";
            array_push($errors, $_SESSION['age_err']);
        }

        // contact number validation
        if(empty($contact_number)) {
            $_SESSION['contact_number_err'] = "Referral contact number is required. ";
            array_push($errors, $_SESSION['contact_number_err']);
        }

        elseif (!$api->validate_data($contact_number, 'int')) {
            $_SESSION['contact_number_err'] = "Referral contact number must be an integer.";
            array_push($errors, $_SESSION['contact_number_err']);
        }

        elseif (mb_strlen($contact_number) < 7) {
            $_SESSION['contact_number_err'] = "Referral contact number must be at least 7 numbers long. ";
            array_push($errors, $_SESSION['contact_number_err']);
        }

        elseif (mb_strlen($contact_number) > 11) {
            $_SESSION['contact_number_err'] = "Referral contact number must not exceed 11 numbers long. ";
            array_push($errors, $_SESSION['contact_number_err']);
        }

        if(empty($errors)){

            $query = $api->update();
            $query = $api->table($query, "referral");
            $query = $api->set($query, array("referral_fname", "referral_mi", "referral_lname", "referral_contact_no", "referral_email", "referral_age", "confirmation_status"), array("?", "?", "?", "?", "?", "?", "?"));
            $query = $api->where($query, array("referral_id", "client_id", "order_id"), array("?", "?", "?"));

            $statement = $api->prepare($query);
            if ($statement===false) {
                throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
            }

            $mysqli_checks = $api->bind_params($statement, "sssssissss",  array($first_name, $mi, $last_name, $contact_number, $email, $age, $confirmation_status, $referral_id, $referral_data['client_id'], $referral_data['order_id']));
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
    } catch (Exception $e) {
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ../client/orders.php");
        exit();
    }

    Header("Location: ./orders.php");
}

// remove client referral
if(isset($_POST['remove_referral'])){
    try {
        $referral_id = $api->sanitize_data($_POST['referral_id'], "string");

        // retrieving referral data
        $statement = $api->prepare("SELECT client_id, order_id FROM referral WHERE referral_id=?");
        if ($statement===false) {
            throw new Exception('prepare() error: The statement could not be prepared.');
        }

        $mysqli_checks = $api->bind_params($statement, "s", $referral_id);
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

        $referral_data = $api->fetch_assoc($res);
        if($referral_data===false) {
            throw new Exception('get_result() error: Getting result set from statement failed.');
        }

        $api->free_result($statement);
        $mysqli_checks = $api->close($statement);
        if ($mysqli_checks===false) {
        throw new Exception('The prepared statement could not be closed.');
        } else {
            $statement = null;
        }

        $query = $api->delete();
        $query = $api->from($query);
        $query = $api->table($query, "referral");
        $query = $api->where($query, array("referral_id", "client_id", "order_id"), array("?", "?", "?"));

        $statement = $api->prepare($query);
        if ($statement===false) {
            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
        }

        $mysqli_checks = $api->bind_params($statement, "sss",  array($referral_id, $referral_data['client_id'], $referral_data['order_id']));
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
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ../client/orders.php");
        exit();
    }

    Header("Location: ./orders.php");
}

/******** BOOKING MANAGEMENT ********/

// update client reservation detilas
if(isset($_POST['update_reservation'])){
    $errors = array();

    $reservation_id = $api->sanitize_data($_POST['reservation_id'], "string");
    $item_id = $api->sanitize_data($_POST['item_id'], "string");
    $service_type = $api->sanitize_data($_POST['service_type'], "string");
    $amount_addon = $api->sanitize_data($_POST['amount_addon'], "float");
    $time = $_POST['scheduled_time'];
    $date = $_POST['scheduled_date'];   
    $address = $api->sanitize_data($_POST['reservation_address'], "string");

    if(empty($address)) {
        $_SESSION['address_err'] = "Reservation address is required.";
        array_push($errors, $_SESSION['address_err']);
    }

    if(empty($service_type)) {
        $_SESSION['service_type_err'] = "Service type is required.";
        array_push($errors, $_SESSION['service_type_err']);
    }

    if(!$api->validate_data($date, 'date')) {
        $_SESSION['scheduled_date_err'] = "Invalid date. ";
        array_push($errors, $_SESSION['scheduled_date_err']);
    }

    if(!$api->validate_data($time, 'time')) {
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
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./reservations.php");
            exit();
        }
    } else {
        $_SESSION['res'] = $errors;
    }

    Header("Location: ./reservations.php");
}

// start tattoo worksession
if(isset($_POST['start_worksession'])){
    $cstrong = true;

    $reservation_id = $api->sanitize_data($_POST['reservation_id'], "string");
    $session_id = bin2hex(openssl_random_pseudo_bytes(11, $cstrong));
    $address = $api->sanitize_data($_POST['reservation_address'], "string");

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
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./reservations.php");
            exit();
        }
    }

    Header("Location: ./reservations.php");
}

// cancel client reservation
if(isset($_POST['cancel_reservation'])){
    $reservation_id = $api->sanitize_data($_POST['reservation_id'], "string");
    $item_id = $api->sanitize_data($_POST['item_id'], "string");
    $quantity = $api->sanitize_data($_POST['quantity'], "int");
    $client_id = $api->sanitize_data($_POST['client_id'], "string");

    try {
        // get order item
        $query = $api->select();
        $query = $api->params($query, array("tattoo_id", "order_id", "tattoo_width", "tattoo_height", "paid"));
        $query = $api->from($query);
        $query = $api->table($query, "order_item");
        $query = $api->where($query, "item_id", "?");
        $query = $api->limit($query, 1);

        $statement = $api->prepare($query);
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

                $mysqli_checks = $api->bind_params($statement, "siisssi", array($item['order_id'], $item['tattoo_width'], $item['tattoo_height'], $item['paid'], $item_id, "Reserved", 1));
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
        $mysqli_checks = $api->update_total($item['order_id'], $client_id);
        if ($mysqli_checks===false) {
            throw new Exception('Error: Updating amount due total of current order failed.');
        }
    } catch (Exception $e) {
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./reservations.php");
        exit();
    }

    Header("Location: ./reservations.php");
}

// finish tattoo worksession
if(isset($_POST['finish_worksession'])){
    $item_id = $api->sanitize_data($_POST['item_id'], "string");
    $reservation_id = $api->sanitize_data($_POST['reservation_id'], "string");
    $session_id = $api->sanitize_data($_POST['session_id'], "string");

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
        $_SESSION['res'] = $e->getMessage();
        Header("Location: ./reservations.php");
        exit();
    }

    Header("Location: ./reservations.php");
}

/******** PAYMENT LOGGING ********/

// logging client payment
if(isset($_POST['log_payment'])){
    if(isset($_POST['item']) && !empty($_POST['item'])){
        try {
            $errors = array();
            $cstrong = true;

            $order_id = $api->sanitize_data($_POST['order_id'], "string");
            $client_id = $api->sanitize_data($_POST['client_id'], "string");

            $first_name = $api->sanitize_data(ucfirst($_POST['first_name']), "string");
            $last_name = $api->sanitize_data(ucfirst($_POST['last_name']), "string");
            $street_address = $api->sanitize_data($_POST['street_address'], "string");
            $city = $api->sanitize_data($_POST['city'], "string");
            $province = $api->sanitize_data($_POST['province'], "string");
            $zip = $api->sanitize_data($_POST['zip'], "int");
            $amount_paid = $api->sanitize_data($_POST['amount_paid'], "float");
            $payment_method = $api->sanitize_data($_POST['payment_method'], "string");

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

            elseif (!$api->validate_data($zip, 'int')) {
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
                $change = $api->sanitize_data($amount_paid, "float");

                // checking for discount
                $statement = $api->prepare("SELECT incentive FROM workorder WHERE order_id=? AND client_id=? AND status=? ORDER BY order_date ASC LIMIT 1");
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $api->bind_params($statement, "sss", array($order_id, $client_id, "Ongoing"));
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $api->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $api->store_result($statement);
                if($api->num_rows($statement) > 0){
                    $discount = "";
                    $res = $api->bind_result($statement, array($discount));
                    $api->get_bound_result($discount, $res[0]);
                }

                $api->free_result($statement);
                $mysqli_checks = $api->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $res = null;
                    $statement = null;
                }

                if(isset($discount) && !empty($discount) && strcasecmp($discount, "15% discount") == 0){
                    $total = (double) 0.00;
                    $change = $api->sanitize_data((($change / 85) * 100), "float");
                }

                foreach($_POST['item'] as $item){
                    $index = array_search($item, $_POST['index']);
                    
                    $checkout_quantity = $api->sanitize_data($_POST['checkout_quantity'][$index], "int");
                    $quantity = $api->sanitize_data($_POST['quantity'][$index], "int");

                    if(empty($checkout_quantity)) {
                        $_SESSION['quantity_err'] = "Checkout quantity is required. ";
                        array_push($errors, $_SESSION['quantity_err']);
                    }
            
                    elseif(!$api->validate_data($checkout_quantity, 'int')) {
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

                            $tattoo_id = $api->sanitize_data($row['tattoo_id'], "string");
                            $width = $api->sanitize_data($row['tattoo_width'], "int");
                            $height = $api->sanitize_data($row['tattoo_height'], "int");
                            $paid = $api->sanitize_data($row['paid'], "string");
                            $item_status = $api->sanitize_data($row['item_status'], "string");
                            $addon = (!empty($row['amount_addon']) && $row['amount_addon'] != 0) ? $api->sanitize_data($row['amount_addon'], "float") : 0.00;
                            $item_amount_due_total = $api->sanitize_data($row['tattoo_price'], "float") * $checkout_quantity;

                            if(in_array($item_status, array("Reserved", "Applied")) && strcasecmp($paid, "Partially Paid") == 0) {
                                $item_amount_due_total += $api->sanitize_data($row['tattoo_price'], "float") + $addon;
                            }

                            if ($change >= $item_amount_due_total){
                                $change -= $item_amount_due_total;
                                if(isset($discount) && !empty($discount) && strcasecmp($discount, "15% discount") == 0){
                                    $total += $item_amount_due_total;
                                }
    
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

                if(isset($discount) && !empty($discount) && strcasecmp($discount, "15% discount") == 0){
                    $change = $amount_paid - ($total - ($total * .15));
                }

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
            $_SESSION['res'] = $e->getMessage();
            Header("Location: ./orders.php");
            exit();
        }
    } else {
        $_SESSION['res'] = "No items selected.";
    }

    Header("Location: ./orders.php");
}

/******** ILLEGAL ACCESS CATCHING ********/

// navigation guard
if(empty($_POST)){
    Header("Location: ./index.php");
    die();
}
?>