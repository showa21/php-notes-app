<h1>My notes</h1>
<form method="get" class="search">
  <input name="q" value="<?= e($search) ?>" placeholder="Search notes…" aria-label="Search notes">
  <button>Search</button>
</form>

<form method="post" class="editor">
  <input type="hidden" name="csrf" value="<?= e(App\Csrf::token()) ?>">
  <input type="hidden" name="action" value="<?= $editing ? 'update' : 'create' ?>">
  <?php if ($editing): ?><input type="hidden" name="id" value="<?= (int) $editing['id'] ?>"><?php endif; ?>
  <input name="title" placeholder="Title" maxlength="100" required value="<?= e($editing['title'] ?? '') ?>">
  <textarea name="body" rows="4" placeholder="Write something…"><?= e($editing['body'] ?? '') ?></textarea>
  <button><?= $editing ? 'Save changes' : 'Add note' ?></button>
  <?php if ($editing): ?><a href="/">Cancel</a><?php endif; ?>
</form>

<?php if (!$items): ?><p class="muted">No notes yet.</p><?php endif; ?>
<?php foreach ($items as $n): ?>
  <article class="note">
    <h2><?= e($n['title']) ?></h2>
    <p><?= nl2br(e($n['body'])) ?></p>
    <small class="muted"><?= e($n['updated_at']) ?></small>
    <div class="actions">
      <a href="/?edit=<?= (int) $n['id'] ?>">Edit</a>
      <form method="post" onsubmit="return confirm('Delete this note?')">
        <input type="hidden" name="csrf" value="<?= e(App\Csrf::token()) ?>">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="id" value="<?= (int) $n['id'] ?>">
        <button class="link danger">Delete</button>
      </form>
    </div>
  </article>
<?php endforeach; ?>
