<?php
ini_set('display_errors', 0);
error_reporting(0);

require_once __DIR__ . '/../vendor/autoload.php';

$mongoUri = getenv("MONGO_URI");

if (!$mongoUri) {
    echo json_encode([
        "status" => "error",
        "message" => "MongoDB URI not set"
    ]);
    exit;
}

try {
    $client = new MongoDB\Client($mongoUri);
    $db = $client->selectDatabase("guvi_internship");
    $profiles = $db->profiles;
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "MongoDB connection failed"
    ]);
    exit;
}
