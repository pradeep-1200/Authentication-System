<?php
// ENABLE DEBUGGING
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: application/json");

// Polyfill for getallheaders() if it doesn't exist
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

try {
    // Check if files exist to avoid fatal require errors
    if (!file_exists(__DIR__ . "/config/redis.php")) throw new Exception("config/redis.php not found");
    if (!file_exists(__DIR__ . "/config/mongo.php")) throw new Exception("config/mongo.php not found");

    require_once __DIR__ . "/config/redis.php";
    require_once __DIR__ . "/config/mongo.php";

    // 1. Get token from Authorization header or POST
    $headers = getallheaders();
    $token = '';
    
    // Case-insensitive header check
    $headers = array_change_key_case($headers, CASE_LOWER);
    
    if (isset($headers['authorization'])) {
        $token = $headers['authorization'];
    } elseif (isset($_POST['token'])) {
        $token = $_POST['token'];
    }

    // Remove 'Bearer ' prefix if present
    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7);
    }

    if (!$token) {
        throw new Exception("Session token missing. Debug: Headers received: " . json_encode($headers));
    }

    // 2. Validate token in Redis
    if (!isset($redis)) {
        throw new Exception("Redis connection object not set");
    }
    
    $userId = $redis->get($token);

    if (!$userId) {
        throw new Exception("Invalid or expired session");
    }

    // 3. Fetch profile from MongoDB
    if (!isset($profiles)) {
        throw new Exception("MongoDB collection object not set");
    }

    $profile = $profiles->findOne(
        ["user_id" => (int)$userId],
        ["projection" => ["_id" => 0]]
    );

    // If no profile found in Mongo, return empty data with Name from MySQL (optional, effectively handling null)
    if (!$profile) {
        // Just return empty or partial data instead of error, as profile might be brand new
         $profile = [
            "age" => "",
            "dob" => "",
            "contact" => ""
        ];
    }
    
    // Merge user_id
    $profile['user_id'] = (int)$userId;

    echo json_encode([
        "status" => "success",
        "data" => $profile
    ]);
    exit;

} catch (Throwable $e) {
    // Return 200 so frontend consumes the JSON error message
    http_response_code(200); 
    echo json_encode([
        "status" => "error",
        "message" => "Server Error: " . $e->getMessage(),
        "trace" => $e->getTraceAsString()
    ]);
    exit;
}
