<?php

require_once __DIR__ . '/../vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb+srv://voiddragon351_db_user:12560@account-portal.lmbhutd.mongodb.net/");
    $db = $client->account_portal;
    $profiles = $db->profiles;
} catch (Exception $e) {
    // Return JSON error if connection fails immediately
    header("Content-Type: application/json");
    echo json_encode(["status" => "error", "message" => "MongoDB Connection Error: " . $e->getMessage()]);
    exit;
}
