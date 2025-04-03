<?php
/**
 * Manages caching of cracked passwords in SQLite.
 */
class CacheManager {
    private PDO $sqlitePdo;

    public function __construct(PDO $sqlitePdo) {
        $this->sqlitePdo = $sqlitePdo;
    }

    /**
     * Checks if a hash exists in the cache.
     * @param string $hash The hash to look up.
     * @return string|null The cached password or null if not found.
     */
    public function checkCache(string $hash): ?string {
        $stmt = $this->sqlitePdo->prepare("SELECT password FROM hash_cache WHERE hash = :hash LIMIT 1");
        $stmt->execute(['hash' => $hash]);
        return $stmt->fetchColumn() ?: null;
    }

    /**
     * Stores a cracked password in the cache.
     * @param string $hash The hash of the password.
     * @param string $password The cracked password.
     */
    public function storeInCache(string $hash, string $password): void {
        $stmt = $this->sqlitePdo->prepare("INSERT OR IGNORE INTO hash_cache (hash, password) VALUES (:hash, :password)");
        $stmt->execute(['hash' => $hash, 'password' => $password]);
    }
}