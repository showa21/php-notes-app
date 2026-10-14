<?php
declare(strict_types=1);

// Front controller: every request enters here.
// Run locally with:  php -S localhost:8000 -t public
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/UserRepository.php';
require __DIR__ . '/../src/NoteRepository.php';
require __DIR__ . '/../src/Csrf.php';
require __DIR__ . '/../src/helpers.php';

use App\{Csrf, Database, NoteRepository, UserRepository};

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'use_strict_mode' => true,
]);

$pdo = Database::connect(__DIR__ . '/../data/app.sqlite');
$users = new UserRepository($pdo);
$notes = new NoteRepository($pdo);

$page = $_GET['page'] ?? 'notes';
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'] ?? null;
$error = null;

// Every state-changing request must carry a valid CSRF token.
if ($method === 'POST' && !Csrf::check($_POST['csrf'] ?? null)) {
    http_response_code(419);
    exit('Invalid or expired form token. Go back and try again.');
}

try {
    switch ($page) {
        case 'register':
            if ($method === 'POST') {
                $_SESSION['user_id'] = $users->register($_POST['username'] ?? '', $_POST['password'] ?? '');
                session_regenerate_id(true);
                redirect('/');
            }
            render('register', ['title' => 'Register']);
            break;

        case 'login':
            if ($method === 'POST') {
                $id = $users->verify($_POST['username'] ?? '', $_POST['password'] ?? '');
                if ($id === null) {
                    $error = 'Wrong username or password.';
                } else {
                    session_regenerate_id(true); // prevents session fixation
                    $_SESSION['user_id'] = $id;
                    redirect('/');
                }
            }
            render('login', ['title' => 'Log in', 'error' => $error]);
            break;

        case 'logout':
            if ($method === 'POST') {
                $_SESSION = [];
                session_destroy();
            }
            redirect('/?page=login');

        default: // notes list + create/edit/delete — requires login
            if ($userId === null) {
                redirect('/?page=login');
            }
            if ($method === 'POST') {
                $action = $_POST['action'] ?? '';
                $id = (int) ($_POST['id'] ?? 0);
                try {
                    match ($action) {
                        'create' => $notes->create($userId, $_POST['title'] ?? '', $_POST['body'] ?? ''),
                        'update' => $notes->update($userId, $id, $_POST['title'] ?? '', $_POST['body'] ?? ''),
                        'delete' => $notes->delete($userId, $id),
                        default => null,
                    };
                    redirect('/');
                } catch (InvalidArgumentException $e) {
                    $error = $e->getMessage();
                }
            }
            $editing = isset($_GET['edit']) ? $notes->find($userId, (int) $_GET['edit']) : null;
            $search = trim($_GET['q'] ?? '');
            render('notes', [
                'title' => 'My notes',
                'error' => $error,
                'editing' => $editing,
                'search' => $search,
                'items' => $notes->allForUser($userId, $search),
            ]);
    }
} catch (InvalidArgumentException $e) {
    render($page === 'register' ? 'register' : 'login', ['title' => 'Error', 'error' => $e->getMessage()]);
}
