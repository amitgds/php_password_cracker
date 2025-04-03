<?php
/**
 * Core logic for cracking passwords.
 */
class PasswordCracker {
    private DatabaseManager $dbManager;
    private CacheManager $cacheManager;
    private string $salt;

    public function __construct(DatabaseManager $dbManager, CacheManager $cacheManager, string $salt) {
        $this->dbManager = $dbManager;
        $this->cacheManager = $cacheManager;
        $this->salt = $salt;
    }

    private function salter(string $string): string {
        return md5($string . $this->salt);
    }

    /**
     * Generates precomputed password combinations for cracking.
     * @param array $categories The password categories to generate combinations for.
     * @param array $results Current results to check against limits.
     * @return array Array of password => hash mappings.
     */
    private function generateCombinations(array $categories, array $results): array {
        $combinations = [];
        if (count($results["Easy"]) < $categories["Easy"]->getLimit()) {
            for ($i = 10000; $i <= 99999; $i++) {
                $combinations[(string)$i] = $this->salter((string)$i);
            }
        }
        if (count($results["Medium"]) < $categories["Medium"]->getLimit()) {
            foreach (range('A', 'Z') as $l1) {
                foreach (range('A', 'Z') as $l2) {
                    foreach (range('A', 'Z') as $l3) {
                        foreach (range(0, 9) as $num) {
                            $pass = $l1 . $l2 . $l3 . $num;
                            $combinations[$pass] = $this->salter($pass);
                        }
                    }
                }
            }
        }
        return $combinations;
    }

    /**
     * Loads dictionary words and generates their hashes.
     * @return array Array of word => hash mappings.
     */
    private function loadDictionary(): array {
        $dictionary = @file("dictionary.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($dictionary === false) {
            error_log("Dictionary file not found or unreadable at " . getcwd());
            return [];
        }
        $hashes = [];
        foreach ($dictionary as $word) {
            $hashes[trim($word)] = $this->salter(trim($word));
        }
        return $hashes;
    }

    /**
     * Cracks passwords based on predefined categories.
     * @return array Cracked passwords organized by category.
     */
    public function crackPasswords(): array {
        $hashedPasswords = $this->dbManager->getHashedPasswords();
        $precomputed = $this->generateCombinations([
            "Easy" => new EasyCategory(),
            "Medium" => new MediumCategory(),
            "Hard" => new HardCategory()
        ], ["Easy" => [], "Medium" => [], "Hard" => []]);
        $dictionary = $this->loadDictionary();

        $categories = [
            "Easy" => new EasyCategory(),
            "Medium" => new MediumCategory(),
            "Hard" => new HardCategory()
        ];
        $results = ["Easy" => [], "Medium" => [], "Hard" => []];

        foreach ($hashedPasswords as $row) {
            $hash = $row['password'];
            $id = $row['user_id'];
            $password = $this->cacheManager->checkCache($hash);

            if (!$password) {
                $password = array_search($hash, $precomputed, true) ?: array_search($hash, $dictionary, true);
                if ($password !== false) {
                    $this->cacheManager->storeInCache($hash, $password);
                }
            }

            if ($password) {
                foreach ($categories as $name => $category) {
                    if ($category->matches($password) && count($results[$name]) < $category->getLimit()) {
                        $results[$name][] = ["id" => $id, "password" => $password];
                        break;
                    }
                }
            }

            if (count($results["Easy"]) >= 4 && count($results["Medium"]) >= 12 && count($results["Hard"]) >= 2) {
                break;
            }
        }

        return $results;
    }
}