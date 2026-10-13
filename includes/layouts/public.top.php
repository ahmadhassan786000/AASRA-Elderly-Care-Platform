<?php include __DIR__ . '/head.php';
$cu = current_user();
$nav = $nav ?? '';
// On the homepage the links scroll in place; on any other public page they lead back to the matching homepage section.
$onHome = ($nav === 'home');
$homeUrl = $onHome ? '' : url(''); ?>

<body class="site-body">
  <a class="skip-link" href="#main">Skip to content</a>
  <header class="site-header sticky-top">
    <nav class="navbar navbar-expand-md container">
      <?php include APP_ROOT . '/includes/partials/brand.php'; ?>
      <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#siteNav" aria-label="Menu"><i class="bi bi-list fs-2"></i></button>
      <div class="collapse navbar-collapse" id="siteNav">
        <ul class="navbar-nav mx-md-auto gap-md-1">
          <li class="nav-item"><a class="nav-link <?= $nav === 'home' ? 'active' : '' ?>" href="<?= e($onHome ? '#home' : url('')) ?>" data-spy="home">Home</a></li>
          <li class="nav-item"><a class="nav-link <?= $nav === 'categories' ? 'active' : '' ?>" href="<?= e($homeUrl . '#categories') ?>" data-spy="categories">Categories</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= e($homeUrl . '#guides') ?>" data-spy="guides">Guides</a></li>
        </ul>
        <div class="d-flex align-items-center gap-2 mt-3 mt-md-0">
          <a class="icon-btn" href="<?= e($cu ? home_for_role($cu['role']) : url('login')) ?>" aria-label="<?= $cu ? 'My account' : 'Log in' ?>" title="<?= $cu ? e($cu['name']) : 'Log in' ?>"><i class="bi bi-person-circle"></i></a>
        </div>
      </div>
    </nav>
  </header>
  <main id="main"><?php if (!empty($_SESSION['flash'])): ?><div class="container pt-3"><?php render_flashes(); ?></div><?php endif; ?>