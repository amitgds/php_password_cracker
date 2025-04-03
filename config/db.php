<?php
require 'vendor/autoload.php'; // Assumes Composer with phpdotenv
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// MySQL Connection
$dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true
    ]);
} catch (PDOException $e) {
    error_log("MySQL connection failed: " . $e->getMessage());
    throw $e;
}

// SQLite Connection
$sqliteDbPath = $_ENV['SQLITE_DB'] ?? 'hash_cache.db';
try {
    $sqlitePdo = new PDO("sqlite:" . $sqliteDbPath);
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlitePdo->exec("CREATE TABLE IF NOT EXISTS hash_cache (hash TEXT PRIMARY KEY, password TEXT)");
} catch (PDOException $e) {
    error_log("SQLite connection failed: " . $e->getMessage());
    throw $e;
}