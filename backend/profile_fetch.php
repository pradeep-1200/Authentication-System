<?php
header("Content-Type: application/json");

require_once "config/redis.php";
require_once "config/mongo.php";

$token = $_POST['token'];
$userId = $redis->get($token);

if (!$userId) {
    echo json_encode(["status" => "error", "message" => "Invalid session"]);
    exit;
}

$profile = $profiles->findOne(["user_id" => (int)$userId]);

if ($profile) {
    echo json_encode([
        "status" => "success",
        "data" => [
            "age" => $profile["age"] ?? "",
            "dob" => $profile["dob"] ?? "",
            "contact" => $profile["contact"] ?? ""
        ]
    ]);
} else {
    echo json_encode([
        "status" => "success",
        "data" => [
            "age" => "",
            "dob" => "",
            "contact" => ""
        ]
    ]);
}
