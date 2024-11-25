<?php
require_once 'Database.php';
require_once 'HeaderAuth.php';
require_once 'PhoneService.php';

header('Content-Type: application/json');

// Fallback for `getallheaders()` if not available
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                // Convert HTTP_HEADER_NAME to Header-Name format
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }
}

// Get headers
$headers = getallheaders();
$auth = new HeaderAuth($headers);

// Validate headers
$authError = $auth->validate();
if ($authError) {
    echo json_encode(['error' => $authError]);
    exit;
}

// Get request data
$requestData = json_decode(file_get_contents("php://input"), true);
$phoneNumber = $requestData['phone_number'] ?? null;
$networkType = $requestData['network_type'] ?? null;

if (empty($phoneNumber) || empty($networkType)) {
    echo json_encode(['error' => 'Phone number and network type are required']);
    exit;
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Process phone number
$phoneService = new PhoneService($db);
$result = $phoneService->processPhoneNumber($phoneNumber, $networkType);

// Return response
echo json_encode($result);
