<?php
$u = require_role('user');
$services = active_services();
$top = listed_providers([], 3);
// Recommended: providers offering services the user booked before, or in the user's area.
$mine = row('SELECT address FROM users WHERE id=?', [$u['id']]);
$past = array_column(rows('SELECT DISTINCT service_id FROM bookings WHERE user_id=?', [$u['id']]), 'service_id');
$area = trim(explode(',', (string)($mine['address'] ?? ''))[0]);
$shownIds = array_column($top, 'id'); $rec = [];
foreach (listed_providers([], 40) as $p) {
    $score = 0;
    if ($past) { $ps = array_column(rows('SELECT service_id FROM provider_services WHERE provider_id=?', [$p['id']]), 'service_id'); $score += 2 * count(array_intersect($past, $ps)); }
    if ($area !== '' && stripos((string)$p['location'], $area) !== false) $score += 3;
    if ($score > 0) $rec[] = [$score, $p];
}
usort($rec, fn($a, $b) => $b[0] <=> $a[0] ?: $b[1]['rating'] <=> $a[1]['rating']);
$rec = array_map(fn($x) => $x[1], array_slice($rec, 0, 3));
$recTitle = $rec ? 'Recommended for you' : 'More verified providers';
if (!$rec) $rec = array_slice(listed_providers([], 6), 3, 3);
$favIds = array_column(rows('SELECT provider_id FROM favorites WHERE user_id=?', [$u['id']]), 'provider_id');
$next = row("SELECT b.*,p.name provider_name,s.service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.user_id=? AND b.status IN ('Pending','Accepted','Confirmed') AND b.booking_date>=CURDATE() ORDER BY b.booking_date,b.booking_time LIMIT 1", [$u['id']]);
layout('user', ['title' => 'Home', 'nav' => 'home', 'js' => ['home.js']]);
?>
<section class="hero"><div class="container">
  <p class="mb-2 fw-semibold text-success">Hello, <?= e(explode(' ', $u['name'])[0]) ?></p>
  <h1 class="mb-4" style="max-width:20ch">What kind of support do you need?</h1>
  <form class="search-panel" action="<?= e(url('user/providers')) ?>" method="get" role="search">
    <label class="sp-field"><i class="bi bi-search"></i><input class="form-control" name="q" placeholder="Search services or providers..." aria-label="Search services or providers"></label>
    <label class="sp-field"><i class="bi bi-geo-alt"></i><input class="form-control" name="location" placeholder="Area (optional)" aria-label="Area"></label>
    <button class="btn btn-success px-4">Search</button>
  </form>
  <?php if ($next): ?><div class="card-soft mt-4 d-inline-flex align-items-center gap-3 py-3"><span class="stat-ic"><i class="bi bi-calendar-event"></i></span><div><div class="small text-secondary">Your next booking</div><b><?= e($next['service_name']) ?></b> with <?= e($next['provider_name']) ?> · <?= e(fmt_date($next['booking_date'])) ?>, <?= e(date('g:i A', strtotime($next['booking_time']))) ?> <?= badge($next['status']) ?></div><a class="btn btn-sm btn-outline-success" href="<?= e(url('user/bookings')) ?>">Manage</a></div><?php endif; ?>
</div></section>

<section class="section"><div class="container">
  <div class="section-head"><div><h2>Services</h2><p class="text-secondary mb-0">Filter by service to see matching providers.</p></div></div>
  <?php $area = 'user'; include APP_ROOT . '/includes/partials/service_browser.php'; ?>
</div></section>

<section class="section pt-0"><div class="container">
  <div class="section-head"><div><h2>Helpful Guides</h2></div><a class="btn btn-outline-success" href="<?= e(url('guides')) ?>">All guides</a></div>
  <div class="row g-4"><?php foreach (guides() as $slug => $g): ?><div class="col-sm-6 col-lg-3"><a class="guide-card" href="<?= e(url('guides/' . $slug)) ?>"><span class="guide-ic"><i class="bi <?= e($g['icon']) ?>"></i></span><h3 class="h6"><?= e($g['title']) ?></h3><p class="text-secondary small mb-0"><?= e($g['excerpt']) ?></p></a></div><?php endforeach; ?></div>
</div></section>

<section class="section pt-0"><div class="container">
  <div class="section-head"><div><h2>Top Rated</h2></div><a class="btn btn-outline-success" href="<?= e(url('user/providers')) ?>">Find providers</a></div>
  <div class="row g-4"><?php foreach ($top as $p): ?><div class="col-md-6 col-lg-4"><?php provider_card($p, 'user'); ?></div><?php endforeach; ?></div>
  <?php if (!$top): ?><div class="card-soft empty-state"><i class="bi bi-people"></i><p class="mb-0 mt-2">No providers yet.</p></div><?php endif; ?>
</div></section>

<?php if ($rec): ?><section class="section pt-0"><div class="container">
  <div class="section-head"><div><h2><?= e($recTitle) ?></h2><p class="text-secondary mb-0"><?= $recTitle === 'Recommended for you' ? 'Based on your past bookings and area.' : 'Browse more verified providers.' ?></p></div></div>
  <div class="row g-4"><?php foreach ($rec as $p): ?><div class="col-md-6 col-lg-4"><?php provider_card($p, 'user'); ?></div><?php endforeach; ?></div>
</div></section><?php endif; ?>
<?php layout_end();
