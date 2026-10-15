<?php
if (current_user() && auth_refresh()) redirect(home_for_role(current_user()['role']));

$adminLogin = request_path() === 'admin/login'; // the Admin sign-in URL only accepts administrator accounts

$errors = [];
$email = '';
$next = (string)($_GET['next'] ?? $_POST['next'] ?? '');

// Where to go after login: only on-site paths inside the area that matches the role.
function safe_next(string $role): string
{
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
        } elseif ($adminLogin && $u['role'] !== 'admin') {
            $errors['email'] = 'Invalid email or password.'; // same message: do not reveal that the account exists
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

<h1 class="text-center mb-1"><?= $adminLogin ? 'Administrator sign in' : 'Welcome back' ?></h1>
<p class="text-secondary text-center mb-4"><?= $adminLogin ? 'Sign in with your AASRA administrator account.' : 'Log in to your AASRA account.' ?></p>

<form method="post" id="loginForm" class="needs-validation" novalidate>
    <?= csrf_field() ?>

    <input
        type="hidden"
        name="next"
        value="<?= e($next) ?>">

    <div class="mb-3">
        <label class="form-label" for="email">Email</label>

        <input
            id="email"
            name="email"
            type="email"
            class="form-control form-control-lg<?= !empty($errors['email']) ? ' is-invalid' : '' ?>"
            value="<?= e($email) ?>"
            autocomplete="email"
            required
            autofocus>

        <div class="invalid-feedback" data-default="Please enter a valid email address."><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
    </div>

    <div class="mb-4">
        <label class="form-label" for="password">Password</label>

        <div class="pw-wrap">
            <input
                id="password"
                name="password"
                type="password"
                class="form-control form-control-lg<?= !empty($errors['password']) ? ' is-invalid' : '' ?>"
                autocomplete="current-password"
                required>

            <button
                type="button"
                class="pw-toggle"
                aria-label="Show password">
                <i class="bi bi-eye"></i>
            </button>
        </div>

        <div class="invalid-feedback" data-default="Please enter your password."><?= e($errors['password'] ?? 'Please enter your password.') ?></div>
    </div>

    <button type="submit" class="btn btn-success btn-lg w-100">
        Log in
    </button>
</form>

<?php if (!$adminLogin): ?><p class="mt-4 mb-0 text-center">
    New to AASRA? <a href="<?= e(url('register')) ?>">Create an account</a>
</p><?php endif; ?>

<?php layout_end();
