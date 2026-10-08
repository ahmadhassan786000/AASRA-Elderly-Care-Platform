<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php'; require_once APP_ROOT . '/includes/provider_helpers.php';
$id = (int)get('id'); $p = row('SELECT * FROM providers WHERE id=?', [$id]); if (!$p) { show_error_page(404); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $name = post('name'); $phone = post('phone'); $loc = post('location'); $exp = post('experience'); $av = post('availability'); $bio = post('bio'); $sk = post('skills'); $chg = post('charges'); $st = post('account_status');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) $err = 'Please enter a valid name.';
    elseif ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $err = 'Please enter a valid phone number.';
    elseif ($chg !== '' && (!is_numeric($chg) || (float)$chg < 0)) $err = 'Charges must be a positive number.';
    elseif (!in_array($st, ['Active', 'Inactive'])) $err = 'Invalid status.';
    else {
        q('UPDATE providers SET name=?,phone=?,location=?,experience=?,availability=?,bio=?,skills=?,charges=?,account_status=? WHERE id=?', [$name, $phone ?: null, $loc ?: null, $exp ?: null, $av ?: null, $bio ?: null, $sk ?: null, $chg === '' ? null : (float)$chg, $st, $id]);
        q('UPDATE users SET name=?,phone=?,status=? WHERE id=?', [$name, $phone ?: null, $st, $p['user_id']]);
        save_provider_services($id, (array)($_POST['services'] ?? []));
        flash('success', 'Provider updated successfully.'); redirect('admin/providers');
    }
    $p = array_merge($p, ['name' => $name, 'phone' => $phone, 'location' => $loc, 'experience' => $exp, 'availability' => $av, 'bio' => $bio, 'skills' => $sk, 'charges' => $chg, 'account_status' => $st]);
}
$all = rows('SELECT * FROM services ORDER BY service_name'); $mine = array_column(rows('SELECT service_id FROM provider_services WHERE provider_id=?', [$id]), 'service_id');
layout('admin', ['title' => 'Edit provider', 'active' => 'providers']);
page_head('Edit provider', $p['email']);
?>
<div class="card-soft col-xl-9"><?php if ($err) echo alert_html('danger', $err); ?><form method="post" class="row g-3"><?= csrf_field() ?>
  <div class="col-md-6"><label class="form-label" for="name">Name</label><input id="name" name="name" class="form-control" value="<?= e($p['name']) ?>" required maxlength="100"></div>
  <div class="col-md-6"><label class="form-label" for="phone">Phone</label><input id="phone" name="phone" class="form-control" value="<?= e($p['phone']) ?>" maxlength="20"></div>
  <div class="col-md-6"><label class="form-label" for="location">Location</label><input id="location" name="location" class="form-control" value="<?= e($p['location']) ?>" maxlength="190"></div>
  <div class="col-md-6"><label class="form-label" for="account_status">Account status</label><select id="account_status" name="account_status" class="form-select"><?php foreach (['Active', 'Inactive'] as $s): ?><option <?= $p['account_status'] === $s ? 'selected' : '' ?>><?= $s ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label" for="experience">Experience</label><input id="experience" name="experience" class="form-control" value="<?= e($p['experience']) ?>" maxlength="120"></div>
  <div class="col-md-4"><label class="form-label" for="charges">Charges (PKR)</label><input id="charges" name="charges" type="number" min="0" class="form-control" value="<?= e($p['charges']) ?>"></div>
  <div class="col-md-4"><label class="form-label" for="availability">Availability</label><input id="availability" name="availability" class="form-control" value="<?= e($p['availability']) ?>" maxlength="255"></div>
  <div class="col-12"><label class="form-label" for="skills">Skills</label><input id="skills" name="skills" class="form-control" value="<?= e($p['skills']) ?>" maxlength="500"></div>
  <div class="col-12"><label class="form-label" for="bio">Bio</label><textarea id="bio" name="bio" class="form-control" rows="3" maxlength="2000"><?= e($p['bio']) ?></textarea></div>
  <div class="col-12"><span class="form-label d-block">Services</span><div class="row g-2"><?php foreach ($all as $s): ?><div class="col-sm-6 col-lg-4"><div class="form-check"><input class="form-check-input" type="checkbox" name="services[]" value="<?= (int)$s['id'] ?>" id="s<?= (int)$s['id'] ?>" <?= in_array($s['id'], $mine) ? 'checked' : '' ?>><label class="form-check-label" for="s<?= (int)$s['id'] ?>"><?= e($s['service_name']) ?></label></div></div><?php endforeach; ?></div></div>
  <div class="col-12 d-flex gap-2"><button class="btn btn-success">Save changes</button><a class="btn btn-outline-secondary" href="<?= e(url('admin/providers')) ?>">Cancel</a></div></form></div>
<?php layout_end();
