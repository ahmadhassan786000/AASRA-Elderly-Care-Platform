<?php
if (current_user() && auth_refresh()) redirect(home_for_role(current_user()['role']));

$errors = [];
$general_error = '';
$name = '';
$email = '';
$type = get('type') === 'provider' ? 'provider' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();

  $type = ($_POST['account_type'] ?? '') === 'provider' ? 'provider' : 'user';
  $name = post('name');
  $email = post('email');
  $pass = (string)($_POST['password'] ?? '');
  $confirm = (string)($_POST['confirm_password'] ?? '');

  if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors['name'] = 'Please enter your full name (2–100 characters).';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    $errors['email'] = 'Please enter a valid email address.';
  }

  if (strlen($pass) < 8 || strlen($pass) > 72) {
    $errors['password'] = 'Password must be 8 to 72 characters.';
  }

  if ($pass !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
  }

  if (!$errors && row('SELECT id FROM users WHERE email=?', [$email])) {
    $errors['email'] = 'This email is already registered. Try logging in instead.';
  }

  if (!$errors) {
    db_begin();

    try {
      $ins = q(
        'INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,?,?)',
        [$name, $email, password_hash($pass, PASSWORD_DEFAULT), $type, 'Active']
      );

      $uid = (int)mysqli_stmt_insert_id($ins);

      if ($type === 'provider') {
        q(
          "INSERT INTO providers(user_id,name,email,verification_status,account_status) VALUES(?,?,?,'Pending','Active')",
          [$uid, $name, $email]
        );
      }

      db_commit();

      flash(
        'success',
        $type === 'provider'
          ? 'Account created. Log in, then complete your Account Settings and verification.'
          : 'Account created. Please log in.'
      );

      redirect('login');
    } catch (mysqli_sql_exception $e) {
      db_rollback();
      error_log($e);
      $general_error = 'Registration failed. Please try again.';
    }
  }
}

layout('auth', ['title' => 'Create account', 'width' => 500]);
?>

<h1 class="text-center mb-1">Create your account</h1>
<p class="text-secondary text-center mb-4">It only takes a minute.</p>

<?php if ($general_error): ?>
  <?= alert_html('danger', $general_error) ?>
<?php endif; ?>

<form method="post" id="registerForm" class="needs-validation" novalidate>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label class="form-label" for="account_type">Account type</label>
    <select id="account_type" name="account_type" class="form-select<?= !empty($errors['account_type']) ? ' is-invalid' : '' ?>" required>
      <option value="user" <?= $type === 'user' ? 'selected' : '' ?>>User</option>
      <option value="provider" <?= $type === 'provider' ? 'selected' : '' ?>>Provider</option>
    </select>
    <div class="invalid-feedback" data-default="Please choose an account type."><?= e($errors['account_type'] ?? 'Please choose an account type.') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="name">Full name</label>
    <input
      id="name"
      name="name"
      class="form-control<?= !empty($errors['name']) ? ' is-invalid' : '' ?>"
      value="<?= e($name) ?>"
      minlength="2"
      maxlength="100"
      autocomplete="name"
      required>
    <div class="invalid-feedback" data-default="Please enter your full name (2–100 characters)."><?= e($errors['name'] ?? 'Please enter your full name (2–100 characters).') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input
      id="email"
      name="email"
      type="email"
      class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>"
      value="<?= e($email) ?>"
      maxlength="190"
      autocomplete="email"
      required>
    <div class="invalid-feedback" data-default="Please enter a valid email address."><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="pw-wrap">
      <input
        id="password"
        name="password"
        type="password"
        minlength="8"
        maxlength="72"
        class="form-control<?= !empty($errors['password']) ? ' is-invalid' : '' ?>"
        autocomplete="new-password"
        required>
      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    <div class="invalid-feedback" data-default="Password must be 8 to 72 characters."><?= e($errors['password'] ?? 'Password must be 8 to 72 characters.') ?></div>
    <div class="form-text">At least 8 characters.</div>
  </div>

  <div class="mb-4">
    <label class="form-label" for="confirm_password">Confirm password</label>
    <div class="pw-wrap">
      <input
        id="confirm_password"
        name="confirm_password"
        type="password"
        minlength="8"
        maxlength="72"
        class="form-control<?= !empty($errors['confirm_password']) ? ' is-invalid' : '' ?>"
        data-match="#password"
        autocomplete="new-password"
        required>
      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    <div class="invalid-feedback" data-default="The two passwords must match."><?= e($errors['confirm_password'] ?? 'The two passwords must match.') ?></div>
  </div>

  <button type="submit" class="btn btn-success btn-lg w-100">
    Create account
  </button>
</form>

<p class="mt-4 mb-0 text-center">
  Already have an account? <a href="<?= e(url('login')) ?>">Log in</a>
</p>

<?php layout_end();
