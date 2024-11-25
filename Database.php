<?php
require_once 'Logger.php'; 
class Database {
    private $host = 'localhost';
    private $db_name = 'ringo_internals';
    private $username = 'root';
    private $password = '';
    public $conn;
    private $logger;

    public function __construct() {
        $this->logger = new Logger();  // Initialize the Logger class
    }
    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            $this->logger->log('Database connection error: ' . $exception->getMessage());
            echo json_encode(['error' => 'Database connection error: ' . $exception->getMessage()]);
        }
        return $this->conn;
    }
}
