<?php

$host = getenv("MYSQL_HOST");
$user = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");
$dbname = getenv("MYSQL_DB");

// Suppress default warnings to handle connection errors manually
mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "error", 
        "message" => "Database connection failed: " . $conn->connect_error
    ]);
    exit;
}
