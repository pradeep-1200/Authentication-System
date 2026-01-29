<?php
require __DIR__ . '/vendor/autoload.php';

try {
    $client = new MongoDB\Client("mongodb://localhost:27017");
    $db = $client->guvi_test;
    $collection = $db->check;

    $collection->insertOne([
        "status" => "working",
        "time" => date("Y-m-d H:i:s")
    ]);

    echo "✅ MongoDB write successful";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
