<?php include __DIR__ . '/head.php'; $cu = current_user(); $nav = $nav ?? ''; ?>
<body class="site-body">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header sticky-top">
  <nav class="navbar navbar-expand-md container">
    <?php include APP_ROOT . '/includes/partials/brand.php'; ?>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-label="Menu"><i class="bi bi-list fs-2"></i></button>
    <div class="collapse navbar-collapse" id="siteNav">
      <ul class="navbar-nav mx-md-auto gap-md-1">
        <li class="nav-item"><a class="nav-link <?= $nav === 'home' ? 'active' : '' ?>" href="<?= e(url('')) ?>">Home</a></li>
        <li class="nav-item"><a class="nav-link <?= $nav === 'services' ? 'active' : '' ?>" href="<?= e(url('services')) ?>">Services</a></li>
        <li class="nav-item"><a class="nav-link <?= $nav === 'how' ? 'active' : '' ?>" href="<?= e(url('how-it-works')) ?>">How It Works</a></li>
      </ul>
      <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
        <?php include APP_ROOT . '/includes/partials/theme_toggle.php'; ?>
        <a class="icon-btn" href="<?= e($cu ? home_for_role($cu['role']) : url('login')) ?>" aria-label="<?= $cu ? 'My account' : 'Log in' ?>" title="<?= $cu ? e($cu['name']) : 'Log in' ?>"><i class="bi bi-person-circle"></i></a>
      </div>
    </div>
  </nav>
</header>
<main id="main"><?php if (!empty($_SESSION['flash'])): ?><div class="container pt-3"><?php render_flashes(); ?></div><?php endif; ?>
