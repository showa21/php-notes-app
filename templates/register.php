<h1>Create account</h1>
<form method="post" action="/?page=register">
  <input type="hidden" name="csrf" value="<?= e(App\Csrf::token()) ?>">
  <label>Username <input name="username" required pattern="[A-Za-z0-9_]{3,20}"></label>
  <label>Password (8+ characters) <input type="password" name="password" required minlength="8"></label>
  <button>Register</button>
</form>
<p>Already registered? <a href="/?page=login">Log in</a></p>
