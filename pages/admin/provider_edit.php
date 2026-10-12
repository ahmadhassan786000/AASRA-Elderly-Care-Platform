<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php'; require_once APP_ROOT . '/includes/provider_helpers.php';
$id = (int)get('id'); $p = row('SELECT * FROM providers WHERE id=?', [$id]); if (!$p) { show_error_page(404); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $name = post('name'); $phone = post('phone'); $loc = post('location'); $bio = post('bio'); $sk = post('skills'); $st = post('account_status');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $err = 'Please enter a valid name.';
    elseif ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $err = 'Please enter a valid phone number.';
    elseif (!in_array($st, ['Active', 'Inactive'])) $err = 'Invalid status.';
    else {
        q('UPDATE providers SET name=?,phone=?,location=?,bio=?,skills=?,account_status=? WHERE id=?', [$name, $phone ?: null, $loc ?: null, $bio ?: null, $sk ?: null, $st, $id]);
        q('UPDATE users SET name=?,phone=?,status=? WHERE id=?', [$name, $phone ?: null, $st, $p['user_id']]);
        flash('success', 'Provider updated successfully.'); redirect('admin/providers');
    }
    $p = array_merge($p, ['name' => $name, 'phone' => $phone, 'location' => $loc, 'bio' => $bio, 'skills' => $sk, 'account_status' => $st]);
}
layout('admin', ['title' => 'Edit provider', 'active' => 'providers']);
page_head('Edit provider', $p['email']);
?>
<div class="card-soft col-xl-9"><?php if ($err) echo alert_html('danger', $err); ?><form method="post" class="row g-3"><?= csrf_field() ?>
  <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control" value="<?= e($p['name']) ?>" required maxlength="100"></div>
  <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" class="form-control" value="<?= e($p['phone']) ?>" maxlength="20"></div>
  <div class="col-md-6"><label class="form-label" for="location">Location</label><input id="location" name="location" class="form-control" value="<?= e($p['location']) ?>" maxlength="190"></div>
  <div class="col-md-6"><label class="form-label" for="account_status">Account status</label><select id="account_status" name="account_status" class="form-select"><?php foreach (['Active', 'Inactive'] as $s): ?><option <?= $p['account_status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label" for="skills">Skills</label><input id="skills" name="skills" class="form-control" value="<?= e($p['skills']) ?>" maxlength="500"></div>
  <div class="col-12"><label class="form-label" for="bio">Bio</label><textarea id="bio" name="bio" class="form-control" rows="3" maxlength="2000"><?= e($p['bio']) ?></textarea></div>
  <div class="col-12 d-flex gap-2"><button class="btn btn-success">Save changes</button><a class="btn btn-outline-secondary" href="<?= e(url('admin/providers')) ?>">Cancel</a></div></form></div>
<?php layout_end();
