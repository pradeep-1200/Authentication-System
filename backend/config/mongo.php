<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client(getenv("MONGO_URI"));
$db = $client->guvi_internship;
$profiles = $db->profiles;

} catch (Exception $e) {
    // Return JSON error if connection fails immediately
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "MongoDB Connection Error: " . $e->getMessage()]);
    exit;
}
