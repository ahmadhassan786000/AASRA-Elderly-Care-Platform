<?php
$u = require_role('provider'); $p = current_provider(); $pid = (int)$p['id'];
$list = rows('SELECT r.*,u.name user_name FROM ratings r JOIN users u ON u.id=r.user_id WHERE r.provider_id=? ORDER BY r.created_at DESC', [$pid]);
$dist = array_fill(1, 5, 0); foreach ($list as $r) $dist[(int)$r['rating']]++;
$avg = $list ? array_sum(array_column($list, 'rating')) / count($list) : 0;
layout('provider', ['title' => 'Reviews', 'active' => 'reviews']);
?>
<div class="page-head"><div><h1>Reviews</h1><p>What families say about your care.</p></div></div>
<div class="row g-4"><div class="col-lg-4"><div class="card-soft text-center"><div class="display-3 fw-bold"><?= $list ? number_format($avg, 1) : '—' ?></div><?= stars((float)$avg) ?><div class="text-secondary mb-3"><?= count($list) ?> review<?= count($list) === 1 ? '' : 's' ?></div>
  <?php for ($i = 5; $i >= 1; $i--): $pc = $list ? round($dist[$i] / count($list) * 100) : 0; ?><div class="d-flex align-items-center gap-2 small mb-1"><span style="width:2rem"><?= $i ?> ★</span><div class="progress flex-grow-1" style="height:8px"><div class="progress-bar bg-warning" style="width:<?= $pc ?>%"></div></div><span style="width:2rem" class="text-end"><?= $dist[$i] ?></span></div><?php endfor; ?></div></div>
<div class="col-lg-8"><div class="card-soft"><?php foreach ($list as $r): ?><div class="border-bottom py-3"><b><?= e($r['user_name']) ?></b> <?= stars((float)$r['rating']) ?> <small class="text-secondary ms-1"><?= e(fmt_date($r['created_at'])) ?></small><p class="mb-0 text-secondary"><?= e($r['review'] ?: 'No written review.') ?></p></div><?php endforeach; ?>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-star"></i><p class="mb-0 mt-2">No reviews yet. They appear after completed visits are rated.</p></div><?php endif; ?></div></div></div>
<?php layout_end();
