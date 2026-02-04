<?php

header("Content-Type: application/json");

require_once __DIR__ . "/config/mysql.php";
require_once __DIR__ . "/config/redis.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode([
        "status" => "error",
        "message" => "Email and password are required"
    ]);
    exit;
}

$stmt = $conn->prepare("SELECT id, password FROM users WHERE email = ?");
if (!$stmt) {
    echo json_encode(["status"=>"error","message"=>"Database error"]);
    exit;
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if (password_verify($password, $user['password'])) {
        $token = bin2hex(random_bytes(16));
        $redis->setex($token, 3600, $user['id']);

        echo json_encode([
            "status" => "success",
            "message" => "Login successful",
            "token" => $token
        ]);
        exit;
    }
}

echo json_encode([
    "status" => "error",
    "message" => "Invalid email or password"
]);
exit;
