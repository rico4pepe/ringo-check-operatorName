<?php

class PhoneService {

    private $db;

     // Constructor to accept database connection
     public function __construct($db) {
        $this->db = $db;  // Assign the database connection to the class property
    }
    
    public function processPhoneNumber($phoneNumber, $initialNetwork){
            try{

                $query = "INSERT INTO network_table (phone_number, initial_network_type) VALUES (:phone_number, :initial_network)";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':phone_number', $phoneNumber);
                $stmt->bindParam(':initial_network', $initialNetwork);
                $stmt->execute();
    
                // Retrieve the last inserted ID
                $lastInsertedId = $this->db->lastInsertId();

            }catch(PDOException $e){

                return ['error' => 'Database insert error: ' . $e->getMessage()];

            }

            $apiUrl = 'https://messaging.approot.ng/interconnect.php?phone=' . $phoneNumber;

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
                return ['error' => 'Failed to retrieve network information'];
            }
    
            $responseBody = json_decode($response, true);

            $operatorName = $responseBody['result'][0]['operatorDetail']['operatorName'];
            //print_r($operatorName);

            // $newNetwork = $responseBody['new_network'] ?? null;

            // print_r($responseBody);

            if ($operatorName) {
                try {
                    $updateQuery = "UPDATE network_table SET current_netwrk_type = :updated_network WHERE id = :id";
                    $updateStmt = $this->db->prepare($updateQuery);
                    $updateStmt->bindParam(':updated_network', $operatorName);
                    $updateStmt->bindParam(':id', $lastInsertedId);
                    $updateStmt->execute();
    
                    return [
                        'success' => true,
                        'id' => $lastInsertedId,
                        'phone_number' => $phoneNumber,
                        'initial_network' => $initialNetwork,
                        'updated_network' => $operatorName
                    ];
                } catch (PDOException $e) {
                    return ['error' => 'Database update error: ' . $e->getMessage()];
                }
            }

            
    }
}
