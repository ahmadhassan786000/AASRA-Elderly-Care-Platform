<?php
$steps = [['Search','bi-search','Find verified providers by service, skill or area.'],['Compare','bi-sliders','Review experience, charges, availability and ratings.'],['Book','bi-calendar-check','Send a booking request with the date and your requirements.'],['Receive care','bi-heart','The provider accepts, visits and marks the service complete.'],['Rate','bi-star','Share your experience so other families can choose well.']];
layout('public', ['title' => 'How It Works', 'nav' => 'how']); ?>
<section class="section"><div class="container">
  <h1 class="mb-2">How AASRA works</h1><p class="text-secondary mb-5 prose">Five simple steps from searching to a rated visit.</p>
  <div class="row g-4"><?php foreach ($steps as $i => [$t, $ic, $d]): ?><div class="col-md-6 col-lg"><div class="card-soft h-100"><div class="step-no"><?= $i + 1 ?></div><h2 class="h5"><i class="bi <?= $ic ?> text-success me-1"></i> <?= e($t) ?></h2><p class="text-secondary mb-0"><?= e($d) ?></p></div></div><?php endforeach; ?></div>
  <div class="card-soft mt-5"><h2 class="h4"><i class="bi bi-patch-check text-success"></i> How providers are verified</h2><p class="mb-2 prose">Providers upload identity and experience documents. An AASRA administrator reviews each one, and only approved, active providers appear in search results.</p><a href="<?= e(url('guides/how-provider-verification-works')) ?>">Read the verification guide</a></div>
  <div class="mt-4 d-flex gap-2 flex-wrap"><a class="btn btn-success btn-lg" href="<?= e(url('register')) ?>">Create an account</a><a class="btn btn-outline-success btn-lg" href="<?= e(url('services')) ?>">Browse services</a></div>
</div></section>
<?php layout_end();
