<?php
$u = require_role('user');
$services = active_services();
$f = ['q' => get('q'), 'service' => (int)get('service'), 'min' => get('min'), 'max' => get('max'), 'location' => get('location'), 'sort' => get('sort', 'rating')];
$list = listed_providers($f);
$favIds = array_column(rows('SELECT provider_id FROM favorites WHERE user_id=?', [$u['id']]), 'provider_id');
$cur = null; foreach ($services as $s) if ($s['id'] == $f['service']) $cur = $s;
layout('user', ['title' => $cur ? $cur['service_name'] : 'Find Providers', 'nav' => $cur ? 'services' : 'providers']);
?>
<div class="container py-4">
  <h1 class="mb-1"><?= $cur ? e($cur['service_name']) : 'Find providers' ?></h1>
  <p class="text-secondary mb-4"><?= $cur ? e($cur['description']) : 'Verified providers, ranked by rating.' ?></p>
  <form class="filter-bar"><div class="row g-2 align-items-end">
    <div class="col-md-3"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Name, skill or area" value="<?= e($f['q']) ?>"></div>
    <div class="col-md-3"><label for="service">Service</label><select id="service" name="service" class="form-select"><option value="0">All services</option><?php foreach ($services as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $f['service'] == $s['id'] ? 'selected' : '' ?>><?= e($s['service_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label for="location">Area</label><input id="location" name="location" class="form-control" value="<?= e($f['location']) ?>"></div>
    <div class="col-6 col-md-1"><label for="min">Min</label><input id="min" name="min" type="number" min="0" class="form-control" value="<?= e($f['min']) ?>"></div>
    <div class="col-6 col-md-1"><label for="max">Max</label><input id="max" name="max" type="number" min="0" class="form-control" value="<?= e($f['max']) ?>"></div>
    <div class="col-md-2 d-flex gap-2"><select name="sort" class="form-select" aria-label="Sort"><option value="rating">Top rated</option><option value="price_asc" <?= $f['sort'] === 'price_asc' ? 'selected' : '' ?>>Price ↑</option><option value="price_desc" <?= $f['sort'] === 'price_desc' ? 'selected' : '' ?>>Price ↓</option></select><button class="btn btn-success">Go</button></div>
  </div></form>
  <p class="text-secondary"><?= count($list) ?> provider<?= count($list) === 1 ? '' : 's' ?> found</p>
  <div class="row g-4">
    <?php foreach ($list as $p): ?><div class="col-md-6 col-xl-4"><?php provider_card($p, 'user'); ?></div><?php endforeach; ?>
  </div>
  <?php if (!$list): ?><div class="card-soft empty-state"><i class="bi bi-search"></i><p class="mb-0 mt-2">No verified providers matched your filters.</p></div><?php endif; ?>
</div>
<?php layout_end();
