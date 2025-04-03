<?php
/**
 * Interface for password category definitions.
 */
interface PasswordCategory {
    public function matches(string $password): bool;
    public function getLimit(): int;
}

/**
 * Category for 5-digit numeric passwords.
 */
class EasyCategory implements PasswordCategory {
    public function matches(string $password): bool {
        return ctype_digit($password) && strlen($password) == 5;
    }
    public function getLimit(): int {
        return 4;
    }
}

/**
 * Category for passwords with 3 uppercase letters + 1 digit or 6-char lowercase words.
 */
class MediumCategory implements PasswordCategory {
    public function matches(string $password): bool {
        return (preg_match('/^[A-Z]{3}[0-9]$/', $password) || 
                (strlen($password) <= 6 && ctype_lower($password)));
    }
    public function getLimit(): int {
        return 12;
    }
}

/**
 * Category for 6-char mixed-case passwords with numbers.
 */
class HardCategory implements PasswordCategory {
    public function matches(string $password): bool {
        return strlen($password) == 6 && 
               preg_match('/[a-z]/', $password) && 
               preg_match('/[A-Z]/', $password) && 
               preg_match('/[0-9]/', $password);
    }
    public function getLimit(): int {
        return 2;
    }
}