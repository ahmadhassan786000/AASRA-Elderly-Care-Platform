<?php require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
$role = ($_GET['role'] ?? '') === 'provider' ? 'provider' : 'user';
$title = 'Register';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $role = $_POST['role'] === 'provider' ? 'provider' : 'user';
    $name = post('name');
    $email = post('email');
    $phone = post('phone');
    $address = post('address');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 8 || $pass !== $confirm) $error = 'Please provide valid details. Password must be at least 8 characters and match confirmation.';
    else {
        $s = $pdo->prepare('SELECT id FROM users WHERE email=?');
        $s->execute([$email]);
        if ($s->fetch()) $error = 'This email is already registered.';
        else {
            $pdo->beginTransaction();
            try {
                $h = password_hash($pass, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO users(name,email,password,phone,address,role,status) VALUES(?,?,?,?,?,?,?)')->execute([$name, $email, $h, $phone, $address, 'user', 'Active']);
                $uid = $pdo->lastInsertId();
                if ($role === 'provider') {
                    $pdo->prepare('INSERT INTO providers(user_id,name,email,phone,address,bio,experience,availability,charges,verification_status,account_status) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$uid, $name, $email, $phone, $address, post('bio'), post('experience'), post('availability'), post('charges'), 'Pending', 'Active']);
                }
                $pdo->commit();
                flash('success', 'Registration successful. Please login.');
                redirect('/aasra/login.php');
            } catch (Throwable $e) {
                $pdo->rollBack();
                $error = 'Registration failed. Please try again.';
            }
        }
    }
}
include __DIR__ . '/includes/header.php'; ?><div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="service-card p-4">
                <h2>Create your AASRA account</h2>
                <p class="text-secondary">Choose whether you are looking for assistance or joining as a provider.</p><?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?><form method="post" data-validate class="row g-3 needs-validation" novalidate><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="role" value="<?= e($role) ?>">
                    <div class="col-md-6"><label class="form-label">Account type</label><select class="form-select" onchange="location.href='/aasra/register.php?role='+this.value">
                            <option value="user" <?= $role === 'user' ? 'selected' : '' ?>>User / Caregiver</option>
                            <option value="provider" <?= $role === 'provider' ? 'selected' : '' ?>>Service Provider</option>
                        </select></div>
                    <div class="col-md-6"><label class="form-label">Full Name</label><input name="name" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Email</label><input name="email" type="email" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Phone</label><input name="phone" class="form-control" required></div>
                    <div class="col-12"><label class="form-label">Address</label><input name="address" class="form-control" required></div><?php if ($role === 'provider'): ?><div class="col-md-6"><label class="form-label">Experience</label><input name="experience" class="form-control" placeholder="e.g. 5 years"></div>
                        <div class="col-md-6"><label class="form-label">Charges (PKR)</label><input name="charges" type="number" min="0" step="0.01" class="form-control" required></div>
                        <div class="col-md-6"><label class="form-label">Availability</label><input name="availability" class="form-control" placeholder="e.g. 9 AM - 5 PM"></div>
                        <div class="col-12"><label class="form-label">Bio</label><textarea name="bio" class="form-control" rows="3"></textarea></div><?php endif; ?><div class="col-md-6"><label class="form-label">Password</label><input name="password" type="password" minlength="8" class="form-control" required></div>
                    <div class="col-md-6"><label class="form-label">Confirm Password</label><input name="confirm_password" type="password" minlength="8" class="form-control" required></div>
                    <div class="col-12"><button class="btn btn-success btn-lg">Create Account</button></div>
                </form>
            </div>
        </div>
    </div>
</div><?php include __DIR__ . '/includes/footer.php'; ?>