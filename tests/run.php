<?php
declare(strict_types=1);

// Tiny dependency-free test runner:  php tests/run.php
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/UserRepository.php';
require __DIR__ . '/../src/NoteRepository.php';
require __DIR__ . '/../src/Csrf.php';
require __DIR__ . '/../src/helpers.php';

use App\{Csrf, Database, NoteRepository, UserRepository};

$failures = 0;
function check(string $name, bool $ok): void
{
    global $failures;
    echo ($ok ? '  ok   ' : '  FAIL ') . $name . PHP_EOL;
    if (!$ok) $failures++;
}
function throwsInvalid(callable $fn): bool
{
    try { $fn(); } catch (InvalidArgumentException) { return true; }
    return false;
}

$pdo = Database::connect(':memory:');
$users = new UserRepository($pdo);
$notes = new NoteRepository($pdo);

$alice = $users->register('alice', 'correct-horse');
$bob = $users->register('bob_99', 'battery-staple');

check('password is stored hashed', $users->findByUsername('alice')['password_hash'] !== 'correct-horse');
check('login succeeds with right password', $users->verify('alice', 'correct-horse') === $alice);
check('login fails with wrong password', $users->verify('alice', 'nope') === null);
check('usernames are unique (case-insensitive)', throwsInvalid(fn() => $users->register('ALICE', 'password123')));
check('short passwords rejected', throwsInvalid(fn() => $users->register('carol', 'short')));
check('bad usernames rejected', throwsInvalid(fn() => $users->register("x'; DROP", 'password123')));

$id = $notes->create($alice, 'Shopping', 'milk, eggs');
check('note can be created and found', $notes->find($alice, $id)['title'] === 'Shopping');
check('other users cannot read it', $notes->find($bob, $id) === null);
check('other users cannot edit it', $notes->update($bob, $id, 'Hacked', '') === false);
check('other users cannot delete it', $notes->delete($bob, $id) === false);
check('owner can update', $notes->update($alice, $id, 'Groceries', 'milk') && $notes->find($alice, $id)['title'] === 'Groceries');
check('search finds notes', count($notes->allForUser($alice, 'milk')) === 1 && count($notes->allForUser($alice, 'zzz')) === 0);
check('SQL injection in search is harmless', $notes->allForUser($alice, "' OR 1=1 --") === []);
check('empty title rejected', throwsInvalid(fn() => $notes->create($alice, '   ', 'x')));
check('owner can delete', $notes->delete($alice, $id) && $notes->find($alice, $id) === null);

check('e() escapes HTML', e('<script>"x"</script>') === '&lt;script&gt;&quot;x&quot;&lt;/script&gt;');

$_SESSION = [];
$token = Csrf::token();
check('CSRF accepts the right token', Csrf::check($token));
check('CSRF rejects wrong/missing token', !Csrf::check('bad') && !Csrf::check(null));

echo PHP_EOL . ($failures === 0 ? 'All tests passed.' : "$failures test(s) failed.") . PHP_EOL;
exit($failures === 0 ? 0 : 1);
