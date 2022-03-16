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

    // api class constructor
    public function __construct(){
        $this->conn = new mysqli($this->server, $this->user, $this->password, $this->db, $this->port);
        if($this->conn->connect_error){
            die("Failed to establish connection. Error code " . $this->conn->connect_errno . " - " . $this->conn->connect_error );
        } else {
            $this->conn->set_charset('utf8mb4');
            $this->change_user("user");
        }
    }

    /***** HELPER FUNCTIONS *****/

    // input sanitization
    public function sanitize_data($data, $type){
        switch($type){
            // int sanitization
            case 'int':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_INT);
                $data = intval($data);
            break;

            // float sanitization
            case 'float':
                $data = filter_var($data, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $data = doubleval($data);
            break;

            // email sanitization
            case 'email':
                $data = trim($data);
                $data = filter_var($data, FILTER_SANITIZE_EMAIL);
            break;

            // default case
            case 'string':
            default:
                $data = trim($data);
                $data = stripslashes($data);
                $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
                $data = htmlspecialchars($data);
            break;
        }

        return $data;
    }

    // input validation
    public function validate_data($data, $type){
        $checks = false;

        switch($type){
            // int validation
            case 'int':
                $checks = filter_var($data, FILTER_VALIDATE_INT);
                $checks = is_int($data);
            break;

            // float validation
            case 'float':
                $checks = filter_var($data, FILTER_VALIDATE_FLOAT);
                $checks = is_double($data);
            break;

            // email validation
            case 'email':
                $checks = filter_var($data, FILTER_VALIDATE_EMAIL);
            break;

            // date validation
            case 'date':
                $checks = (bool) strtotime($data);
                if($checks){
                    $ymd = explode('-', $data);
                    $checks = checkdate($ymd[1], $ymd[2], $ymd[0]);
                    if($checks){
                        $d = DateTime::createFromFormat("Y-m-d", $data);

                        $checks = ($d && $d->format("Y-m-d") === $data) ? true : false;
                        if($checks){
                            $date = new DateTime($data);
                            
                            $today = new DateTime();

                            $checks = ($date >= $today) ? true : false;
                        }
                    }
                }
            break;

            // time validation
            case 'time':
                $time = strtotime($data);
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
            break;

            // default case
            default:
                $checks = filter_var($data, FILTER_SANITIZE_STRING);
            break;
        }

        return $checks;
    }

    /*
     * checks if scheduled time is within service hours
        public function within_service_hours($time){
            $checks = false;
            if($this->is_valid_time($time)){
                $hour = date("H", strtotime($time));

                $checks = ($hour >= 8 && $hour <= 18);
            }

            return $checks;
        }
    */

    /***** MYSQL HELPERS *****/

    // mysql table
    public function table($string, $params){
        if(!empty($string) && !empty($params)){
            if(!is_array($params)){
                $string = $string . $params . " ";
            } else {
                if(!empty($params)){
                    for($k = 0; $k < count($params); $k++){
                        $string = $string . $this->sanitize_data($params[$k], "string") . ", ";
                    }
        
                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    // mysql join clause
    public function join($type, $left, $right, $left_kv, $right_kv){
        $join = (is_string($type)) ? "(" . $this->sanitize_data($left, "string") . " " . strtoupper($type) . " JOIN " : "(" . $this->sanitize_data($left, "string") . " JOIN ";
        $join = $join . $this->sanitize_data($right, "string") . " ON " . $this->sanitize_data($left_kv, "string") . "=" . $this->sanitize_data($right_kv, "string") . ")";
        return $join;
    }

    // mysql where clause
    public function where($string, $cols, $params){
        if(!empty($params) && !empty($params)){
            $string = $string . "WHERE ";
            if(!is_array($cols) && !is_array($params)){
                $cols = is_string($cols) ? $this->sanitize_data($cols, "string") : $cols;
                $params = is_string($params) ? $this->sanitize_data($params, "string") : $params;
                $string = $string . $cols . "=" . $params . " ";
            } else {
                $col_count = count($cols);
                $param_count = count($params);

                if($col_count == $param_count){
                    for($k = 0; $k < $col_count; $k++){
                        $cols[$k] = is_string($cols[$k]) ? $this->sanitize_data($cols[$k], "string") : $cols[$k];
                        $params[$k] = is_string($params[$k]) ? $this->sanitize_data($params[$k], "string") : $params[$k];
                        $string = $string . $cols[$k] . "=" . $params[$k] . " AND ";
                    }
        
                    $string = substr($string, 0, -4);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    // mysql limit clause
    public function limit($string, $limit){
        if(is_int($limit)){
            return $string . "LIMIT " . $limit;
        }
    }

    // mysql order by clause
    public function order($string, $params, $order){
        if(!empty($params) && !empty($order)){
            if(!is_array($params)){
                $string = $string . "ORDER BY " . $this->sanitize_data($params, "string") . " " . $this->sanitize_data($order, "string") . " ";
            } else {
                $param_count = count($params);
                $order_count = count($order);

                if($param_count == $order_count){
                    $string = $string . "ORDER BY ";
                    
                    for($k = 0; $k < count($params); $k++){
                        $string = $string . $this->sanitize_data($params[$k], "string") . " " . $this->sanitize_data($order[$k], "string") . ", ";
                    }

                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    // php mysqli change_user()
    public function change_user($user){
        $user = $this->sanitize_data($user, "string");
        $password = "";

        if(strcasecmp($user, "user") == 0){
            $password = "User@CIS2104.njctattoodb";
        }
        
        elseif(strcasecmp($user, "admin") == 0){
            $password = "Admin@CIS2104.njctattoodb";
        }

        $this->conn->change_user($user, $password, $this->db);
    }

    // get workorder details
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
                if($statement===false){
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }
            
                $mysqli_checks = $this->bind_params($statement, "ss", array($client_id, "Ongoing"));
                if($mysqli_checks===false){
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }
            
                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false){
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
                    if($mysqli_checks===false){
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
                    if($statement===false){
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $this->bind_params($statement, "s", $_SESSION['order_id']);
                    if($mysqli_checks===false){
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
                
                    $mysqli_checks = $this->execute($statement);
                    if($statement===false){
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $res = $this->get_result($statement);
                    if($res===false){
                        throw new Exception('get_result() error: Getting result set from statement failed.');
                    }

                    $unfiltered_row_count = $this->num_rows($res);
            
                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if($mysqli_checks===false){
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
                    if($statement===false){
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $mysqli_checks = $this->bind_params($statement, "sss", array($_SESSION['order_id'], "Fully Paid", "Applied"));
                    if($mysqli_checks===false){
                        throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                    }
                
                    $mysqli_checks = $this->execute($statement);
                    if($statement===false){
                        throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                    }

                    $res = $this->get_result($statement);
                    if($res===false){
                        throw new Exception('get_result() error: Getting result set from statement failed.');
                    }

                    $filtered_row_count = $this->num_rows($res);
            
                    $this->free_result($statement);
                    $mysqli_checks = $this->close($statement);
                    if($mysqli_checks===false){
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }

                    // updating status of existing order - finishing order
                    if($unfiltered_row_count == $filtered_row_count && $amount_due_total == 0){
                        $statement = $this->prepare("UPDATE workorder SET status=? WHERE order_id=? AND client_id=?");
                        if($statement===false){
                            throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                        }

                        $mysqli_checks = $this->bind_params($statement, "sss", array("Finished", $_SESSION['order_id'], $client_id));
                        if($mysqli_checks===false){
                            throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                        }

                        $mysqli_checks = $this->execute($statement);
                        if($mysqli_checks===false){
                            throw new Exception('Execute error: The prepared statement could not be executed.');
                        }

                        $mysqli_checks = $this->close($statement);
                        if($mysqli_checks===false){
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
                    if($mysqli_checks===false){
                        throw new Exception('The prepared statement could not be closed.');
                    } else {
                        $statement = null;
                    }

                    $_SESSION['order_id'] = "";
                }

                return true;
            } catch (Exception $e){
                exit();
                return $e;
            }
        }
    }

    // update workorder amount due total
    public function update_total($order_id, $client_id){
        if(!empty($order_id) && !empty($client_id)){
            try {
                $total = (double) 0.00;

                // retrieving order items
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
                if($statement===false){
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $this->bind_params($statement, "ssss", array($client_id, $order_id, "Finished", "Fully Paid"));
                if($mysqli_checks===false){
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false){
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $res = $this->get_result($statement);
                if($res===false){
                    throw new Exception('get_result() error: Getting result set from statement failed.');
                }

                // calculating total
                if($this->num_rows($res) > 0){
                    while($row = $this->fetch_assoc($res)){
                        if(strcasecmp($row['item_status'], "Standing") == 0 && strcasecmp($row['paid'], "Unpaid") == 0){
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
                if($mysqli_checks===false){
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $res = null;
                    $statement = null;
                }

                // checking for discount
                $statement = $this->prepare("SELECT incentive FROM workorder WHERE order_id=? AND client_id=? AND status=? ORDER BY order_date ASC LIMIT 1");
                if($statement===false){
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $this->bind_params($statement, "sss", array($order_id, $client_id, "Ongoing"));
                if($mysqli_checks===false){
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false){
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $this->store_result($statement);
                if($this->num_rows($statement) > 0){
                    $discount = "";
                    $res = $this->bind_result($statement, array($discount));
                    $this->get_bound_result($discount, $res[0]);
                }

                $this->free_result($statement);
                $mysqli_checks = $this->close($statement);
                if($mysqli_checks===false){
                    throw new Exception('The prepared statement could not be closed.');
                } else {
                    $statement = null;
                }

                if(isset($discount) && !empty($discount) && strcasecmp($discount, "15% discount") == 0){
                    $total -= ($total * .15);
                }

                // update order amount due total
                $total = doubleval($total);

                $update_total = $this->update();
                $update_total = $this->table($update_total, "workorder");
                $update_total = $this->set($update_total, "amount_due_total", "?");
                $update_total = $this->where($update_total, "order_id", "?");

                $statement = $this->prepare($update_total);
                if($statement===false){
                    throw new Exception('prepare() error: ' . $conn->errno . ' - ' . $conn->error);
                }

                $mysqli_checks = $this->bind_params($statement, "ds", array($total, $order_id));
                if($mysqli_checks===false){
                    throw new Exception('bind_param() error: A variable could not be bound to the prepared statement.');
                }

                $mysqli_checks = $this->execute($statement);
                if($mysqli_checks===false){
                    throw new Exception('Execute error: The prepared statement could not be executed.');
                }

                $mysqli_checks = $this->close($statement);
                if($mysqli_checks===false){
                    throw new Exception('The prepared statement could not be closed.');
                }

                return true;
            } catch (Exception $e){
                exit();
                return false;
            }
        }
    }

    /***** SELECT *****/

    // mysql select
    public function select(){
        return "SELECT ";
    }

    // mysql select statement parameters
    public function params($string, $params){
        if(!is_array($params)){
            return $string . $params . " ";
        } else {
            if(!empty($params)){
                for($k = 0; $k < count($params); $k++){
                    $string = $string . $this->sanitize_data($params[$k], "string") . ", ";
                }
    
                $string = substr($string, 0, -2);
                $string = $string . " ";
                return $string;
            }
        }
    }

    // mysql from
    public function from($string){
        return $string . "FROM ";
    }

    /***** INSERT *****/

    // mysql insert
    public function insert(){
        return "INSERT INTO ";
    }

    // mysql insert statement parameters
    public function columns($string, $params = array()){
        if(!empty($params)){
            $string = $string . "(";
            for($k = 0; $k < count($params); $k++){
                $string = $string . $this->sanitize_data($params[$k], "string") . ", ";
            }

            $string = substr($string, 0, -2);
            $string = trim($string) . ") ";
            return $string;
        }  
    }

    // mysql values
    public function values($string){
        return $string . "VALUES ";
    }

    /***** UPDATE *****/

    // mysql update
    public function update(){
        return "UPDATE ";
    }

    // mysql update set parameters
    public function set($string, $cols, $params){
        if(!empty($cols) && !empty($params)){
            if(!is_array($cols) && !is_array($params)){
                $string = $string . "SET ";
                $string = $string . $this->sanitize_data($cols, "string") . "=" . $this->sanitize_data($params, "string") . " ";
            } else {
                $col_count = count($cols);
                $param_count = count($params);

                if($col_count == $param_count){
                    $string = $string . "SET ";

                    for($k = 0; $k < $col_count; $k++){
                        $string = $string . $this->sanitize_data($cols[$k], "string") . "=" . $this->sanitize_data($params[$k], "string") . ", ";
                    }
        
                    $string = substr($string, 0, -2);
                    $string = $string . " ";
                }
            }

            return $string;
        }
    }

    /***** DELETING *****/

    // mysql delete
    public function delete(){
        return "DELETE ";
    }

    /***** QUERYING *****/

    // php mysqli prepare()
    public function prepare($query){
        return $this->conn->prepare($query);
    }
    
    // php mysqli execute()
    public function execute(&$statement){
        return $statement->execute();
    }

    // php mysqli store_result()
    public function store_result(&$statement){
        return $statement->store_result();
    }

    // php mysqli num_rows()
    public function num_rows($res){
        return $res->num_rows;
    }

    // php mysqli bind_params()
    public function bind_params(&$statement, $types, $params){
        if(!is_array($params)){
            try {
                $param_ref[] = &$types;
                if(is_string($params)){
                    $params = $this->sanitize_data($params, "string");
                }
                $param_ref[] = &$params;
                return call_user_func_array(array($statement, 'bind_param'), $param_ref);
            } catch (Exception $e){
                echo $e->getMessage();
            }
        } else {
            try {
                $param_ref[] = &$types;
                for($i = 0; $i < count($params); $i++){
                    if(is_string($params[$i])){
                        $params[$i] = $this->sanitize_data($params[$i], "string");
                    }
                    $param_ref[] = &$params[$i];
                }
                return call_user_func_array(array($statement, 'bind_param'), $param_ref);
            } catch (Exception $e){
                echo $e->getMessage();
            }
        }
    }

    // php mysqli bind_result()
    public function bind_result(&$statement, $params = array()){
        if(!empty($params)){
            try {
                for($i = 0; $i < count($params); $i++){
                    $param_ref[] = &$params[$i];
                }
                call_user_func_array(array($statement, 'bind_result'), $param_ref);
                $statement->fetch();
                return $param_ref;
            }
            catch (Exception $e){
                echo $e->getMessage();
            }
        }
    }

    // getting column value from returned result set
    public function get_bound_result(&$param, $bound_result){
        $param = $bound_result;
    }

    // php mysqli get_result()
    public function get_result(&$statement){
        return $statement->get_result();
    }

    // php mysqli fetch_assoc()
    public function fetch_assoc(&$result){
        return $result->fetch_assoc();
    }

    // php mysqli free_result()
    public function free_result(&$statement){
        $statement->free_result();
    }

    // php mysqli close()
    public function close(&$statement){
        return $statement->close();
    }
}
?>