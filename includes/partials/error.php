<div class="error-box text-center">
  <div class="error-code"><?= (int)$code ?></div>
  <h1 class="h2 mb-2"><?= e($heading) ?></h1>
  <p class="text-secondary mb-4"><?= e($message) ?></p>
  <div class="d-flex gap-2 justify-content-center flex-wrap">
    <?php
$role = $_SESSION['uid_role'] ?? '';
$homePath = match ($role) {
    'admin' => 'admin',
    'provider' => 'provider',
    'user' => 'user',
    default => '',
};
?>
<a class="btn btn-success btn-lg" href="<?= e(url($homePath)) ?>"><i class="bi bi-house"></i> <?= $role !== '' ? 'Back to Dashboard' : 'Back to Home' ?></a>
  </div>
</div>