<?php
declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use PDO;

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    /** Create a user. Passwords are hashed with bcrypt/argon via password_hash(). */
    public function register(string $username, string $password): int
    {
        $username = trim($username);
        if (!preg_match('/^[A-Za-z0-9_]{3,20}$/', $username)) {
            throw new InvalidArgumentException('Username must be 3-20 letters, digits or underscores.');
        }
        if (strlen($password) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }
        if ($this->findByUsername($username) !== null) {
            throw new InvalidArgumentException('That username is taken.');
        }
        $stmt = $this->pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
        $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT)]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return array<string,mixed>|null */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ?');
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    /** Returns the user id on success, null on failure. */
    public function verify(string $username, string $password): ?int
    {
        $user = $this->findByUsername(trim($username));
        if ($user === null || !password_verify($password, $user['password_hash'])) {
            return null;
        }
        return (int) $user['id'];
    }
}
