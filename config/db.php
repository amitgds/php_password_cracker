<?php
$dsn = "mysql:host=localhost;dbname=cracker;charset=utf8mb4";
$username = "root";
$password = "";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true 
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// SQLite Connection for Hash Cache
try {
    $sqlitePdo = new PDO("sqlite:hash_cache.db");
    $sqlitePdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "✅ SQLite Connected Successfully";
} catch (PDOException $e) {
    die("SQLite connection failed: " . $e->getMessage());
}

$stmt = $sqlitePdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='hash_cache'");
if ($stmt->fetch()) {
    // echo "✅ Table 'hash_cache' exists.";
} else {
    // echo "❌ Table 'hash_cache' NOT found!";
}


?>
