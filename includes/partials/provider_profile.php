<?php
/* Provider profile (shared by /providers/{id} and /user/providers/{id}).
   Expects $p (listed provider row), $svcs (active categories of this provider), $reviews, $area ('public'|'user'), $isFav.
   Everything shown comes from the database; nothing is filled in when data is missing. */
$cu = current_user();
$isUser = $cu && $cu['role'] === 'user';
$pid = (int)$p['id'];
$offs = listed_offerings($pid);

// Categories: one chip per category, even when several services share it.
$cats = [];
foreach ($svcs as $s) $cats[(int)$s['id']] = (string)$s['category_name'];
foreach ($offs as $o) $cats[(int)$o['category_id']] = (string)$o['category_name'];
asort($cats, SORT_NATURAL | SORT_FLAG_CASE);

// Skills: stored as free text, shown as separate tags (split on commas, semicolons or new lines).
$skillList = [];
foreach (preg_split('/[,;\r\n]+/', (string)($p['skills'] ?? '')) ?: [] as $sk) {
    $sk = trim($sk);
    $key = mb_strtolower($sk);
    if ($sk === '' || isset($skillList[$key])) continue;
    $skillList[$key] = mb_strimwidth($sk, 0, 60, '…', 'UTF-8');
    if (count($skillList) >= 20) break;
}

$bio = trim((string)($p['bio'] ?? ''));
$intro = $bio !== '' ? mb_strimwidth((string)preg_replace('/\s+/u', ' ', $bio), 0, 170, '…', 'UTF-8') : '';
$reviewCount = (int)$p['reviews'];
$avg = (float)$p['rating'];
$location = trim((string)($p['location'] ?? ''));
$verified = ($p['verification_status'] ?? '') === 'Approved';
?>
<article class="provider-profile">
  <header class="provider-profile__header card-soft">
    <div class="provider-profile__avatar">
      <?php if (!empty($p['photo'])): ?>
        <img src="<?= e(upload_url($p['photo'])) ?>" alt="Profile photo of <?= e($p['name']) ?>" width="128" height="128" onerror="this.hidden=true;this.nextElementSibling.hidden=false">
        <span hidden aria-hidden="true"><?= e(initials((string)$p['name'])) ?></span>
      <?php else: ?>
        <span aria-hidden="true"><?= e(initials((string)$p['name'])) ?></span>
      <?php endif; ?>
    </div>

    <div class="provider-profile__identity">
      <h1 class="provider-profile__name"><?= e($p['name']) ?></h1>
      <div class="provider-profile__meta">
        <?php if ($verified): ?><span class="provider-profile__verified"><i class="bi bi-patch-check-fill" aria-hidden="true"></i> Verified</span><?php endif; ?>
        <?php if ($reviewCount > 0): ?>
          <span class="provider-profile__rating"><?= stars($avg) ?> <b><?= e(number_format($avg, 1)) ?></b> <span class="text-secondary">· <?= $reviewCount ?> review<?= $reviewCount === 1 ? '' : 's' ?></span></span>
        <?php else: ?>
          <span class="provider-profile__new"><i class="bi bi-stars" aria-hidden="true"></i> New provider</span>
        <?php endif; ?>
        <?php if ($location !== ''): ?><span class="provider-profile__location"><i class="bi bi-geo-alt" aria-hidden="true"></i> <?= e($location) ?></span><?php endif; ?>
      </div>
      <?php if ($intro !== ''): ?><p class="provider-profile__intro"><?= e($intro) ?></p><?php endif; ?>
    </div>

    <div class="provider-profile__actions">
      <?php if ($area === 'user'): ?><button type="button" class="btn btn-outline-secondary fav-btn <?= $isFav ? 'on' : '' ?>" data-fav="<?= $pid ?>" aria-label="Toggle favourite"><i class="bi <?= $isFav ? 'bi-heart-fill text-danger' : 'bi-heart' ?>" aria-hidden="true"></i> Favourite</button><?php endif; ?>
      <?php if ($offs): ?><a class="btn btn-success" href="#services"><i class="bi bi-grid" aria-hidden="true"></i> View services</a><?php endif; ?>
    </div>
  </header>

  <div class="row g-4">
    <div class="col-lg-6 order-2 order-lg-1">
      <div class="provider-profile__details">
        <section class="card-soft" aria-labelledby="pp-about">
          <h2 id="pp-about" class="provider-profile__title"><i class="bi bi-person-lines-fill" aria-hidden="true"></i> About</h2>
          <?php if ($bio !== ''): ?><p class="mb-0"><?= nl2br(e($bio)) ?></p>
          <?php else: ?><p class="text-secondary mb-0">This provider hasn't added an introduction yet.</p><?php endif; ?>
        </section>

        <?php if ($skillList): ?>
        <section class="card-soft" aria-labelledby="pp-skills">
          <h2 id="pp-skills" class="provider-profile__title"><i class="bi bi-award" aria-hidden="true"></i> Skills and expertise</h2>
          <ul class="provider-chips">
            <?php foreach ($skillList as $sk): ?><li class="provider-chip"><?= e($sk) ?></li><?php endforeach; ?>
          </ul>
        </section>
        <?php endif; ?>

        <?php if ($cats): ?>
        <section class="card-soft" aria-labelledby="pp-cats">
          <h2 id="pp-cats" class="provider-profile__title"><i class="bi bi-grid" aria-hidden="true"></i> Categories offered</h2>
          <ul class="provider-chips">
            <?php foreach ($cats as $cname): ?><li class="provider-chip provider-chip--category"><i class="bi <?= e(service_icon($cname)) ?>" aria-hidden="true"></i> <?= e($cname) ?></li><?php endforeach; ?>
          </ul>
        </section>
        <?php endif; ?>

        <section class="card-soft" aria-labelledby="pp-reviews">
          <div class="provider-profile__title-row">
            <h2 id="pp-reviews" class="provider-profile__title"><i class="bi bi-chat-square-heart" aria-hidden="true"></i> Reviews</h2>
            <?php if ($reviewCount > 0): ?><span class="provider-profile__rating"><?= stars($avg) ?> <b><?= e(number_format($avg, 1)) ?></b> <span class="text-secondary">(<?= $reviewCount ?>)</span></span><?php endif; ?>
          </div>
          <?php if ($reviews): ?>
            <ul class="provider-reviews">
              <?php foreach ($reviews as $r): ?>
              <li class="provider-review">
                <div class="provider-review__head">
                  <span class="provider-review__avatar" aria-hidden="true"><?= e(initials((string)$r['user_name'])) ?></span>
                  <div class="provider-review__who">
                    <b><?= e($r['user_name']) ?></b>
                    <small class="text-secondary"><?= e(fmt_date($r['created_at'])) ?></small>
                  </div>
                  <?= stars((float)$r['rating']) ?>
                </div>
                <?php if (trim((string)$r['review']) !== ''): ?><p class="provider-review__text"><?= nl2br(e($r['review'])) ?></p><?php endif; ?>
              </li>
              <?php endforeach; ?>
            </ul>
          <?php else: ?>
            <div class="empty-state provider-empty">
              <i class="bi bi-chat-square-heart" aria-hidden="true"></i>
              <p class="mb-0 mt-2">No reviews yet. Reviews appear here after clients complete a booking.</p>
            </div>
          <?php endif; ?>
        </section>
      </div>
    </div>

    <div class="col-lg-6 order-1 order-lg-2">
      <section id="services" class="provider-offerings" aria-labelledby="pp-services" tabindex="-1">
        <div class="provider-profile__title-row">
          <h2 id="pp-services" class="provider-profile__title"><i class="bi bi-briefcase" aria-hidden="true"></i> Available services</h2>
          <?php if ($offs): ?><span class="text-secondary small"><?= count($offs) ?> service<?= count($offs) === 1 ? '' : 's' ?></span><?php endif; ?>
        </div>

        <?php if ($offs): ?>
        <ul class="provider-offerings__list">
          <?php foreach ($offs as $o):
            $hasPrice = $o['price'] !== null && (float)$o['price'] > 0;
            $oid = (int)$o['id'];
            $desc = trim((string)($o['description'] ?? ''));
            $avail = trim((string)($o['availability'] ?? ''));
          ?>
          <li>
            <article class="offering-card" aria-labelledby="offering-<?= $oid ?>">
              <div class="offering-card__top">
                <span class="offering-card__icon" aria-hidden="true"><i class="bi <?= e(service_icon((string)$o['category_name'])) ?>"></i></span>
                <div class="offering-card__main">
                  <h3 id="offering-<?= $oid ?>" class="offering-card__title"><?= e($o['title']) ?></h3>
                  <span class="provider-chip provider-chip--category"><?= e($o['category_name']) ?></span>
                </div>
                <div class="offering-card__price">
                  <?php if ($hasPrice): ?>
                    <?= e(money($o['price'])) ?><span class="offering-card__unit">/ visit</span>
                  <?php else: ?>
                    <span class="offering-card__unit">Price not listed</span>
                  <?php endif; ?>
                </div>
              </div>
              <?php if ($desc !== ''): ?><p class="offering-card__desc"><?= nl2br(e($desc)) ?></p><?php endif; ?>
              <?php if ($avail !== ''): ?>
              <div class="offering-card__meta"><span><i class="bi bi-clock" aria-hidden="true"></i> <span class="visually-hidden">Availability:</span> <?= e($avail) ?></span></div>
              <?php endif; ?>
              <?php if ($hasPrice): ?>
              <div class="offering-card__action">
                <?php if ($isUser): ?>
                  <a class="btn btn-success w-100" href="<?= e(url('user/book/' . $pid . '?service=' . $oid)) ?>">Book Now<span class="visually-hidden">: <?= e($o['title']) ?></span></a>
                <?php elseif (!$cu): ?>
                  <a class="btn btn-success w-100" href="<?= e(url('login?next=' . rawurlencode('/user/book/' . $pid . '?service=' . $oid))) ?>">Log in to book<span class="visually-hidden">: <?= e($o['title']) ?></span></a>
                <?php endif; ?>
              </div>
              <?php endif; ?>
            </article>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php if (!$isUser && $cu): ?><p class="small text-secondary mt-3 mb-0">Sign in with a user account to book this provider.</p>
        <?php elseif (!$cu): ?><p class="small text-secondary mt-3 mb-0">New here? <a href="<?= e(url('register')) ?>">Create a free account</a>.</p><?php endif; ?>
        <?php else: ?>
        <div class="card-soft empty-state provider-empty">
          <i class="bi bi-briefcase" aria-hidden="true"></i>
          <p class="mb-0 mt-2">This provider hasn't published any services yet.</p>
        </div>
        <?php endif; ?>
      </section>
    </div>
  </div>
</article>
