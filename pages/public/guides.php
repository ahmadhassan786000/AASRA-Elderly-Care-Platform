<?php layout('public', ['title' => 'Helpful Guides', 'nav' => 'guides']); ?>
<section class="section"><div class="container"><h1 class="mb-2">Helpful guides</h1><p class="text-secondary mb-4">Practical reading for families and caregivers.</p>
<div class="row g-4"><?php foreach (guides() as $slug => $g): ?><div class="col-md-6"><a class="guide-card" href="<?= e(url('guides/' . $slug)) ?>"><span class="guide-ic"><i class="bi <?= e($g['icon']) ?>"></i></span><h2 class="h5"><?= e($g['title']) ?></h2><p class="text-secondary mb-0"><?= e($g['excerpt']) ?></p></a></div><?php endforeach; ?></div></div></section>
<?php layout_end();
