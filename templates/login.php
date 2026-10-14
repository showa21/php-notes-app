<h1>Log in</h1>
<form method="post" action="/?page=login">
  <input type="hidden" name="csrf" value="<?= e(App\Csrf::token()) ?>">
  <label>Username <input name="username" required autofocus></label>
  <label>Password <input type="password" name="password" required></label>
  <button>Log in</button>
</form>
<p>No account? <a href="/?page=register">Register</a></p>
