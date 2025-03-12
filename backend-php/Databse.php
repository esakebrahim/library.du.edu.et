<?php
class Database {
    private $host = 'localhost'; // Your database host
    private $db_name = 'esak'; // Your database name
    private $username = 'root'; // Your database username
    private $password = ''; // Your database password
    public $conn;

    public function getConnection() {
        $this->conn = new mysqli($this->host, $this->username, $this->password, $this->db_name);
        
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }

        return $this->conn;
    }
}
?>