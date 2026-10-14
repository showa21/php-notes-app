<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title ?? 'Notes') ?> · PHP Notes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header>
  <a href="/" class="brand">PHP Notes</a>
  <?php if (!empty($_SESSION['user_id'])): ?>
    <form method="post" action="/?page=logout">
      <input type="hidden" name="csrf" value="<?= e(App\Csrf::token()) ?>">
      <button class="link">Log out</button>
    </form>
  <?php endif; ?>
</header>
<main>
  <?php if (!empty($error)): ?><p class="error" role="alert"><?= e($error) ?></p><?php endif; ?>
  <?php require __DIR__ . '/' . $template . '.php'; ?>
</main>
</body>
</html>
