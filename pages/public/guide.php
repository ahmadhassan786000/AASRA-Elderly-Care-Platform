<?php $all = guides(); $g = $all[get('slug')] ?? null; if (!$g) { show_error_page(404); exit; }
layout('public', ['title' => $g['title']]); ?>
<section class="section"><div class="container"><a href="<?= e(url('guides')) ?>" class="text-decoration-none"><i class="bi bi-arrow-left"></i> All guides</a>
<article class="prose mt-3"><span class="guide-ic"><i class="bi <?= e($g['icon']) ?>"></i></span><h1 class="mb-3"><?= e($g['title']) ?></h1>
<?php foreach ($g['body'] as $para): ?><p><?= e($para) ?></p><?php endforeach; ?>
<div class="card-soft mt-4"><h2 class="h5">Quick tips</h2><ul class="mb-0"><?php foreach ($g['tips'] as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul></div>
<a class="btn btn-success mt-4" href="<?= e(url('services')) ?>">Find a provider</a></article></div></section>
<?php layout_end();
