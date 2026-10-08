<?php
if (current_user() && auth_refresh()) redirect(home_for_role(current_user()['role']));

$errors = [];
$email = '';
$next = (string)($_GET['next'] ?? $_POST['next'] ?? '');

// Where to go after login: only on-site paths inside the area that matches the role.
function safe_next(string $role): string {
    $n = (string)($_GET['next'] ?? $_POST['next'] ?? '');

    if ($n === '' || $n[0] !== '/' || str_starts_with($n, '//') || str_contains($n, '\\')) {
        return '';
    }

    $rel = BASE_URL !== '' && str_starts_with($n, BASE_URL)
        ? substr($n, strlen(BASE_URL))
        : $n;

    $rel = ltrim($rel, '/');
    $first = explode('/', explode('?', $rel)[0])[0];

    if (in_array($first, ['user', 'provider', 'admin'], true) && $first !== $role) {
        return '';
    }

    return $rel;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    $email = post('email');
    $pass = (string)($_POST['password'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($pass === '') {
        $errors['password'] = 'Please enter your password.';
    }

    if (!$errors) {
        $u = row(
            'SELECT id,name,email,password,role,status FROM users WHERE email=? LIMIT 1',
            [$email]
        );

        $ok = password_verify(
            $pass,
            $u['password'] ?? '$2y$10$usesomesillystringforsaltuseless.abcdefghijklmnopqrstuv'
        );

        if (!$u || !$ok) {
            $errors['email'] = 'Invalid email or password.';
        } elseif ($u['status'] !== 'Active') {
            $errors['email'] = 'This account is inactive. Please contact AASRA support.';
        } else {
            login_user($u);

            $next = safe_next($u['role']);

            redirect(
                $next !== ''
                    ? $next
                    : home_for_role($u['role'])
            );
        }
    }
}

layout('auth', ['title' => 'Login', 'width' => 440]);
?>

<h1 class="text-center mb-1">Welcome back</h1>
<p class="text-secondary text-center mb-4">Log in to your AASRA account.</p>

<form method="post" id="loginForm" novalidate>
  <?= csrf_field() ?>

  <input
    type="hidden"
    name="next"
    value="<?= e($next) ?>"
  >

  <div class="mb-3">
    <label class="form-label" for="email">Email</label>

    <input
      id="email"
      name="email"
      type="email"
      class="form-control form-control-lg"
      value="<?= e($email) ?>"
      autocomplete="email"
      required
      autofocus
    >

    <?php if (!empty($errors['email'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['email']) ?></div>
    <?php endif; ?>
  </div>

  <div class="mb-4">
    <label class="form-label" for="password">Password</label>

    <div class="pw-wrap">
      <input
        id="password"
        name="password"
        type="password"
        class="form-control form-control-lg"
        autocomplete="current-password"
        required
      >

      <button
        type="button"
        class="pw-toggle"
        aria-label="Show password"
      >
        <i class="bi bi-eye"></i>
      </button>
    </div>

    <?php if (!empty($errors['password'])): ?>
      <div class="text-danger small mt-1"><?= e($errors['password']) ?></div>
    <?php endif; ?>
  </div>

  <button type="submit" class="btn btn-success btn-lg w-100">
    Log in
  </button>
</form>

<p class="mt-4 mb-0 text-center">
  New to AASRA? <a href="<?= e(url('register')) ?>">Create an account</a>
</p>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('loginForm');

    if (!form) return;

    const email = document.getElementById('email');
    const password = document.getElementById('password');

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

    function validateEmail() {
        const value = email.value.trim();

        if (!value || !email.validity.valid) {
            showClientError(email, 'Please enter a valid email address.');
            return false;
        }

        removeClientError(email);
        return true;
    }

    function validatePassword() {
        if (password.value === '') {
            showClientError(password, 'Please enter your password.');
            return false;
        }

        removeClientError(password);
        return true;
    }

    email.addEventListener('blur', validateEmail);
    password.addEventListener('blur', validatePassword);

    email.addEventListener('input', function () {
        if (email.classList.contains('is-invalid')) {
            validateEmail();
        }
    });

    password.addEventListener('input', function () {
        if (password.classList.contains('is-invalid')) {
            validatePassword();
        }
    });

    form.addEventListener('submit', function (event) {
        const validEmail = validateEmail();
        const validPassword = validatePassword();

        if (!validEmail || !validPassword) {
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
