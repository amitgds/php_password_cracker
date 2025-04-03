<?php
/**
 * Manages database interactions for the password cracker.
 */
class DatabaseManager {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Retrieves hashed passwords and user IDs from the database.
     * @return array Array of [user_id, password] pairs.
     */
    public function getHashedPasswords(): array {
        $stmt = $this->pdo->query("SELECT user_id, `password` FROM not_so_smart_users");
        return $stmt->fetchAll();
    }
}