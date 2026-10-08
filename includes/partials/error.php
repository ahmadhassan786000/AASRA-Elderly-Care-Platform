<div class="error-box text-center">
  <div class="error-code"><?= (int)$code ?></div>
  <h1 class="h2 mb-2"><?= e($heading) ?></h1>
  <p class="text-secondary mb-4"><?= e($message) ?></p>
  <div class="d-flex gap-2 justify-content-center flex-wrap">
    <a class="btn btn-success btn-lg" href="<?= e(url('')) ?>"><i class="bi bi-house"></i> Back to Home</a>
    <?php if (!empty($inAdmin)): ?><a class="btn btn-outline-success btn-lg" href="<?= e(url('admin')) ?>">Admin dashboard</a><?php endif; ?>
  </div>
</div>
