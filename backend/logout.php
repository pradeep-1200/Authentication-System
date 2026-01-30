<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

require_once __DIR__ . "/config/redis.php";

$token = $_POST['token'] ?? null;

if ($token) {
    $redis->del($token);
}

echo json_encode([
    "status" => "success",
    "message" => "Logged out"
]);
exit;
