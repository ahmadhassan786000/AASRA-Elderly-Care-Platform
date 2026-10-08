<?php require_once __DIR__ . '/_base.php';
$title = 'My Profile';
include __DIR__ . '/../includes/header.php'; ?><div class="dashboard-shell"><?php include __DIR__ . '/../includes/sidebar.php'; ?><section class="content-area"><?php $msg = '';
                                                                                                                                                                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                                                                                                                                                                    check_csrf();
                                                                                                                                                                    $pdo->prepare('UPDATE users SET name=?,phone=?,address=? WHERE id=?')->execute([post('name'), post('phone'), post('address'), $u['id']]);
                                                                                                                                                                    $_SESSION['user']['name'] = post('name');
                                                                                                                                                                    $msg = 'Profile updated successfully.';
                                                                                                                                                                } ?><div class="service-card p-4 col-lg-8">
            <h3>My Profile</h3><?php if ($msg): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <div class="mb-3"><label class="form-label">Name</label><input name="name" class="form-control" value="<?= e($u['name']) ?>"></div>
                <div class="mb-3"><label class="form-label">Email</label><input class="form-control" value="<?= e($u['email']) ?>" disabled></div>
                <div class="mb-3"><label class="form-label">Phone</label><input name="phone" class="form-control" value="<?= e($u['phone']) ?>"></div>
                <div class="mb-3"><label class="form-label">Address</label><textarea name="address" class="form-control"><?= e($u['address']) ?></textarea></div><button class="btn btn-success">Save Changes</button>
            </form>
        </div>
    </section>
</div><?php include __DIR__ . '/../includes/footer.php'; ?>