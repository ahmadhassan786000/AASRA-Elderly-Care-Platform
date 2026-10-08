<?php
$services = active_services();
$sid = (int)get('service'); $qs = get('q');
$current = null; foreach ($services as $s) if ($s['id'] == $sid) $current = $s;
if ($sid && !$current) $sid = 0;
$searching = $sid || $qs !== '';
$list = $searching ? listed_providers(['q' => $qs, 'service' => $sid, 'sort' => get('sort', 'rating'), 'location' => get('location')]) : [];
layout('public', ['title' => $current ? $current['service_name'] : 'Services', 'nav' => 'services', 'js' => ['home.js']]);
?>
<section class="section pb-0"><div class="container">
  <h1 class="mb-1"><?= $current ? e($current['service_name']) : 'Services' ?></h1>
  <p class="text-secondary mb-4"><?= $current ? e($current['description']) : 'Browse the support AASRA providers offer, or search for a provider.' ?></p>
  <form class="search-panel mb-4" method="get" role="search">
    <label class="sp-field"><i class="bi bi-search"></i><input class="form-control" name="q" value="<?= e($qs) ?>" placeholder="Search by name, skill or area" aria-label="Search"></label>
    <label class="sp-field"><i class="bi bi-grid"></i><select class="form-select" name="service" aria-label="Service"><option value="0">Any service</option><?php foreach ($services as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $sid == $s['id'] ? 'selected' : '' ?>><?= e($s['service_name']) ?></option><?php endforeach; ?></select></label>
    <button class="btn btn-success px-4">Search</button>
  </form>
</div></section>
<section class="section pt-2"><div class="container">
<?php if ($searching): ?>
  <div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0"><?= count($list) ?> verified provider<?= count($list) === 1 ? '' : 's' ?> found</h2><a href="<?= e(url('services')) ?>" class="btn btn-sm btn-outline-secondary">Clear search</a></div>
  <?php if ($list): ?><div class="row g-4"><?php foreach ($list as $p): ?><div class="col-md-6 col-lg-4"><?php provider_card($p, 'public'); ?></div><?php endforeach; ?></div>
  <?php else: ?><div class="card-soft empty-state"><i class="bi bi-search"></i><p class="mb-0 mt-2">No verified providers matched your search. Try another service or keyword.</p></div><?php endif; ?>
<?php else: $area = 'public'; include APP_ROOT . '/includes/partials/service_browser.php'; endif; ?>
</div></section>
<?php layout_end();
