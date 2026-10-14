<?php
declare(strict_types=1);

namespace App;

/** Synchroniser-token CSRF protection stored in the session. */
final class Csrf
{
    public static function token(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    }

    public static function check(?string $submitted): bool
    {
        return is_string($submitted)
            && isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $submitted);
    }
}
