<?php
require_once 'Logger.php'; 
class HeaderAuth {
    private $username;
    private $password;

    private $logger;

    // Constructor to initialize username and password from headers
    public function __construct($headers) {
        $this->username = isset($headers['Username']) ? $headers['Username'] : null;
        $this->password = isset($headers['Password']) ? $headers['Password'] : null;
        $this->logger = new Logger();
    }

    // Validate if username and password are present
    public function validate() {
        // Check if username or password are missing
        if (empty($this->username) || empty($this->password)) {
            $errorMessage = 'Username and Password are required';
            $this->logger->log($errorMessage);
            return ['error' => $errorMessage];  // Return the error message 
        }

        return null;  // If valid, return null (no error)
    }
}
