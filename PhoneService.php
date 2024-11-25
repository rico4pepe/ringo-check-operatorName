<?php

require_once 'Logger.php'; 
class PhoneService {

    private $db;

    private $logger;

     // Constructor to accept database connection
     public function __construct($db) {
        $this->db = $db;  // Assign the database connection to the class property
        $this->logger = new Logger();
    }
    
    public function processPhoneNumber($phoneNumber, $initialNetwork){
            try{

                 // Log the phone number and initial network type before insertion
            $this->logger->log("Processing phone number: $phoneNumber with initial network: $initialNetwork");

                // Insert the phone number and initial network type into the database
                $query = "INSERT INTO network_table (phone_number, initial_network_type) VALUES (:phone_number, :initial_network)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':phone_number', $phoneNumber);
                $stmt->bindParam(':initial_network', $initialNetwork);
                $stmt->execute();
    
                // Retrieve the last inserted ID
                $lastInsertedId = $this->db->lastInsertId();
                $this->logger->log("Inserted phone number: $phoneNumber with ID: $lastInsertedId");

            }catch(PDOException $e){

                return ['error' => 'Database insert error: ' . $e->getMessage()];

            }

            $apiUrl = 'https://messaging.approot.ng/interconnect.php?phone=' . $phoneNumber;

            // Log the API request
        $this->logger->log("Requesting network information for phone number: $phoneNumber via API: $apiUrl");

           // print_r($apiUrl);

            // $apiUrl = 'https://example.com/external-api?' .
            // http_build_query([
            //     'phone_number' => $phoneNumber,
            // ]);

            $ch = curl_init($apiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
    
            if ($httpCode !== 200) {
                 // Log failure to retrieve network information
            $this->logger->log("Failed to retrieve network information for phone number: $phoneNumber, HTTP Code: $httpCode");
                return ['error' => 'Failed to retrieve network information'];
            }
    
            $responseBody = json_decode($response, true);

            $operatorName = $responseBody['result'][0]['operatorDetail']['operatorName'];
            //print_r($operatorName);

            // Log the retrieved operator name
        $this->logger->log("Operator name retrieved for phone number: $phoneNumber: $operatorName");

            // $newNetwork = $responseBody['new_network'] ?? null;

            // print_r($responseBody);

             // If the operator name is found, update the network information in the database
            if ($operatorName) {
                try {
                    $updateQuery = "UPDATE network_table SET current_netwrk_type = :updated_network WHERE id = :id";
                    $updateStmt = $this->db->prepare($updateQuery);
                    $updateStmt->bindParam(':updated_network', $operatorName);
                    $updateStmt->bindParam(':id', $lastInsertedId);
                    $updateStmt->execute();

                     // Log the successful update
                $this->logger->log("Updated network type for phone number: $phoneNumber to $operatorName with ID: $lastInsertedId");
    
                    return [
                        'success' => true,
                        'id' => $lastInsertedId,
                        'phone_number' => $phoneNumber,
                        'initial_network' => $initialNetwork,
                        'updated_network' => $operatorName
                    ];
                } catch (PDOException $e) {
                     // Log database update error
                    $this->logger->log("Database update error: " . $e->getMessage());
                    return ['error' => 'Database update error: ' . $e->getMessage()];
                }
            }

            
    }
}
