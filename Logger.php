<?php

class Logger {

    private $logDirectory;

    // Constructor to initialize log directory
    public function __construct($logDirectory = '/var/www/html/powersmpp/log') {
        $this->logDirectory = $logDirectory;

        // Create the log directory if it doesn't exist
        if (!file_exists($this->logDirectory)) {
            mkdir($this->logDirectory, 0777, true);  // Create directory with appropriate permissions
        }
    }

    // Method to log messages
    public function log($log_msg) {
        $logFile = $this->logDirectory . '/log_' . date('d-M-Y') . '.log';
        
        // Write the log message to the log file (appends to the file)
        file_put_contents($logFile, $log_msg . "\n", FILE_APPEND);
    }
}




