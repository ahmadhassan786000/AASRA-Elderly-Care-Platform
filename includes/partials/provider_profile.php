<?php /* expects $p, $svcs, $reviews, $area ('public'|'user'), $isFav */ $isUser = current_user() && current_user()['role'] === 'user'; ?>
<div class="row g-4">
  <div class="col-lg-8">
    <div class="card-soft">
      <div class="d-flex gap-4 align-items-center flex-wrap">
        <?= avatar($p, 'avatar-xl') ?>
        <div class="flex-grow-1">
          <h1 class="h2 mb-1"><?= e($p['name']) ?> <span class="badge text-bg-success fs-6 align-middle"><i class="bi bi-patch-check-fill"></i> Verified</span></h1>
          <div><?= stars((float)$p['rating']) ?> <span class="text-secondary"><?= $p['reviews'] ? number_format((float)$p['rating'], 1) . ' / 5 · ' . (int)$p['reviews'] . ' review' . ($p['reviews'] == 1 ? '' : 's') : 'No reviews yet' ?></span></div>
          <div class="text-secondary mt-1"><i class="bi bi-geo-alt"></i> <?= e($p['location']) ?></div>
        </div>
        <?php if ($area === 'user'): ?><button class="btn btn-outline-secondary fav-btn <?= $isFav ? 'on' : '' ?>" data-fav="<?= (int)$p['id'] ?>" aria-label="Toggle favourite"><i class="bi <?= $isFav ? 'bi-heart-fill text-danger' : 'bi-heart' ?>"></i> Favourite</button><?php endif; ?>
      </div>
      <hr>
      <h2 class="h5">About</h2>
      <p><?= nl2br(e($p['bio'])) ?></p>
      <?php if ($p['skills']): ?><h2 class="h5">Skills</h2>
        <p><?= e($p['skills']) ?></p><?php endif; ?>
      <h2 class="h5">Services</h2>
      <p><?php foreach ($svcs as $s): ?><span class="tag me-1"><?= e($s['service_name']) ?></span><?php endforeach; ?></p>
      <?php $offs = listed_offerings((int)$p['id']);
      if ($offs): ?>
        <h2 class="h5">Offerings</h2>
        <div class="list-group list-group-flush mb-2">
          <?php foreach ($offs as $o): ?><div class="list-group-item px-0">
              <div class="d-flex justify-content-between gap-3 flex-wrap">
                <div><b><?= e($o['title']) ?></b> <span class="tag ms-1"><?= e($o['service_name']) ?></span></div><?php if ($o['price'] !== null && (float)$o['price'] > 0): ?><div class="fw-bold text-nowrap"><?= e(money($o['price'])) ?><small class="text-secondary fw-normal"> / visit</small></div><?php endif; ?>
              </div>
              <?php if ($o['description']): ?><p class="mb-1 text-secondary"><?= nl2br(e($o['description'])) ?></p><?php endif; ?>
              <?php if ($o['availability']): ?><div class="small text-secondary"><i class="bi bi-clock me-1"></i><?= e($o['availability']) ?></div><?php endif; ?>
            </div><?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
    <div class="card-soft mt-4">
      <h2 class="h5">Reviews</h2>
      <?php foreach ($reviews as $r): ?><div class="border-bottom py-3"><b><?= e($r['user_name']) ?></b> <?= stars((float)$r['rating']) ?> <small class="text-secondary ms-1"><?= e(fmt_date($r['created_at'])) ?></small>
          <p class="mb-0 text-secondary"><?= e($r['review']) ?></p>
        </div><?php endforeach; ?>
      <?php if (!$reviews): ?><p class="text-secondary mb-0">No reviews yet.</p><?php endif; ?>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card-soft sticky-top" style="top:90px">
      <?php if ($isUser): ?><a href="<?= e(url('user/book/' . $p['id'])) ?>" class="btn btn-success btn-lg w-100">Book now</a>
      <?php elseif (!current_user()): ?><a href="<?= e(url('login?next=' . rawurlencode('/user/book/' . $p['id']))) ?>" class="btn btn-success btn-lg w-100">Log in to book</a>
        <p class="small text-secondary mt-2 mb-0">New here? <a href="<?= e(url('register')) ?>">Create a free account</a>.</p>
      <?php else: ?><p class="small text-secondary mb-0">Sign in with a user account to book this provider.</p><?php endif; ?>
    </div>
  </div>
</div>