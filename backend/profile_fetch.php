<?php
// ROBUST PROFILE FETCH v3
// Explicitly handles errors including require failures

ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

// Define a shutdown function to catch fatal errors (like require failures)
function shutdownHandler() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE || $error['type'] === E_CORE_ERROR)) {
        http_response_code(200); // Force 200 to show message in frontend
        echo json_encode([
            "status" => "error",
            "message" => "FATAL ERROR: " . $error['message'] . " in " . $error['file'] . ":" . $error['line']
        ]);
        exit;
    }
}
register_shutdown_function('shutdownHandler');

try {
    // ----------------------------------------------------
    // PRE-CHECK: Vendor
    // ----------------------------------------------------
    $backendDir = __DIR__; // /var/www/html/backend
    $autoloadPath = $backendDir . '/vendor/autoload.php';

    if (!file_exists($autoloadPath)) {
        throw new Exception("Autoload not found at $autoloadPath");
    }

    // Try to require - if this fails due to permissions, shutdownHandler catches it
    require_once $autoloadPath;
    
    // Check if MongoDB Library loaded
    if (!class_exists('MongoDB\Client')) {
         throw new Exception("MongoDB\Client class not found after autoload.");
    }

    // ----------------------------------------------------
    // TOKEN
    // ----------------------------------------------------
    // Headers polyfill inline
    $headers = [];
    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                 $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
    }
    
    $headers = array_change_key_case($headers, CASE_LOWER);
    $token = $headers['authorization'] ?? $_POST['token'] ?? '';
    
    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7);
    }

    if (!$token) {
        // Fallback: Check if sent via GET query param (sometimes useful for debugging)
        $token = $_GET['token'] ?? '';
    }

    if (!$token) {
        throw new Exception("Session token missing.");
    }

    // ----------------------------------------------------
    // REDIS
    // ----------------------------------------------------
    $redisUrl = getenv("REDIS_HOST");
    if (!$redisUrl) throw new Exception("REDIS_HOST missing");

    $redis = new Redis();
    $parts = parse_url($redisUrl);
    
    $host = $parts['host'] ?? '';
    $port = $parts['port'] ?? 6379;
    $user = $parts['user'] ?? '';
    $pass = $parts['pass'] ?? '';
    
    if (($parts['scheme']??'') === 'rediss') $host = 'tls://' . $host;

    if (!$redis->connect($host, $port, 2.5)) throw new Exception("Redis connect failed");
    if ($pass && !$redis->auth($pass)) throw new Exception("Redis auth failed");

    // ----------------------------------------------------
    // SESSION LOOKUP
    // ----------------------------------------------------
    $userId = $redis->get($token);
    if (!$userId) throw new Exception("Invalid session token");

    // ----------------------------------------------------
    // MONGO FETCH
    // ----------------------------------------------------
    $mongoUri = getenv("MONGO_URI");
    if (!$mongoUri) throw new Exception("MONGO_URI missing");

    $client = new MongoDB\Client($mongoUri);
    $db = $client->selectDatabase("guvi_internship");
    $profiles = $db->profiles;

    $profile = $profiles->findOne(
        ["user_id" => (int)$userId],
        ["projection" => ["_id" => 0]]
    );
    
    if (!$profile) $profile = (object)[];

    echo json_encode([
        "status" => "success",
        "data" => [
            "user_id" => (int)$userId,
            "age" => $profile['age'] ?? '',
            "dob" => $profile['dob'] ?? '',
            "contact" => $profile['contact'] ?? ''
        ]
    ]);

} catch (Throwable $e) {
    http_response_code(200);
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
