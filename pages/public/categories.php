<?php
// Public category browsing: /categories (all categories) and /categories/<id> (one category and its providers).
$categories = active_categories();
$routeId = (int)get('id');          // set by the router for /categories/<id>
$qs = get('q');

if (!$routeId && (int)get('category') > 0) {
  // Form submissions and old-style links (/categories?category=<id>) go to the clean /categories/<id> URL, keeping other filters.
  $qp = $_GET;
  $cid = (int)$qp['category'];
  unset($qp['category']);
  redirect('categories/' . $cid . ($qp ? '?' . http_build_query($qp) : ''), 301);
}

$sid = $routeId;
$current = null;
foreach ($categories as $s) if ($s['id'] == $sid) $current = $s;
if ($sid && !$current) {            // unknown or inactive category
  show_error_page(404);
  exit;
}
$searching = $sid || $qs !== '';
$list = $searching ? listed_providers(['q' => $qs, 'category' => $sid, 'sort' => get('sort', 'rating'), 'location' => get('location')]) : [];
layout('public', ['title' => $current ? $current['category_name'] : 'Categories', 'nav' => 'categories', 'js' => ['home.js']]);
?>
<section class="section pb-0">
  <div class="container">
    <nav aria-label="breadcrumb" class="mb-2">
      <ol class="breadcrumb mb-0 small">
        <li class="breadcrumb-item"><a href="<?= e(url('')) ?>">Home</a></li>
        <?php if ($current): ?>
          <li class="breadcrumb-item"><a href="<?= e(url('categories')) ?>">Categories</a></li>
          <li class="breadcrumb-item active" aria-current="page"><?= e($current['category_name']) ?></li>
        <?php else: ?>
          <li class="breadcrumb-item active" aria-current="page">Categories</li>
        <?php endif; ?>
      </ol>
    </nav>
    <h1 class="mb-1"><?= $current ? e($current['category_name']) : 'Categories' ?></h1>
    <p class="text-secondary mb-4"><?= $current ? e($current['description']) : 'Browse the categories of support AASRA providers offer, or search for a provider.' ?></p>
    <form class="search-panel mb-4" method="get" action="<?= e(url('categories')) ?>" role="search">
      <label class="sp-field"><i class="bi bi-search"></i><input class="form-control" name="q" value="<?= e($qs) ?>" placeholder="Search by name, skill or area" aria-label="Search"></label>
      <label class="sp-field"><i class="bi bi-grid"></i><select class="form-select" name="category" aria-label="Category">
          <option value="0">Any category</option><?php foreach ($categories as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $sid == $s['id'] ? 'selected' : '' ?>><?= e($s['category_name']) ?></option><?php endforeach; ?>
        </select></label>
      <button class="btn btn-success px-4">Search</button>
    </form>
  </div>
</section>
<section class="section pt-2">
  <div class="container">
    <?php if ($searching): ?>
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="h5 mb-0"><?= count($list) ?> verified provider<?= count($list) === 1 ? '' : 's' ?> found</h2><a href="<?= e(url('categories')) ?>" class="btn btn-sm btn-outline-secondary">Clear search</a>
      </div>
      <?php if ($list): ?><div class="row g-4"><?php foreach ($list as $p): ?><div class="col-md-6 col-lg-4"><?php provider_card($p, 'public'); ?></div><?php endforeach; ?></div>
      <?php else: ?><div class="card-soft empty-state"><i class="bi bi-search"></i>
          <p class="mb-0 mt-2">No verified providers matched your search. Try another category or keyword.</p>
        </div><?php endif; ?>
    <?php else: $area = 'public';
      include APP_ROOT . '/includes/partials/category_browser.php';
    endif; ?>
  </div>
</section>
<?php layout_end();
