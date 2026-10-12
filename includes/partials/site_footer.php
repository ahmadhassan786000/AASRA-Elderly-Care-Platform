<?php /* Shared site footer (homepage design). Set $footer_area = 'user' for the logged-in account variant of the Account column. */ ?>
<footer class="site-footer">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4">
        <div class="footer-brand mb-2"><img src="<?= e(asset('images/logo.svg')) ?>" alt="" width="34" height="34"> AASRA</div>
        <p class="mb-3 footer-muted">Connecting Care, Building Trust.</p>
        <div class="d-flex gap-2">
          <?php foreach (SOCIAL_LINKS as [$n, $ic, $href]): ?><a class="social" href="<?= e($href) ?>" target="_blank" rel="noopener noreferrer" aria-label="<?= e($n) ?>"><i class="bi <?= e($ic) ?>"></i></a><?php endforeach; ?>
        </div>
      </div>
      <div class="col-6 col-lg-2">
        <h6>Explore</h6>
        <a class="footer-link" href="<?= e(url('')) ?>">Home</a><a class="footer-link" href="<?= e(url('services')) ?>">Services</a><a class="footer-link" href="<?= e(url((($footer_area ?? 'public') === 'user' ? 'user' : '') . '#guides')) ?>">Helpful Guides</a>
      </div>
      <div class="col-6 col-lg-2">
        <?php if (($footer_area ?? 'public') === 'user'): ?>
          <h6>My account</h6>
          <a class="footer-link" href="<?= e(url('user')) ?>">Dashboard</a><a class="footer-link" href="<?= e(url('user/profile')) ?>">My profile</a><a class="footer-link" href="<?= e(url('user/bookings')) ?>">Bookings</a>
        <?php else: ?>
          <h6>Account</h6>
          <a class="footer-link" href="<?= e(url('login')) ?>">Login</a><a class="footer-link" href="<?= e(url('register')) ?>">Register</a><a class="footer-link" href="<?= e(url('register')) ?>">Become a Provider</a>
        <?php endif; ?>
      </div>

      <div class="col-6 col-lg-2">
        <h6>Contact</h6>
        <p class="footer-muted mb-0">Lahore, Pakistan<br>support@aasra.local</p>
      </div>
    </div>
    <hr class="footer-hr">
    <small class="footer-muted">© <?= date('Y') ?> AASRA. University project demonstration.</small>
  </div>
</footer>