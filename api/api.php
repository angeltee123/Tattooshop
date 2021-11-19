<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

class API {
    private $server = "localhost";
    private $user = "root";
    private $password = "";
    private $db = "bank";
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

    public function table($string, $params = array()){
        if(!empty($params)){
            for ($k = 0; $k < count($params); $k++) {
                $string = $string . $this->clean($params[$k]) . ", ";
            }

            $string = substr($string, 0, -2);
            $string = $string . " ";
            return $string;
        }
    }

    public function limit($string, $limit){
        return $string . "LIMIT " . $limit;
    }

    public function where($string, $cols = array(), $params = array()){
        if(!empty($params) && !empty($params)){
            $col_count = count($cols);
            $param_count = count($params);

            if($col_count == $param_count){
                $string = $string . "WHERE ";
                for ($k = 0; $k < $col_count; $k++) {
                    $string = $string . $this->clean($cols[$k]) . "=" . $this->clean($params[$k]) . ", ";
                }
    
                $string = substr($string, 0, -2);
                $string = $string . " ";
                return $string;
            }
        }
    }

    /***** SELECT *****/

    public function params($string, $params = array()){
        if(!empty($params)){
            for ($k = 0; $k < count($params); $k++) {
                $string = $string . $this->clean($params[$k]) . ", ";
            }

            $string = substr($string, 0, -2);
            $string = $string . " ";
            return $string;
        }
    }

    public function select(){
        return "SELECT ";
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

    /***** INSERT *****/

    public function update(){
        return "UPDATE ";
    }

    public function set($string, $cols = array(), $params = array()){
        if(!empty($params) && !empty($params)){
            $col_count = count($cols);
            $param_count = count($params);

            if($col_count == $param_count){
                $string = $string . "SET ";
                for ($k = 0; $k < $col_count; $k++) {
                    $string = $string . $this->clean($cols[$k]) . "=" . $this->clean($params[$k]) . ", ";
                }
    
                $string = substr($string, 0, -2);
                $string = $string . " ";
                return $string;
            }
        }
    }

    public function query($query){
        return mysqli_query($this->conn, $query);
    }
}
?>