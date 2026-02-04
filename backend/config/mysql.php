<?php

header("Content-Type: application/json");

$host = getenv("MYSQL_HOST");
$user = getenv("MYSQL_USER");
$password = getenv("MYSQL_PASSWORD");
$dbname = getenv("MYSQL_DB");
$port = getenv("MYSQL_PORT");

mysqli_report(MYSQLI_REPORT_OFF);

$conn = @new mysqli($host, $user, $password, $dbname, (int)$port);

if ($conn->connect_error) {
    echo json_encode([
        "status" => "error",
        "message" => "Database connection failed"
    ]);
    exit;
}
