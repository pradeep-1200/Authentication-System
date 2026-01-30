<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

require_once __DIR__ . "/config/redis.php";
require_once __DIR__ . "/config/mongo.php";

$token = $_POST['token'] ?? '';
$userId = $redis->get($token);

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Invalid session"]);
    exit;
}

$data = [
    "user_id" => (int)$userId,
    "age" => $_POST['age'] ?? '',
    "dob" => $_POST['dob'] ?? '',
    "contact" => $_POST['contact'] ?? ''
];

$profiles->updateOne(
    ["user_id" => (int)$userId],
    ['$set' => $data],
    ["upsert" => true]
);

echo json_encode([
    "status" => "success",
    "message" => "Profile updated successfully"
]);
exit;
