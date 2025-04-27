<?php
// Set CORS headers to allow requests from your domain
header('Access-Control-Allow-Origin: *'); // Replace * with your domain in production
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log errors to file 
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Authentication credentials - stored securely on the server
// Replace these with your actual credentials
$n8n_username = 'your_username';
$n8n_password = 'your_password';
$n8n_webhook_url = 'your_n8n_webhook_url';

// Get request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Log incoming data for debugging
error_log("Received data: " . $input);

// Validate input
if (!$data || !isset($data['chatInput']) || !isset($data['sessionId'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data']);
    exit;
}

// Prepare the request to n8n
$ch = curl_init($n8n_webhook_url);

// Set up authentication with basic auth
curl_setopt($ch, CURLOPT_USERPWD, $n8n_username . ':' . $n8n_password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $input);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

// Option to see the full request/response for debugging
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

// Execute the request
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Get verbose information for debugging
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
error_log("cURL verbose: " . $verboseLog);

// Log the response for debugging
error_log("HTTP Code: " . $http_code);
error_log("Response: " . $response);

// Check for errors
if (curl_errno($ch)) {
    error_log("cURL Error: " . curl_error($ch));
    http_response_code(500);
    echo json_encode(['error' => 'Error connecting to service: ' . curl_error($ch)]);
    exit;
}

curl_close($ch);

// If we got an empty response but HTTP code is 200, return a default format
if (empty($response) && $http_code == 200) {
    echo json_encode(['output' => 'The request was successful but returned no data.']);
    exit;
}

// Return the response status code
http_response_code($http_code);

// If we got a non-empty response, check if it's valid JSON
if (!empty($response)) {
    // Try to detect if the response is already JSON
    $json_check = json_decode($response);
    if (json_last_error() != JSON_ERROR_NONE) {
        // Response is not valid JSON, wrap it
        echo json_encode(['output' => $response]);
    } else {
        // Response is already valid JSON, pass it through
        echo $response;
    }
} else {
    // Empty response
    echo json_encode(['output' => 'Received an empty response from the service.']);
}
?> 