<?php
ini_set('display_errors', 0);
error_reporting(0);

$redisUrl = getenv("REDIS_HOST"); // full rediss:// URL

if (!$redisUrl) {
    echo json_encode([
        "status" => "error",
        "message" => "Redis URL not set"
    ]);
    exit;
}

$parts = parse_url($redisUrl);

$redis = new Redis();

try {
    $redis->connect($parts['host'], $parts['port'], 2.5);

    if (isset($parts['pass'])) {
        $redis->auth($parts['pass']);
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Redis connection failed"
    ]);
    exit;
}
