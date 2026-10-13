<?php /* expects $area ('public'|'user'), $categories */ ?>
<div id="svcBrowser" data-area="<?= e($area) ?>" data-profile="<?= e(url($area === 'user' ? 'user/providers/' : 'providers/')) ?>" data-listing="<?= e(url($area === 'user' ? 'user/providers' : 'categories')) ?>">
  <div class="chips mb-4" role="tablist" aria-label="Filter categories">
    <button type="button" class="chip active" data-category="0">All</button>
    <?php foreach ($categories as $s): ?><button type="button" class="chip" data-category="<?= (int)$s['id'] ?>"><?= e($s['category_name']) ?></button><?php endforeach; ?>
  </div>
  <div class="row g-4" id="svcGrid">
    <?php $__si = 0;
    foreach ($categories as $s): ?>
      <div class="col-sm-6 col-lg-4 reveal" style="--i:<?= $__si++ % 3 ?>" data-svc="<?= (int)$s['id'] ?>">
        <a class="svc-card" href="<?= e(url($area === 'user' ? 'user/providers?category=' . $s['id'] : 'categories/' . $s['id'])) ?>">
          <?= service_thumb($s) ?>
          <div class="svc-body">
            <h3><?= e($s['category_name']) ?></h3>
            <p class="text-secondary small mb-0 clamp-2"><?= e($s['description']) ?></p>
          </div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-5" id="svcProviders" hidden>
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="h5 mb-0" id="svcProvidersTitle">Providers</h3><a class="btn btn-outline-success btn-sm" id="svcProvidersAll" href="#">See all</a>
    </div>
    <div class="row g-3" id="svcProvidersGrid"></div>
  </div>
</div>
