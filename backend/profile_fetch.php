<?php
// Robust profile fetch with consistent error reporting

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php://stderr');
error_reporting(E_ALL);

header("Content-Type: application/json");
http_response_code(200);

set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function ($e) {
    echo json_encode([
        "status" => "error",
        "message" => "Unhandled error: " . $e->getMessage()
    ]);
    exit;
});

register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        echo json_encode([
            "status" => "error",
            "message" => "Fatal error: " . $error['message'] . " in " . $error['file'] . ":" . $error['line']
        ]);
        exit;
    }
});

try {
    // Token extraction
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $headers = array_change_key_case($headers, CASE_LOWER);
    $token = $headers['authorization'] ?? $_POST['token'] ?? $_GET['token'] ?? '';

    if (strpos($token, 'Bearer ') === 0) {
        $token = substr($token, 7);
    }

    if (!$token) {
        throw new Exception("Session token missing.");
    }

    // Redis + Mongo connections (reuse shared configs)
    require_once __DIR__ . "/config/redis.php";
    require_once __DIR__ . "/config/mongo.php";

    if (!isset($redis)) {
        throw new Exception("Redis client not initialized.");
    }
    if (!isset($profiles)) {
        throw new Exception("Mongo collection not initialized.");
    }

    // Session lookup
    $userId = $redis->get($token);
    if (!$userId) {
        throw new Exception("Invalid session token");
    }

    // Profile fetch
    $profile = $profiles->findOne(
        ["user_id" => (int)$userId],
        ["projection" => ["_id" => 0]]
    );

    if (!$profile) {
        $profile = (object)[];
    }

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
    echo json_encode([
        "status" => "error",
        "message" => "Error: " . $e->getMessage()
    ]);
}
