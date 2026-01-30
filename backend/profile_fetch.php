<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

require_once __DIR__ . "/config/redis.php";
require_once __DIR__ . "/config/mongo.php";
require_once __DIR__ . "/config/mysql.php";

$token = $_POST['token'] ?? '';
if (!$token) {
    echo json_encode(["status" => "error", "message" => "No token provided"]);
    exit;
}

$userId = $redis->get($token);

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Invalid session"]);
    exit;
}

// Fetch Name from MySQL
$stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$name = $user ? $user['name'] : "";
$stmt->close();

$profile = $profiles->findOne(["user_id" => (int)$userId]);

$data = [
    "name" => $name,
    "age" => $profile["age"] ?? "",
    "dob" => $profile["dob"] ?? "",
    "contact" => $profile["contact"] ?? ""
];

echo json_encode([
    "status" => "success",
    "data" => $data
]);
exit;
