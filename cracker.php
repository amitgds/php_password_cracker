<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
set_time_limit(0);
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('SALT', 'ThisIs-A-Salt123');

require 'config/db.php';

function salter($string) {
    return md5($string . SALT);
}

function checkCache($pdo, $hash) {
    $stmt = $pdo->prepare("SELECT password FROM hash_cache WHERE hash = :hash LIMIT 1");
    $stmt->execute(['hash' => $hash]);
    return $stmt->fetchColumn();
}

function storeInCache($pdo, $hash, $password) {
    $stmt = $pdo->prepare("INSERT INTO hash_cache (hash, password) VALUES (:hash, :password) ON CONFLICT(hash) DO NOTHING");
    $stmt->execute(['hash' => $hash, 'password' => $password]);
}

function getHashedPasswords($pdo) {
    $stmt = $pdo->query("SELECT user_id, `password` FROM not_so_smart_users");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function generateCombinations() {
    $combinations = [];

    // Precompute numeric passwords (10000-99999)
    for ($i = 10000; $i <= 99999; $i++) {
        $combinations[(string)$i] = salter((string)$i);
    }

    // Precompute 3-uppercase-letters + 1-digit passwords
    foreach (range('A', 'Z') as $l1) {
        foreach (range('A', 'Z') as $l2) {
            foreach (range('A', 'Z') as $l3) {
                foreach (range(0, 9) as $num) {
                    $pass = $l1 . $l2 . $l3 . $num;
                    $combinations[$pass] = salter($pass);
                }
            }
        }
    }

    return $combinations;
}

function crackPasswords($pdo, $sqlitePdo) {
    $hashedPasswords = getHashedPasswords($pdo);
    $dictionary = file("dictionary.txt", FILE_IGNORE_NEW_LINES);
    $dictionaryHashesMedium = [];
    $dictionaryHashesHard = [];

    // Process dictionary words
    foreach ($dictionary as $word) {
        $word = trim($word);
        // Only lowercase words of exactly 6 chars for medium
        if (strlen($word) == 6 && ctype_lower($word)) {
            $dictionaryHashesMedium[$word] = salter($word); 
        }
        // Words that are mixed-case or have numbers are considered hard
        if (strlen($word) >= 6 && preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])/', $word)) {
            $dictionaryHashesHard[$word] = salter($word); 
        }
    }

    $precomputed = generateCombinations();

    $categories = [
        "Easy" => [],
        "Medium" => [], // Merged Medium category
        "Hard" => []
    ];

    foreach ($hashedPasswords as $row) {
        $hash = $row['password'];
        $id = $row['user_id'];
        $password = null;

        // Check in precomputed passwords (Easy + Medium_Type1 + Hard)
        if (($password = array_search($hash, $precomputed, true)) !== false) {
            // Easy category (Numeric 5-digit)
            if (ctype_digit((string)$password) && count($categories["Easy"]) < 4) {
                $categories["Easy"][] = ["id" => $id, "password" => $password];
            }
            // Medium category (3 uppercase letters + 1 digit)
            elseif (preg_match('/^[A-Z]{3}[0-9]$/', $password) && count($categories["Medium"]) < 12) {
                $categories["Medium"][] = ["id" => $id, "password" => $password];
            }
            // Hard category (complex passwords)
            elseif (preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}$/', $password) && count($categories["Hard"]) < 2) {
                $categories["Hard"][] = ["id" => $id, "password" => $password];
                continue;
            }
        }

        // Check in dictionary for Hard (6+ chars, mixed case, number)
        if (($password = array_search($hash, $dictionaryHashesHard, true)) !== false) {
            if (count($categories["Hard"]) < 2) {
                $categories["Hard"][] = ["id" => $id, "password" => $password];
            }
        }

        // Check in dictionary for Medium (only lowercase, exactly 6 characters)
        if (($password = array_search($hash, $dictionaryHashesMedium, true)) !== false) {
            if (count($categories["Medium"]) < 12) {
                $categories["Medium"][] = ["id" => $id, "password" => $password];
            }
        }

        if (preg_match('/^[a-z]{3,8}$/', $password) && !isset($dictionaryHashesMedium[$password])) {
            if (count($categories["Medium"]) < 6) {
                $categories["Medium"][] = ["id" => $id, "password" => $password];
            }
        }
        // Store cracked password in cache
        if (!empty($password)) {
            storeInCache($sqlitePdo, $hash, $password);
        }

        // Stop if all category limits are met
        if (count($categories["Easy"]) >= 4 && count($categories["Medium"]) >= 12 && count($categories["Hard"]) >= 2) {
            break;
        }
    }

    return [
        "Easy" => $categories["Easy"],
        "Medium" => $categories["Medium"], // Merge Medium categories
        "Hard" => $categories["Hard"]
    ];
}

$cracked = crackPasswords($pdo, $sqlitePdo);
echo json_encode($cracked, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);


?>
