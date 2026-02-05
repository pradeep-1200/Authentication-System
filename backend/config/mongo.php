<?php
// Load Composer autoloader safely
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($autoloadPath)) {
    echo json_encode([
        "status" => "error",
        "message" => "Composer autoload not found at: " . $autoloadPath
    ]);
    exit;
}
require_once $autoloadPath;

$mongoUri = getenv("MONGO_URI");

if (!$mongoUri) {
    // Graceful fallback for debugging or local testing
    // echo json_encode(["status" => "error", "message" => "MongoDB URI not set"]);
    // exit;
}

if (!class_exists('MongoDB\\Client')) {
    echo json_encode([
        "status" => "error",
        "message" => "MongoDB Client class not found. Check mongodb extension and composer install."
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
        "message" => "MongoDB connection failed: " . $e->getMessage()
    ]);
    exit;
}
