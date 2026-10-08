<?php
$u = require_role('user');
$ids = array_column(rows('SELECT provider_id FROM favorites WHERE user_id=? ORDER BY created_at DESC', [$u['id']]), 'provider_id');
$list = $ids ? listed_providers(['ids' => $ids]) : [];
layout('user', ['title' => 'Favorites', 'nav' => 'favorites']);
?>
<div class="container py-4"><div class="page-head"><div><h1>Favorites</h1><p>Providers you saved for later.</p></div></div>
<div class="row g-4"><?php foreach ($list as $p): ?><div class="col-md-6 col-xl-4" data-fav-card><?php provider_card($p, 'user'); ?>
  <button class="btn btn-sm btn-outline-danger mt-2 fav-btn on" data-fav="<?= (int)$p['id'] ?>" data-remove="1"><i class="bi bi-heart-fill text-danger"></i> Remove</button></div><?php endforeach; ?></div>
<?php if (!$list): ?><div class="card-soft empty-state"><i class="bi bi-heart"></i><p class="mt-2">You have no favorites yet. Open a provider profile and tap Favourite.</p><a class="btn btn-success" href="<?= e(url('user/providers')) ?>">Find providers</a></div><?php endif; ?></div>
<?php layout_end();
