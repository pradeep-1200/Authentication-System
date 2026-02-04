<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

try {
    require_once __DIR__ . "/config/mysql.php";
    require_once __DIR__ . "/config/mongo.php";

    if (!$conn) throw new Exception("MySQL connection failed");
    if (!isset($profiles)) throw new Exception("MongoDB not initialized");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';

    if (!$name || !$email || !$password || !$age || !$dob || !$contact) {
        throw new Exception("All fields are required");
    }

    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$checkStmt) throw new Exception("MySQL prepare failed");

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    $checkStmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
    if (!$stmt) throw new Exception("MySQL prepare failed");

    $stmt->bind_param("sss", $name, $email, $hashedPassword);
    if (!$stmt->execute()) {
        throw new Exception("MySQL execute failed");
    }

    $userId = $stmt->insert_id;
    $stmt->close();

    $profiles->insertOne([
        "user_id" => (int)$userId,
        "age" => $age,
        "dob" => $dob,
        "contact" => $contact
    ]);

    echo json_encode([
        "status" => "success",
        "message" => "Registration successful"
    ]);
    exit;

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
