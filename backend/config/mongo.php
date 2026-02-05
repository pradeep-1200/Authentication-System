<?php


require_once __DIR__ . '/../vendor/autoload.php';

$mongoUri = getenv("MONGO_URI");

if (!$mongoUri) {
    // Graceful fallback for debugging or local testing
    // echo json_encode(["status" => "error", "message" => "MongoDB URI not set"]);
    // exit;
}

try {
    $client = new MongoDB\Client($mongoUri);
    $db = $client->selectDatabase("guvi_internship");
    $profiles = $db->profiles;
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "MongoDB connection failed: " . $e->getMessage()
    ]);
    exit;
}
