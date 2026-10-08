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
        $pdo = db();
        $pdo->beginTransaction();

        try {
            q(
                'INSERT INTO users(name,email,password,role,status) VALUES(?,?,?,?,?)',
                [$name, $email, password_hash($pass, PASSWORD_DEFAULT), $type, 'Active']
            );

            $uid = (int)$pdo->lastInsertId();

            if ($type === 'provider') {
                q(
                    "INSERT INTO providers(user_id,name,email,verification_status,account_status) VALUES(?,?,?,'Pending','Active')",
                    [$uid, $name, $email]
                );
            }

            $pdo->commit();

            flash(
                'success',
                $type === 'provider'
                    ? 'Account created. Log in, then complete your Account Settings and verification.'
                    : 'Account created. Please log in.'
            );

            redirect('login');
        } catch (PDOException $e) {
            $pdo->rollBack();
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

<form method="post" id="registerForm" novalidate>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label class="form-label" for="account_type">Account type</label>

    <select id="account_type" name="account_type" class="form-select" required>
      <option value="user" <?= $type === 'user' ? 'selected' : '' ?>>User</option>
      <option value="provider" <?= $type === 'provider' ? 'selected' : '' ?>>Provider</option>
    </select>

    <?php if (!empty($errors['account_type'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['account_type']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="name">Full name</label>

    <input
      id="name"
      name="name"
      class="form-control"
      value="<?= e($name) ?>"
      maxlength="100"
      autocomplete="name"
      required
    >

    <?php if (!empty($errors['name'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['name']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">Email</label>

    <input
      id="email"
      name="email"
      type="email"
      class="form-control"
      value="<?= e($email) ?>"
      maxlength="190"
      autocomplete="email"
      required
    >

    <?php if (!empty($errors['email'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['email']) ?></div>
    <?php endif; ?>
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
        class="form-control"
        autocomplete="new-password"
        required
      >

      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>

    <div class="form-text">At least 8 characters.</div>

    <?php if (!empty($errors['password'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['password']) ?></div>
    <?php endif; ?>
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
        class="form-control"
        autocomplete="new-password"
        required
      >

      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>

    <?php if (!empty($errors['confirm_password'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['confirm_password']) ?></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-success btn-lg w-100">
    Create account
  </button>
</form>

<p class="mt-4 mb-0 text-center">
  Already have an account? <a href="<?= e(url('login')) ?>">Log in</a>
</p>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('registerForm');

    if (!form) return;

    const name = document.getElementById('name');
    const email = document.getElementById('email');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    function removeClientError(input) {
        if (!input) return;

        input.classList.remove('is-invalid');

        const error = input.parentElement.querySelector('.client-field-error');

        if (error) {
            error.remove();
        }
    }

    function showClientError(input, message) {
        if (!input) return;

        removeClientError(input);

        input.classList.add('is-invalid');

        const error = document.createElement('div');
        error.className = 'client-field-error text-danger small mt-1';
        error.textContent = message;

        input.parentElement.appendChild(error);
    }

    function validateName() {
        const value = name.value.trim();

        if (value.length < 2 || value.length > 100) {
            showClientError(name, 'Please enter your full name (2–100 characters).');
            return false;
        }

        removeClientError(name);
        return true;
    }

    function validateEmail() {
        const value = email.value.trim();

        if (!value || !email.validity.valid || value.length > 190) {
            showClientError(email, 'Please enter a valid email address.');
            return false;
        }

        removeClientError(email);
        return true;
    }

    function validatePassword() {
        const value = password.value;

        if (value.length < 8 || value.length > 72) {
            showClientError(password, 'Password must be 8 to 72 characters.');
            return false;
        }

        removeClientError(password);
        return true;
    }

    function validateConfirmPassword() {
        const value = confirmPassword.value;

        if (value !== password.value || value.length < 8) {
            showClientError(confirmPassword, 'Passwords do not match.');
            return false;
        }

        removeClientError(confirmPassword);
        return true;
    }

    name.addEventListener('blur', validateName);
    email.addEventListener('blur', validateEmail);
    password.addEventListener('blur', validatePassword);
    confirmPassword.addEventListener('blur', validateConfirmPassword);

    name.addEventListener('input', function () {
        if (name.classList.contains('is-invalid')) validateName();
    });

    email.addEventListener('input', function () {
        if (email.classList.contains('is-invalid')) validateEmail();
    });

    password.addEventListener('input', function () {
        if (password.classList.contains('is-invalid')) validatePassword();

        if (confirmPassword.value) {
            validateConfirmPassword();
        }
    });

    confirmPassword.addEventListener('input', function () {
        if (confirmPassword.classList.contains('is-invalid')) {
            validateConfirmPassword();
        }
    });

    form.addEventListener('submit', function (event) {
        const validName = validateName();
        const validEmail = validateEmail();
        const validPassword = validatePassword();
        const validConfirmPassword = validateConfirmPassword();

        if (!validName || !validEmail || !validPassword || !validConfirmPassword) {
            event.preventDefault();

            const firstInvalid = form.querySelector('.is-invalid');

            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
</script>

<?php layout_end();
