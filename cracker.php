<?php
header("Access-Control-Allow-Origin: http://localhost");
header("Access-Control-Allow-Methods: GET");
header("Content-Type: application/json");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Content-Security-Policy: default-src 'self'");

session_start();
$rateLimit = 5; // Max 5 requests per minute
$timeWindow = 60; // 1 minute

if (!isset($_SESSION['request_count'])) {
    $_SESSION['request_count'] = 0;
    $_SESSION['first_request_time'] = time();
}

if (time() - $_SESSION['first_request_time'] > $timeWindow) {
    $_SESSION['request_count'] = 0;
    $_SESSION['first_request_time'] = time();
}

if ($_SESSION['request_count'] >= $rateLimit) {
    http_response_code(429);
    echo json_encode(["error" => "Too many requests. Please try again later."]);
    exit;
}

$_SESSION['request_count']++;

require 'config/db.php';
require 'src/DatabaseManager.php';
require 'src/CacheManager.php';
require 'src/PasswordCategory.php';
require 'src/PasswordCracker.php';

try {
    $dbManager = new DatabaseManager($pdo);
    $cacheManager = new CacheManager($sqlitePdo);
    $cracker = new PasswordCracker($dbManager, $cacheManager, $_ENV['SALT']);
    $cracked = $cracker->crackPasswords();
    echo json_encode($cracked, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "General error: " . $e->getMessage()]);
}