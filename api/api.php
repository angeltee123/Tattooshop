<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
date_default_timezone_set("Asia/Manila");

class API {
    private $server = "localhost";
    private $user = "root";
    private $password = "";
    private $db = "njctattoodb";
    private $port = 3306;
    private $conn = null;

    /***** CREATE CONNECTION *****/

    public function __construct(){
        $this->conn = new mysqli($this->server, $this->user, $this->password, $this->db, $this->port);
        $this->conn->connect_error ? die("Failed to establish connection. Error code " . $this->conn->connect_errno . " - " . $this->conn->connect_error ) : $this->conn->set_charset('utf8mb4');
    }

    /***** HELPER FUNCTIONS *****/

    public function clean($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    public function is_valid_date($date){
        $checks = false;

        $checks = (bool) strtotime($date);
        if($checks){
            $ymd = explode('-', $date);
            $checks = checkdate($ymd[1], $ymd[2], $ymd[0]);
            if($checks){
                $d = DateTime::createFromFormat("Y-m-d", $date);

                $checks = ($d && $d->format("Y-m-d") === $date) ? true : false;
                if($checks){
                    $date = new DateTime($date);
                    
                    $today = new DateTime();

                    $checks = ($date >= $today) ? true : false;
                }
            }
        }

        return $checks;
    }

    public function is_valid_time($time){
        $checks = false;

        $time = strtotime($time);
        $checks = (bool) $time;
        if($checks){
            $time = date("G:i:s", $time);

            $hms = explode(':', $time);
            $checks = ($hms[0] >= 0 && $hms[0] <= 24) ? true : false;
            if($checks){
                $checks = ($hms[1] >= 0 && $hms[1] <= 60) ? true : false;
                if($checks){
                    $checks = ($hms[2] >= 0 && $hms[2] <= 60) ? true : false;
                }
            }
        }

        return $checks;
    }

    /*  check if scheduled time is within service hours,
        adjust if business changes their service hours
    public function within_service_hours($time){
        $checks = false;
        if($this->is_valid_time($time)){
            $hour = date("H", strtotime($time));

            $checks = ($hour >= 8 && $hour <= 18);
        }

        return $checks;
    } */

    /***** MYSQL HELPERS *****/

    public function table($string, $params){
        if(!empty($string) && !empty($params)){
            if(!is_array($params)){
                $string = $string . $params . " ";
            } else {
                if(!empty($params)){
                    for ($k = 0; $k < count($params); $k++) {
                        $string = $string . $this->clean($params[$k]) . ", ";
                    }
        
                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    public function join($type, $left, $right, $left_kv, $right_kv){
        $join = (is_string($type)) ? "(" . $this->clean($left) . " " . strtoupper($type) . " JOIN " : "(" . $this->clean($left) . " JOIN ";
        $join = $join . $this->clean($right) . " ON " . $this->clean($left_kv) . "=" . $this->clean($right_kv) . ")";
        return $join;
    }

    public function where($string, $cols, $params){
        if(!empty($params) && !empty($params)){
            $string = $string . "WHERE ";
            if(!is_array($cols) && !is_array($params)){
                $cols = is_string($cols) ? $this->clean($cols) : $cols;
                $params = is_string($params) ? $this->clean($params) : $params;
                $string = $string . $cols . "=" . $params . " ";
            } else {
                $col_count = count($cols);
                $param_count = count($params);

                if($col_count == $param_count){
                    for ($k = 0; $k < $col_count; $k++) {
                        $cols[$k] = is_string($cols[$k]) ? $this->clean($cols[$k]) : $cols[$k];
                        $params[$k] = is_string($params[$k]) ? $this->clean($params[$k]) : $params[$k];
                        $string = $string . $cols[$k] . "=" . $params[$k] . " AND ";
                    }
        
                    $string = substr($string, 0, -4);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    public function limit($string, $limit){
        if(is_int($limit)){
            return $string . "LIMIT " . $limit;
        }
    }

    public function order($string, $params, $order){
        if(!empty($params) && !empty($order)){
            if(!is_array($params)){
                $string = $string . "ORDER BY " . $this->clean($params) . " " . $this->clean($order) . " ";
            } else {
                $param_count = count($params);
                $order_count = count($order);

                if($param_count == $order_count){
                    $string = $string . "ORDER BY ";
                    
                    for ($k = 0; $k < count($params); $k++) {
                        $string = $string . $this->clean($params[$k]) . " " . $this->clean($order[$k]) . ", ";
                    }

                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    public function change_user($user){
        $user = $this->clean($user);
        $password = "";

        if (strcasecmp($user, "user") == 0){
            $password = "User@CIS2104.njctattoodb";
        }
        
        elseif (strcasecmp($user, "admin") == 0){
            $password = "Admin@CIS2104.njctattoodb";
        }

        $this->conn->change_user($user, $password, $this->db);
    }

    public function get_workorder($client_id){
        if(!empty($client_id)){
            $_SESSION['order_id'] = "";
            
            try {
                // get existing order
                $get_order = $this->select();
                $get_order = $this->params($get_order, array("order_id", "amount_due_total"));
                $get_order = $this->from($get_order);
                $get_order = $this->table($get_order, "workorder");
                $get_order = $this->where($get_order, array("client_id", "status"), array("?", "?"));
                $get_order = $this->order($get_order, "order_date", "DESC");
                $get_order = $this->limit($get_order, 1);
            
                $statement = $this->prepare($get_order);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
            
                $mysqli_checks = $this->bind_params($statement, "ss", array($client_id, "Ongoing"));
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
            
                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }
            
                $res = $this->get_result($statement);
                if($res===false){
                    throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                if($this->num_rows($res) > 0){
                    $workorder = $this->fetch_assoc($res);
                    $_SESSION['order_id'] = $workorder['order_id'];
                    $amount_due_total = $workorder['amount_due_total'];

                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                        $res = null;
                    }

                    // updating status of existing order - getting all items
                    $get_all_items = $this->select();
                    $get_all_items = $this->params($get_all_items, "*");
                    $get_all_items = $this->from($get_all_items);
                    $get_all_items = $this->table($get_all_items, "order_item");
                    $get_all_items = $this->where($get_all_items, "order_id", "?");
                
                    $statement = $this->prepare($get_all_items);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $this->bind_params($statement, "s", $_SESSION['order_id']);
                    if ($mysqli_checks===false) {
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
                
                    $mysqli_checks = $this->execute($statement);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $res = $this->get_result($statement);
                    if($res===false){
                        throw new Exception('get_result() error: Getting result set from statement failed.');
                    }

                    $unfiltered_row_count = $this->num_rows($res);
            
                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                        $res = null;
                    }

                    // updating status of existing order - getting completed items
                    $get_completed_items = $this->select();
                    $get_completed_items = $this->params($get_completed_items, "*");
                    $get_completed_items = $this->from($get_completed_items);
                    $get_completed_items = $this->table($get_completed_items, "order_item");
                    $get_completed_items = $this->where($get_completed_items, array("order_id", "paid", "item_status"), array("?", "?", "?"));
                
                    $statement = $this->prepare($get_completed_items);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $this->bind_params($statement, "sss", array($_SESSION['order_id'], "Fully Paid", "Applied"));
                    if ($mysqli_checks===false) {
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
                
                    $mysqli_checks = $this->execute($statement);
                    if ($statement===false) {
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $res = $this->get_result($statement);
                    if($res===false){
                        throw new Exception('get_result() error: Getting result set from statement failed.');
                    }

                    $filtered_row_count = $this->num_rows($res);
            
                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }

                    // updating status of existing order - finishing order
                    if($unfiltered_row_count == $filtered_row_count && $amount_due_total == 0){
                        $statement = $this->prepare("UPDATE workorder SET status=? WHERE order_id=? AND client_id=?");
                        if ($statement===false) {
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $this->bind_params($statement, "sss", array("Finished", $_SESSION['order_id'], $client_id));
                        if ($mysqli_checks===false) {
                            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                        }

                        $mysqli_checks = $this->execute($statement);
                        if($mysqli_checks===false) {
                            throw new Exception('Execute error: The prepared statement could not be executed.');
                        }

                        $mysqli_checks = $this->close($statement);
                        if ($mysqli_checks===false) {
                            throw new Exception('The prepared statement could not be closed.');
                        } else {
                            $statement = null;
                        }

                        $_SESSION['order_id'] = "";
                    }
                } else {
                    // no exsiting order found
                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if ($mysqli_checks===false) {
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }

                    $_SESSION['order_id'] = "";
                }

                return true;
            } catch (Exception $e) {
                exit();
                return $e;
            }
        }
    }

    public function update_total($order_id, $client_id){
        if(!empty($order_id) && !empty($client_id)){
            try {
                $total = (double) 0.00;

                // update order amount_due_total
                $left = $this->join("INNER", "order_item", "workorder", "order_item.order_id", "workorder.order_id");
                $right = $this->join("LEFT", $left, "reservation", "order_item.item_id", "reservation.item_id");
                $join = $this->join("INNER", $right, "tattoo", "order_item.tattoo_id", "tattoo.tattoo_id");

                $get_total = $this->select();
                $get_total = $this->params($get_total, array("tattoo_price", "tattoo_quantity", "paid", "item_status", "amount_addon"));
                $get_total = $this->from($get_total);
                $get_total = $this->table($get_total, $join);
                $get_total = $this->where($get_total, array("client_id", "workorder.order_id"), array("?", "?"));
                $not = "AND status!=? AND paid!=? ";
                $get_total = $get_total . $not;

                $statement = $this->prepare($get_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $this->bind_params($statement, "ssss", array($client_id, $order_id, "Finished", "Fully Paid"));
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $res = $this->get_result($statement);
                if($res===false){
                    throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                if($this->num_rows($res) > 0){
                    while($row = $this->fetch_assoc($res)){
                        if(strcasecmp($row['item_status'], "Standing") == 0 && strcasecmp($row['paid'], "Unpaid") == 0) {
                            $total += $row['tattoo_price'] * $row['tattoo_quantity'];
                        }

                        elseif(in_array($row['item_status'], array("Reserved", "Applied"))){
                            if(strcasecmp($row['paid'], "Unpaid") == 0){
                                $total += ($row['tattoo_price'] * $row['tattoo_quantity']) + $row['amount_addon'];
                            }

                            elseif(strcasecmp($row['paid'], "Partially Paid") == 0){
                                $total += $row['amount_addon'];
                            }
                        }
                    }
                }

                $this->free_result($statement);
                $mysqli_checks = $this->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $statement = null;
                }

                $total = doubleval($total);

                $update_total = $this->update();
                $update_total = $this->table($update_total, "workorder");
                $update_total = $this->set($update_total, "amount_due_total", "?");
                $update_total = $this->where($update_total, "order_id", "?");

                $statement = $this->prepare($update_total);
                if ($statement===false) {
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $this->bind_params($statement, "ds", array($total, $order_id));
                if ($mysqli_checks===false) {
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false) {
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $mysqli_checks = $this->close($statement);
                if ($mysqli_checks===false) {
                    throw new Exception('The prepared statement could not be closed.');
                }

                return true;
            } catch (Exception $e) {
                exit();
                return false;
            }
        }
    }

    /***** SELECT *****/

    public function select(){
        return "SELECT ";
    }

    public function params($string, $params){
        if(!is_array($params)){
            return $string . $params . " ";
        } else {
            if(!empty($params)){
                for ($k = 0; $k < count($params); $k++) {
                    $string = $string . $this->clean($params[$k]) . ", ";
                }
    
                $string = substr($string, 0, -2);
                $string = $string . " ";
                return $string;
            }
        }
    }

    public function from($string){
        return $string . "FROM ";
    }

    /***** INSERT *****/

    public function insert(){
        return "INSERT INTO ";
    }

    public function columns($string, $params = array()){
        if(!empty($params)){
            $string = $string . "(";
            for ($k = 0; $k < count($params); $k++) {
                $string = $string . $this->clean($params[$k]) . ", ";
            }

            $string = substr($string, 0, -2);
            $string = trim($string) . ") ";
            return $string;
        }  
    }

    public function values($string){
        return $string . "VALUES ";
    }

    /***** UPDATE *****/

    public function update(){
        return "UPDATE ";
    }

    public function set($string, $cols, $params){
        if(!empty($cols) && !empty($params)){
            if(!is_array($cols) && !is_array($params)){
                $string = $string . "SET ";
                $string = $string . $this->clean($cols) . "=" . $this->clean($params) . " ";
            } else {
                $col_count = count($cols);
                $param_count = count($params);

                if($col_count == $param_count){
                    $string = $string . "SET ";

                    for ($k = 0; $k < $col_count; $k++) {
                        $string = $string . $this->clean($cols[$k]) . "=" . $this->clean($params[$k]) . ", ";
                    }
        
                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    /***** DELETING *****/

    public function delete(){
        return "DELETE ";
    }

    /***** QUERYING *****/

    public function prepare($query){
        return $this->conn->prepare($query);
    }
    
    public function execute(&$statement){
        return $statement->execute();
    }

    public function store_result(&$statement){
        return $statement->store_result();
    }

    public function num_rows($res){
        return $res->num_rows;
    }

    public function bind_params(&$statement, $types, $params){
        if(!is_array($params)){
            try {
                $param_ref[] = &$types;
                if (is_string($params)){
                    $params = $this->clean($params);
                }
                $param_ref[] = &$params;
                return call_user_func_array(array($statement, 'bind_param'), $param_ref);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            try {
                $param_ref[] = &$types;
                for ($i = 0; $i < count($params); $i++) {
                    if (is_string($params[$i])){
                        $params[$i] = $this->clean($params[$i]);
                    }
                    $param_ref[] = &$params[$i];
                }
                return call_user_func_array(array($statement, 'bind_param'), $param_ref);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function bind_result(&$statement, $params = array()){
        if(!empty($params)){
            try {
                for ($i = 0; $i < count($params); $i++) {
                    $param_ref[] = &$params[$i];
                }
                call_user_func_array(array($statement, 'bind_result'), $param_ref);
                $statement->fetch();
                return $param_ref;
            }
            catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    public function get_bound_result(&$param, $bound_result){
        $param = $bound_result;
    }

    public function get_result(&$statement){
        return $statement->get_result();
    }

    public function fetch_assoc(&$result){
        return $result->fetch_assoc();
    }

    public function free_result(&$statement){
        $statement->free_result();
    }

    public function close(&$statement){
        return $statement->close();
    }
}
?>