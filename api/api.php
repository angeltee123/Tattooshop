<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*  TO DO
    - ADD ERROR CATCHING FOR PREPARED STATEMENT QUERYING
*/

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
        $join = $join . $this->clean($right) . " ON " . $this->clean($left) . "." . $this->clean($left_kv) . "=" . $this->clean($right) . "." . $this->clean($right_kv) . ")";
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
                $string = $string . "ORDER BY " . $this->clean($params) . " " . $this->clean($order);
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

    public function change_user($user, $password){
        $this->conn->change_user($user, $password, $this->db);
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