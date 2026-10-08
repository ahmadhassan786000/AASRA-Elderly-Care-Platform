<?php
$u = require_role('user'); require_once APP_ROOT . '/includes/password_form.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (post('form') === 'password') { $err = handle_password_change($u['id']); $err ? flash('danger', $err) : flash('success', 'Password changed.'); }
    else {
        $name = post('name'); $phone = post('phone'); $addr = post('address');
        if (mb_strlen($name) < 2 || mb_strlen($name) > 100) flash('danger', 'Please enter a valid name.');
        elseif ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) flash('danger', 'Please enter a valid phone number.');
        else { q('UPDATE users SET name=?,phone=?,address=? WHERE id=?', [$name, $phone ?: null, mb_substr($addr, 0, 255) ?: null, $u['id']]); flash('success', 'Profile updated.'); }
    }
    redirect('user/profile');
}
$me = row('SELECT * FROM users WHERE id=?', [$u['id']]);
layout('user', ['title' => 'My Profile', 'nav' => 'profile']);
?>
<div class="container py-4"><div class="row justify-content-center"><div class="col-lg-8">
  <h1 class="mb-4">My profile</h1>
  <div class="card-soft mb-4"><form method="post" class="row g-3"><?= csrf_field() ?>
    <div class="col-md-6"><label class="form-label" for="name">Full name</label><input id="name" name="name" class="form-control" value="<?= e($me['name']) ?>" required maxlength="100"></div>
    <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" class="form-control" value="<?= e($me['email']) ?>" disabled></div>
    <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" class="form-control" value="<?= e($me['phone']) ?>" maxlength="20"></div>
    <div class="col-12"><label class="form-label" for="address">Address / area</label><textarea id="address" name="address" class="form-control" rows="2" maxlength="255"><?= e($me['address']) ?></textarea><div class="form-text">Used to recommend providers near you.</div></div>
    <div class="col-12"><button class="btn btn-success">Save changes</button></div></form></div>
  <div class="card-soft"><h2 class="h5 mb-3">Change password</h2><?= password_form_html() ?></div>
</div></div></div>
<?php layout_end();
