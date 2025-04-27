<?php
class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $servername = "localhost";
        $port = 3306; 
        $username = "root"; 
        $password = ""; // Your phpMyAdmin password
        $dbname = "vids"; 

        $this->connection = new mysqli($servername, $username, $password, $dbname, $port);

        if ($this->connection->connect_error) {
            die("Connection failed: " . $this->connection->connect_error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function closeConnection() {
        if ($this->connection) {
            $this->connection->close();
        }
    }

    public function __clone() { }
    public function __wakeup() { }

    public function __destruct() {
        $this->closeConnection();
    }
}
