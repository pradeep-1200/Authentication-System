<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/config/redis.php";
    require_once __DIR__ . "/config/mongo.php";

    // 1. Get token from Authorization header
    $headers = getallheaders();
    $token = $headers['Authorization'] ?? '';

    if (!$token) {
        throw new Exception("Session token missing");
    }

    // 2. Validate token in Redis
    $userId = $redis->get($token);

    if (!$userId) {
        throw new Exception("Invalid or expired session");
    }

    // 3. Fetch profile from MongoDB
    $profile = $profiles->findOne(
        ["user_id" => (int)$userId],
        ["projection" => ["_id" => 0]]
    );

    if (!$profile) {
        throw new Exception("Profile not found");
    }

    echo json_encode([
        "status" => "success",
        "data" => $profile
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
