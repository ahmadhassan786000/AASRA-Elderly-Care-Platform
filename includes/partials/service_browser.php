<?php /* expects $area ('public'|'user'), $services */ ?>
<div id="svcBrowser" data-area="<?= e($area) ?>" data-profile="<?= e(url($area === 'user' ? 'user/providers/' : 'providers/')) ?>" data-listing="<?= e(url($area === 'user' ? 'user/providers' : 'services')) ?>">
  <div class="chips mb-4" role="tablist" aria-label="Filter services">
    <button type="button" class="chip active" data-service="0">All</button>
    <?php foreach ($services as $s): ?><button type="button" class="chip" data-service="<?= (int)$s['id'] ?>"><?= e($s['service_name']) ?></button><?php endforeach; ?>
  </div>
  <div class="row g-4" id="svcGrid">
    <?php foreach ($services as $s): ?>
      <div class="col-sm-6 col-lg-4" data-svc="<?= (int)$s['id'] ?>">
        <a class="svc-card" href="<?= e(url(($area === 'user' ? 'user/providers' : 'services') . '?service=' . $s['id'])) ?>">
          <?= service_thumb($s) ?>
          <div class="svc-body"><h3><?= e($s['service_name']) ?></h3><p class="text-secondary small mb-0 clamp-2"><?= e($s['description']) ?></p></div>
        </a>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="mt-5" id="svcProviders" hidden>
    <div class="d-flex justify-content-between align-items-center mb-3"><h3 class="h5 mb-0" id="svcProvidersTitle">Providers</h3><a class="btn btn-outline-success btn-sm" id="svcProvidersAll" href="#">See all</a></div>
    <div class="row g-3" id="svcProvidersGrid"></div>
  </div>
</div>
