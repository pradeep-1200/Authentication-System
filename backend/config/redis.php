<?php
ini_set('display_errors', 0);
error_reporting(0);

$redisUrl = getenv("REDIS_HOST");

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
    // TLS (Upstash requires this)
    $redis->connect(
        "tls://" . $parts['host'],
        $parts['port'],
        2.5,
        NULL,
        0,
        0,
        ["verify_peer" => false, "verify_peer_name" => false]
    );

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
