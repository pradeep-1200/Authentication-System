<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config/redis.php";

$token = $_POST['token'] ?? '';

if (!$token) {
    echo json_encode([
        "status" => "error",
        "message" => "No token provided"
    ]);
    exit;
}

$userId = $redis->get($token);

if ($userId) {
    echo json_encode([
        "status" => "success",
        "user_id" => $userId
    ]);
    exit;
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Invalid session"
    ]);
    exit;
}
