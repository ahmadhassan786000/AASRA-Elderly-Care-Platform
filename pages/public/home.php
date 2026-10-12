<?php
$services = active_services();
$top = listed_providers([], 6);
$stats = [
  'providers' => (int)val("SELECT COUNT(*) FROM providers p JOIN users u ON u.id=p.user_id WHERE p.verification_status='Approved' AND p.account_status='Active' AND u.status='Active'"),
  'visits' => (int)val("SELECT COUNT(*) FROM bookings WHERE status='Completed'"),
  'rating' => (float)val('SELECT COALESCE(AVG(rating),0) FROM ratings'),
];
$spot = $top[0] ?? null;
layout('public', ['title' => 'AASRA', 'nav' => 'home', 'motion' => true, 'js' => ['home.js', 'motion.js']]);
?>
<section class="hero hero-anim anchor-target" id="home" data-spy-section="home">
  <div class="hero-decor" aria-hidden="true"><span class="hero-orb o1"></span><span class="hero-orb o2"></span></div>
  <div class="container">
    <div class="row align-items-center gy-5 gx-lg-5">
      <div class="col-lg-7">
        <h1 class="mb-3 rise" style="--i:0">Trusted Care, When You Need It</h1>
        <p class="lead fs-5 mb-4 rise" style="--i:1">Find reliable and verified service providers for elderly and differently-abled individuals.</p>
        <form class="search-panel rise" style="--i:2" action="<?= e(url('services')) ?>" method="get" role="search">
          <label class="sp-field"><i class="bi bi-search"></i><input class="form-control" name="q" placeholder="Service or provider" aria-label="Search a service or provider"></label>
          <label class="sp-field"><i class="bi bi-grid"></i><select class="form-select" name="service" aria-label="Service">
              <option value="0">Any service</option><?php foreach ($services as $s): ?><option value="<?= (int)$s['id'] ?>"><?= e($s['service_name']) ?></option><?php endforeach; ?>
            </select></label>
          <button class="btn btn-success px-4">Search</button>
        </form>
        <div class="hero-stats rise" style="--i:3">
          <div><b><?= $stats['providers'] ?></b><span>Verified providers</span></div>
          <div><b><?= $stats['visits'] ?></b><span>Visits completed</span></div>
          <div><b><?= $stats['rating'] ? number_format($stats['rating'], 1) : '—' ?> <i class="bi bi-star-fill stars fs-6"></i></b><span>Average rating</span></div>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block">
        <div class="hero-art" aria-hidden="true">
          <div class="hero-blob"><img src="<?= e(asset('images/logo.svg')) ?>" alt=""></div>
          <div class="float-card a"><span class="ic"><i class="bi bi-patch-check"></i></span>
            <div><b>Admin-verified</b>
              <div class="text-secondary small">Documents reviewed</div>
            </div>
          </div>
          <?php if ($spot): ?><div class="float-card b"><?= avatar($spot, 'avatar-sm') ?><div><b><?= e($spot['name']) ?></b>
                <div class="small"><?= stars((float)$spot['rating']) ?> <span class="text-secondary"><?= e($spot['location']) ?></span></div>
              </div>
            </div><?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section anchor-target" id="services" data-spy-section="services">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Services</h2>
        <p class="text-secondary mb-0">Choose the kind of support you need.</p>
      </div><a class="btn btn-outline-success" href="<?= e(url('services')) ?>">All services</a>
    </div>
    <?php $area = 'public';
    include APP_ROOT . '/includes/partials/service_browser.php'; ?>
  </div>
</section>

<section class="section pt-0 anchor-target" id="guides" data-spy-section="guides">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Helpful Guides</h2>
        <p class="text-secondary mb-0">Short, practical reading for families and caregivers.</p>
      </div>
    </div>
    <div class="row g-4"><?php $gi = 0;
                          foreach (guides() as $g): ?>
        <div class="col-sm-6 col-lg-3 reveal" style="--i:<?= $gi++ % 4 ?>">
          <article class="guide-card"><span class="guide-ic" aria-hidden="true"><i class="bi <?= e($g['icon']) ?>"></i></span>
            <h3 class="h6"><?= e($g['title']) ?></h3>
            <p class="text-secondary small mb-0"><?= e($g['excerpt']) ?></p>
          </article>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section pt-0 anchor-target" id="top-rated" data-spy-section="top-rated">
  <div class="container">
    <div class="section-head reveal">
      <div>
        <h2>Top Rated Providers</h2>
        <p class="text-secondary mb-0">Highly rated by the families who booked them.</p>
      </div><a class="btn btn-outline-success" href="<?= e(url('services')) ?>">Browse providers</a>
    </div>
    <?php if ($top): ?><div class="row g-4"><?php $pi = 0;
                                            foreach ($top as $p): ?><div class="col-md-6 col-lg-4 reveal" style="--i:<?= $pi++ % 3 ?>"><?php provider_card($p, 'public'); ?></div><?php endforeach; ?></div>
    <?php else: ?><div class="card-soft empty-state"><i class="bi bi-people"></i>
        <p class="mb-0 mt-2">Verified providers will appear here soon.</p>
      </div><?php endif; ?>
  </div>
</section>

<section class="section pt-0">
  <div class="container">
    <div class="cta-band reveal d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div>
        <h2 class="h3 mb-1">Offer care? Join AASRA as a provider.</h2>
        <p class="mb-0 opacity-75">Create your account, complete your profile and get verified to receive booking requests.</p>
      </div>
      <a class="btn btn-light btn-lg" href="<?= e(url('register?type=provider')) ?>">Become a provider</a>
    </div>
  </div>
</section>
<?php layout_end();
