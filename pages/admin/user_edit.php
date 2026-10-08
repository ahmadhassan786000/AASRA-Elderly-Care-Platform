<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$id = (int)get('id'); $x = row('SELECT * FROM users WHERE id=?', [$id]); if (!$x) { show_error_page(404); exit; }
if ($x['role'] === 'admin') { flash('warning', 'Administrator accounts cannot be edited here.'); redirect('admin/users'); }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $name = post('name'); $email = post('email'); $phone = post('phone'); $addr = post('address'); $st = post('status'); $pw = (string)($_POST['password'] ?? '');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $err = 'Please enter a valid name.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $err = 'Please enter a valid email.';
    elseif (val('SELECT COUNT(*) FROM users WHERE email=? AND id<>?', [$email, $id])) $err = 'That email is already used by another account.';
    elseif ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $err = 'Please enter a valid phone number.';
    elseif (!in_array($st, ['Active', 'Inactive'])) $err = 'Invalid status.';
    elseif ($pw !== '' && (strlen($pw) < 8 || strlen($pw) > 72)) $err = 'New password must be 8 to 72 characters.';
    else {
        q('UPDATE users SET name=?,email=?,phone=?,address=?,status=? WHERE id=?', [$name, $email, $phone ?: null, mb_substr($addr, 0, 255) ?: null, $st, $id]);
        if ($pw !== '') q('UPDATE users SET password=? WHERE id=?', [password_hash($pw, PASSWORD_DEFAULT), $id]);
        if ($x['role'] === 'provider') q('UPDATE providers SET name=?,email=?,phone=?,account_status=? WHERE user_id=?', [$name, $email, $phone ?: null, $st, $id]);
        flash('success', 'User updated successfully.'); redirect('admin/users');
    }
    $x = array_merge($x, ['name' => $name, 'email' => $email, 'phone' => $phone, 'address' => $addr, 'status' => $st]);
}
layout('admin', ['title' => 'Edit user', 'active' => 'users']);
page_head('Edit user', $x['email']);
?>
<div class="card-soft col-xl-8"><?php if ($err) echo alert_html('danger', $err); ?><form method="post" class="row g-3" data-validate novalidate><?= csrf_field() ?>
  <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control" value="<?= e($x['name']) ?>" required maxlength="100"></div>
  <div class="col-md-6"><label class="form-label" for="email">Email</label><input id="email" name="email" type="email" class="form-control" value="<?= e($x['email']) ?>" required></div>
  <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" class="form-control" value="<?= e($x['phone']) ?>" maxlength="20"></div>
  <div class="col-md-6"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><?php foreach (['Active', 'Inactive'] as $s): ?><option <?= $x['status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label" for="address">Address</label><input id="address" name="address" class="form-control" value="<?= e($x['address']) ?>" maxlength="255"></div>
  <div class="col-md-6"><label class="form-label" for="password">Set new password (optional)</label><input id="password" name="password" type="password" class="form-control" autocomplete="new-password" minlength="8"></div>
  <div class="col-12 d-flex gap-2"><button class="btn btn-success">Save changes</button><a class="btn btn-outline-secondary" href="<?= e(url('admin/users')) ?>">Cancel</a></div></form></div>
<?php layout_end();
