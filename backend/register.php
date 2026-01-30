<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header("Content-Type: application/json");

try {
    // 1. Include DB configs
    require_once __DIR__ . "/config/mysql.php"; // $conn
    require_once __DIR__ . "/config/mongo.php"; // $profiles

    if (!$conn) throw new Exception("MySQL connection failed");
    if (!isset($profiles)) throw new Exception("MongoDB collection not initialized");

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // 2. Capture inputs
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';

    if (!$name || !$email || !$password || !$age || !$dob || !$contact) {
        throw new Exception("All fields are required");
    }

    // 3. Check if email exists
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email=?");
    if (!$checkStmt) throw new Exception("MySQL prepare failed: " . $conn->error);

    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    $checkStmt->close();

    // 4. Insert into MySQL
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
    if (!$stmt) throw new Exception("MySQL prepare failed: " . $conn->error);

    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if (!$stmt->execute()) {
        throw new Exception("MySQL execute failed: " . $stmt->error);
    }

    $newUserId = $stmt->insert_id;
    $stmt->close();

    // 5. Insert into MongoDB
    $profileData = [
        "user_id" => (int)$newUserId,
        "age" => $age,
        "dob" => $dob,
        "contact" => $contact
    ];

    $profiles->insertOne($profileData);

    echo json_encode(["status"=>"success","message"=>"Registration successful"]);
    exit;

} catch (Exception $e) {
    echo json_encode(["status"=>"error","message"=>$e->getMessage()]);
    exit;
}
