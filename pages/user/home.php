<?php
$u = require_role('user');
$categories = active_categories();
$top = listed_providers([], 3);
// Recommended: providers offering services the user booked before, or in the user's area.
$mine = row('SELECT address FROM users WHERE id=?', [$u['id']]);
$past = array_column(rows('SELECT DISTINCT category_id FROM bookings WHERE user_id=?', [$u['id']]), 'category_id');
$area = trim(explode(',', (string)($mine['address'] ?? ''))[0]);
$shownIds = array_column($top, 'id');
$rec = [];
foreach (listed_providers([], 40) as $p) {
  $score = 0;
  if ($past) {
    $ps = array_column(rows('SELECT category_id FROM provider_categories WHERE provider_id=?', [$p['id']]), 'category_id');
    $score += 2 * count(array_intersect($past, $ps));
  }
  if ($area !== '' && stripos((string)$p['location'], $area) !== false) $score += 3;
  if ($score > 0) $rec[] = [$score, $p];
}
usort($rec, fn($a, $b) => $b[0] <=> $a[0] ?: $b[1]['rating'] <=> $a[1]['rating']);
$rec = array_map(fn($x) => $x[1], array_slice($rec, 0, 3));
$recTitle = $rec ? 'Recommended for you' : 'More verified providers';
if (!$rec) $rec = array_slice(listed_providers([], 6), 3, 3);
$favIds = array_column(rows('SELECT provider_id FROM favorites WHERE user_id=?', [$u['id']]), 'provider_id');
layout('user', ['title' => 'Home', 'nav' => 'home', 'motion' => true, 'js' => ['home.js', 'motion.js']]);
?>
<section class="hero hero-anim" id="home">
  <div class="container">
    <p class="mb-2 fw-semibold text-success rise" style="--i:0">Hello, <?= e(explode(' ', $u['name'])[0]) ?></p>
    <h1 class="mb-4 rise" style="max-width:20ch;--i:1">What kind of support do you need?</h1>
    <form class="search-panel rise" style="--i:2" action="<?= e(url('user/providers')) ?>" method="get" role="search">
      <label class="sp-field"><i class="bi bi-search"></i><input class="form-control" name="q" placeholder="Search services or providers..." aria-label="Search services or providers"></label>
      <label class="sp-field"><i class="bi bi-geo-alt"></i><input class="form-control" name="location" placeholder="Area (optional)" aria-label="Area"></label>
      <button class="btn btn-success px-4">Search</button>
    </form>
  </div>
</section>

<section class="section home-band home-band--mint" id="categories">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Categories</h2>
        <p class="text-secondary mb-0">Filter by category to see matching providers.</p>
      </div>
    </div>
    <?php $area = 'user';
    include APP_ROOT . '/includes/partials/category_browser.php'; ?>
  </div>
</section>

<section class="section pt-0 home-band home-band--forest anchor-target" id="guides">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Helpful Guides</h2>
      </div>
    </div>
    <div class="row g-4"><?php $gi = 0;
                          foreach (guides() as $g): ?><div class="col-sm-6 col-lg-3 reveal" style="--i:<?= $gi++ % 4 ?>">
          <article class="guide-card"><span class="guide-ic" aria-hidden="true"><i class="bi <?= e($g['icon']) ?>"></i></span>
            <h3 class="h6"><?= e($g['title']) ?></h3>
            <p class="text-secondary small mb-0"><?= e($g['excerpt']) ?></p>
          </article>
        </div><?php endforeach; ?></div>
  </div>
</section>

<section class="section pt-0 home-band home-band--mint">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Top Rated</h2>
      </div><a class="btn btn-outline-success" href="<?= e(url('user/providers')) ?>">Find providers</a>
    </div>
    <div class="row g-4"><?php $pi = 0;
                          foreach ($top as $p): ?><div class="col-md-6 col-lg-4 reveal" style="--i:<?= $pi++ % 3 ?>"><?php provider_card($p, 'user'); ?></div><?php endforeach; ?></div>
    <?php if (!$top): ?><div class="card-soft empty-state"><i class="bi bi-people"></i>
        <p class="mb-0 mt-2">No providers yet.</p>
      </div><?php endif; ?>
  </div>
</section>

<?php if ($rec): ?><section class="section pt-0 home-band home-band--teal">
    <div class="container">
      <div class="section-head reveal">
        <div>
          <h2><?= e($recTitle) ?></h2>
          <p class="text-secondary mb-0"><?= $recTitle === 'Recommended for you' ? 'Based on your past bookings and area.' : 'Browse more verified providers.' ?></p>
        </div>
      </div>
      <div class="row g-4"><?php $ri = 0;
                            foreach ($rec as $p): ?><div class="col-md-6 col-lg-4 reveal" style="--i:<?= $ri++ % 3 ?>"><?php provider_card($p, 'user'); ?></div><?php endforeach; ?></div>
    </div>
  </section><?php endif; ?>
<?php layout_end();
