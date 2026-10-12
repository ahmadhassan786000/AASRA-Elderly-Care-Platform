<?php
$u = require_role('provider'); require_once APP_ROOT . '/includes/provider_helpers.php';
$p = current_provider(); $pid = (int)$p['id'];
$svcs = rows('SELECT s.* FROM services s JOIN provider_services ps ON ps.service_id=s.id WHERE ps.provider_id=? ORDER BY s.service_name', [$pid]);
$rt = row('SELECT COALESCE(AVG(rating),0) a,COUNT(*) c FROM ratings WHERE provider_id=?', [$pid]);
$offs = provider_offerings($pid);
layout('provider', ['title' => 'Profile', 'active' => 'profile']);
?>
<div class="page-head"><div><h1>My profile</h1><p>This is how your profile appears to users.</p></div><a class="btn btn-success" href="<?= e(url('provider/settings')) ?>"><i class="bi bi-pencil"></i> Edit in Account Settings</a></div>
<div class="card-soft">
  <div class="d-flex gap-4 align-items-center flex-wrap"><?= avatar($p, 'avatar-xl') ?><div>
    <h2 class="h3 mb-1"><?= e($p['name']) ?> <?= badge($p['verification_status']) ?></h2>
    <div><?= stars((float)$rt['a']) ?> <span class="text-secondary"><?= $rt['c'] ? number_format((float)$rt['a'], 1) . ' (' . (int)$rt['c'] . ' reviews)' : 'No reviews yet' ?></span></div>
    <div class="text-secondary"><i class="bi bi-geo-alt"></i> <?= e($p['location'] ?: 'Add your location') ?> · <i class="bi bi-telephone"></i> <?= e($p['phone'] ?: 'Add your phone') ?></div></div></div>
  <hr><div class="row g-4"><div class="col-md-7"><h3 class="h6">About</h3><p><?= $p['bio'] ? nl2br(e($p['bio'])) : '<span class="text-secondary">Add a short bio in Account Settings.</span>' ?></p><h3 class="h6">Skills</h3><p><?= e($p['skills'] ?: '—') ?></p><h3 class="h6">Services</h3><p><?php foreach ($svcs as $s): ?><span class="tag me-1"><?= e($s['service_name']) ?></span><?php endforeach; ?><?php if (!$svcs): ?><span class="text-secondary">No services selected.</span><?php endif; ?></p></div>
  <div class="col-md-5"><h3 class="h6">Offerings</h3>
    <?php foreach ($offs as $o): ?><div class="mb-2"><b><?= e($o['title']) ?></b> <span class="tag ms-1"><?= e($o['service_name']) ?></span><?php if ($o['price'] !== null && (float)$o['price'] > 0): ?><div class="small text-secondary"><?= e(money($o['price'])) ?> / visit</div><?php endif; ?></div><?php endforeach; ?>
    <?php if (!$offs): ?><p class="text-secondary mb-2">No offerings yet.</p><?php endif; ?>
    <a class="btn btn-outline-success btn-sm" href="<?= e(url('provider/services')) ?>">Manage services and offerings</a></div></div>
</div>
<?php layout_end();
