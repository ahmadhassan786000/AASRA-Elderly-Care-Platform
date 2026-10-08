<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php'; require_once APP_ROOT . '/includes/provider_helpers.php';
$id = (int)get('id'); $p = row('SELECT * FROM providers WHERE id=?', [$id]); if (!$p) { show_error_page(404); exit; }
$svcs = rows('SELECT s.service_name FROM services s JOIN provider_services ps ON ps.service_id=s.id WHERE ps.provider_id=?', [$id]);
$st = row("SELECT COUNT(*) total, SUM(status='Completed') done FROM bookings WHERE provider_id=?", [$id]);
$rt = row('SELECT COALESCE(AVG(rating),0) a,COUNT(*) c FROM ratings WHERE provider_id=?', [$id]);
$c = provider_completeness($p);
$ks = 'provider_status:' . $id; $kv = 'provider_verification:' . $id;
layout('admin', ['title' => $p['name'], 'active' => 'providers']);
page_head($p['name'], 'Provider details', '<div class="d-flex gap-2 flex-wrap"><a class="btn btn-outline-secondary" href="' . e(url('admin/providers')) . '">Back</a><a class="btn btn-outline-success" href="' . e(url('admin/verification/' . $id)) . '">Review verification</a><a class="btn btn-success" href="' . e(url('admin/providers/' . $id . '/edit')) . '">Edit</a></div>');
?>
<div class="row g-4"><div class="col-lg-4"><div class="card-soft text-center"><?= avatar($p, 'avatar-xl') ?><h2 class="h5 mt-3 mb-1"><?= e($p['name']) ?></h2><div class="text-secondary mb-3"><?= e($p['email']) ?></div>
  <div class="d-flex justify-content-center gap-2 mb-3"><?= live_badge($kv, $p['verification_status']) ?> <?= live_badge($ks, $p['account_status']) ?></div>
  <div class="d-grid gap-2"><a href="#" class="btn btn-success <?= $p['account_status'] === 'Active' ? 'd-none' : '' ?>" data-ajax-action="provider_status" data-id="<?= $id ?>" data-value="Active" data-live-when="<?= e($ks) ?>" data-when="Inactive">Activate provider</a>
  <a href="#" class="btn btn-outline-danger <?= $p['account_status'] === 'Inactive' ? 'd-none' : '' ?>" data-ajax-action="provider_status" data-id="<?= $id ?>" data-value="Inactive" data-live-when="<?= e($ks) ?>" data-when="Active" data-confirm="Deactivate this provider? They will be hidden and unable to log in.">Deactivate provider</a></div></div></div>
<div class="col-lg-8"><div class="card-soft"><div class="row g-3">
  <div class="col-md-6"><div class="small text-secondary">Phone</div><?= e($p['phone'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Location</div><?= e($p['location'] ?: '—') ?></div>
  <div class="col-md-6"><div class="small text-secondary">Experience</div><?= e($p['experience'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Charges</div><?= $p['charges'] !== null ? e(money($p['charges'])) : '—' ?></div>
  <div class="col-md-6"><div class="small text-secondary">Availability</div><?= e($p['availability'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Rating</div><?= $rt['c'] ? number_format((float)$rt['a'], 1) . ' (' . (int)$rt['c'] . ')' : '—' ?></div>
  <div class="col-12"><div class="small text-secondary">Skills</div><?= e($p['skills'] ?: '—') ?></div><div class="col-12"><div class="small text-secondary">Bio</div><?= e($p['bio'] ?: '—') ?></div>
  <div class="col-12"><div class="small text-secondary mb-1">Services</div><?php foreach ($svcs as $s): ?><span class="tag me-1"><?= e($s['service_name']) ?></span><?php endforeach; ?><?php if (!$svcs) echo '—'; ?></div>
  <div class="col-md-6"><div class="small text-secondary">Bookings</div><?= (int)$st['total'] ?> total, <?= (int)$st['done'] ?> completed</div><div class="col-md-6"><div class="small text-secondary">Profile completeness</div><?= $c['pct'] ?>%</div>
</div></div></div></div>
<?php layout_end();
