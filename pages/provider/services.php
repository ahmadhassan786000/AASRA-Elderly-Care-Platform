<?php
$u = require_role('provider'); require_once APP_ROOT . '/includes/provider_helpers.php';
$p = current_provider(); $pid = (int)$p['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); save_provider_services($pid, (array)($_POST['services'] ?? [])); flash('success', 'Your services were updated.'); redirect('provider/services'); }
$all = active_services(); $mine = array_column(rows('SELECT service_id FROM provider_services WHERE provider_id=?', [$pid]), 'service_id');
layout('provider', ['title' => 'Services', 'active' => 'services']);
?>
<div class="page-head"><div><h1>My services</h1><p>Select the support you provide. Users find you by these.</p></div></div>
<form method="post"><?= csrf_field() ?><div class="row g-4"><?php foreach ($all as $s): $on = in_array($s['id'], $mine); ?>
  <div class="col-sm-6 col-xl-4"><label class="svc-card d-block" for="sv<?= (int)$s['id'] ?>" style="cursor:pointer"><?= service_thumb($s) ?><div class="svc-body d-flex gap-3 align-items-start"><input class="form-check-input mt-1" type="checkbox" name="services[]" value="<?= (int)$s['id'] ?>" id="sv<?= (int)$s['id'] ?>" <?= $on ? 'checked' : '' ?> style="width:1.4rem;height:1.4rem"><div><h3><?= e($s['service_name']) ?></h3><p class="small text-secondary mb-0"><?= e($s['description']) ?></p></div></div></label></div><?php endforeach; ?></div>
<button class="btn btn-success btn-lg mt-4">Save services</button></form>
<?php layout_end();
