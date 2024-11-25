<?php

class HeaderAuth {
    private $username;
    private $password;

    // Constructor to initialize username and password from headers
    public function __construct($headers) {
        $this->username = isset($headers['Username']) ? $headers['Username'] : null;
        $this->password = isset($headers['Password']) ? $headers['Password'] : null;
    }

    // Validate if username and password are present
    public function validate() {
        // Check if username or password are missing
        if (empty($this->username) || empty($this->password)) {
            return ['error' => 'Username and Password are required'];  // Corrected syntax
        }

        return null;  // If valid, return null (no error)
    }
}
