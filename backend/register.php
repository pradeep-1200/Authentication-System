<?php
ini_set('display_errors', 0);
error_reporting(0);
header("Content-Type: application/json");

try {
    /* 
       DB Connections:
       - MySQL for Authentication (users table)
       - MongoDB for Profile (profiles collection)
    */
    if (!file_exists(__DIR__ . "/config/mysql.php")) {
        throw new Exception("Missing config/mysql.php");
    }
    if (!file_exists(__DIR__ . "/config/mongo.php")) {
        throw new Exception("Missing config/mongo.php");
    }

    require_once __DIR__ . "/config/mysql.php";
    require_once __DIR__ . "/config/mongo.php";

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    // 1. Capture All Inputs
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $age = $_POST['age'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $contact = $_POST['contact'] ?? '';

    // 2. Validate Required Fields
    if (empty($name) || empty($email) || empty($password) || empty($age) || empty($dob) || empty($contact)) {
        throw new Exception("All fields (Account & Profile) are required");
    }

    // 3. Insert into MySQL (Authentication)
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Check if email exists first
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();
    
    if ($checkStmt->num_rows > 0) {
        throw new Exception("Email already exists");
    }
    $checkStmt->close();

    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $hashedPassword);

    if ($stmt->execute()) {
        
        // 4. Get the New User ID
        $newUserId = $stmt->insert_id;

        // 5. Insert into MongoDB (Profile)
        $profileData = [
            "user_id" => (int)$newUserId,
            "age" => $age,
            "dob" => $dob,
            "contact" => $contact
        ];

        // Access global profiles collection from config/mongo.php
        global $profiles;
        if (!isset($profiles)) {
             throw new Exception("MongoDB collection not initialized");
        }

        $profiles->insertOne($profileData);
        
        echo json_encode([
            "status" => "success",
            "message" => "Registration successful"
        ]);
        exit;

    } else {
        throw new Exception("Database error: Registration failed");
    }

} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => $e->getMessage()
    ]);
    exit;
}
