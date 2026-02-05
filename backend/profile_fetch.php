<?php
// MONOLITHIC PROFILE_FETCH.PHP TO PREVENT 500 ERRORS
// We define everything inline to ensure try-catch wraps absolutely everything.

ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json"); // Always return JSON

// Header Polyfill
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
    // ----------------------------------------------------
    // 1. ENVIRONMENT & DEPENDENCY CHECKS
    // ----------------------------------------------------
    
    // Check Autoload (Critical)
    $autoloadPath = __DIR__ . '/vendor/autoload.php';
    if (!file_exists($autoloadPath)) {
        throw new Exception("Vendor autoload missing at: $autoloadPath. Composer install failed.");
    }
    require_once $autoloadPath;
    
    // ----------------------------------------------------
    // 2. TOKEN EXTRACTION
    // ----------------------------------------------------
    $headers = getallheaders();
    $headers = array_change_key_case($headers, CASE_LOWER);
    $token = $headers['authorization'] ?? $_POST['token'] ?? '';

    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7);
    }

    if (!$token) {
        throw new Exception("Session token missing.");
    }

    // ----------------------------------------------------
    // 3. REDIS CONNECTION (Inline)
    // ----------------------------------------------------
    $redisUrl = getenv("REDIS_HOST");
    if (!$redisUrl) {
         throw new Exception("REDIS_HOST env var is not set.");
    }

    $redis = new Redis();
    $parts = parse_url($redisUrl);
    
    // Use TLS if needed (for Upstash/Render)
    $host = $parts['host'] ?? '';
    $port = $parts['port'] ?? 6379;
    $user = $parts['user'] ?? '';
    $pass = $parts['pass'] ?? '';
    
    $scheme = $parts['scheme'] ?? 'tcp';
    if ($scheme === 'rediss') {
        $host = 'tls://' . $host;
    }

    // Connect with timeout
    if (!$redis->connect($host, $port, 2.5)) { // 2.5s timeout
        throw new Exception("Could not connect to Redis host.");
    }
    
    if ($pass) {
        if (!$redis->auth($pass)) {
             throw new Exception("Redis authentication failed.");
        }
    }

    // ----------------------------------------------------
    // 4. VALIDATE SESSION
    // ----------------------------------------------------
    $userId = $redis->get($token);
    if (!$userId) {
        throw new Exception("Invalid or expired session.");
    }

    // ----------------------------------------------------
    // 5. MONGO CONNECTION (Inline)
    // ----------------------------------------------------
    $mongoUri = getenv("MONGO_URI");
    if (!$mongoUri) {
        throw new Exception("MONGO_URI env var is not set.");
    }
    
    if (!class_exists('MongoDB\Client')) {
        throw new Exception("MongoDB Client library not loaded.");
    }

    $client = new MongoDB\Client($mongoUri);
    $db = $client->selectDatabase("guvi_internship");
    $profiles = $db->profiles;

    // ----------------------------------------------------
    // 6. FETCH DATA
    // ----------------------------------------------------
    $profile = $profiles->findOne(
        ["user_id" => (int)$userId],
        ["projection" => ["_id" => 0]]
    );

    // Default object if not found
    if (!$profile) {
        $profile = (object)[];
    }
    
    // Check key fields
    $resultData = [
        "user_id" => (int)$userId,
        "age" => $profile['age'] ?? '',
        "dob" => $profile['dob'] ?? '',
        "contact" => $profile['contact'] ?? ''
    ];

    echo json_encode([
        "status" => "success",
        "data" => $resultData
    ]);
    exit;

} catch (Throwable $e) {
    // FORCE 200 OK so frontend handles the error
    http_response_code(200); 
    echo json_encode([
        "status" => "error",
        "message" => "Backend Error: " . $e->getMessage()
    ]);
    exit;
}
