<?php
declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use PDO;

final class NoteRepository
{
    public function __construct(private PDO $pdo) {}

    public function create(int $userId, string $title, string $body): int
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 100) {
            throw new InvalidArgumentException('Title is required (max 100 characters).');
        }
        $stmt = $this->pdo->prepare('INSERT INTO notes (user_id, title, body) VALUES (?, ?, ?)');
        $stmt->execute([$userId, $title, $body]);
        return (int) $this->pdo->lastInsertId();
    }

    /** @return list<array<string,mixed>> */
    public function allForUser(int $userId, string $search = ''): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notes WHERE user_id = ? AND (title LIKE ? OR body LIKE ?) ORDER BY updated_at DESC, id DESC'
        );
        $like = '%' . $search . '%';
        $stmt->execute([$userId, $like, $like]);
        return $stmt->fetchAll();
    }

    /** Ownership is always part of the query, so users can't touch each other's notes. */
    public function find(int $userId, int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notes WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->fetch() ?: null;
    }

    public function update(int $userId, int $id, string $title, string $body): bool
    {
        $title = trim($title);
        if ($title === '' || mb_strlen($title) > 100) {
            throw new InvalidArgumentException('Title is required (max 100 characters).');
        }
        $stmt = $this->pdo->prepare(
            'UPDATE notes SET title = ?, body = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?'
        );
        $stmt->execute([$title, $body, $id, $userId]);
        return $stmt->rowCount() === 1;
    }

    public function delete(int $userId, int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM notes WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
        return $stmt->rowCount() === 1;
    }
}
