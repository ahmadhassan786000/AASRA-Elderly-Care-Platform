<?php
include __DIR__ . '/head.php';
$cu = current_user(); $active = $active ?? '';
$items = [
  ['dashboard','admin','bi-speedometer2','Dashboard'],['users','admin/users','bi-people','Users'],['providers','admin/providers','bi-person-badge','Providers'],
  ['verification','admin/verification','bi-patch-check','Verification'],['services','admin/services','bi-grid-3x3-gap','Services'],
  ['bookings','admin/bookings','bi-calendar-check','Bookings'],['payments','admin/payments','bi-credit-card','Payments'],['complaints','admin/complaints','bi-exclamation-triangle','Complaints'],
];
$pendingV = (int)val("SELECT COUNT(*) FROM providers WHERE verification_status='Pending'");
?>
<body class="app-body admin-area">
<div class="app-shell">
  <aside class="sidebar sidebar-admin offcanvas-lg offcanvas-start" id="sidebar" tabindex="-1">
    <div class="sidebar-head"><?php include APP_ROOT . '/includes/partials/brand.php'; ?><span class="role-pill">Admin</span></div>
    <nav class="sidebar-nav">
      <?php foreach ($items as [$k, $path, $ic, $label]): ?>
        <a href="<?= e(url($path)) ?>" class="side-link <?= $active === $k ? 'active' : '' ?>" <?= $active === $k ? 'aria-current="page"' : '' ?>><i class="bi <?= e($ic) ?>"></i><span><?= e($label) ?></span><?php if ($k === 'verification' && $pendingV): ?><span class="side-count"><?= $pendingV ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="side-sep"></div>
      <a href="<?= e(url('logout')) ?>" class="side-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
      <a href="<?= e(url('')) ?>" target="_blank" rel="noopener" class="side-link"><i class="bi bi-box-arrow-up-right"></i><span>View Website</span></a>
    </nav>
  </aside>
  <div class="app-main">
    <header class="app-top">
      <button class="icon-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Open menu"><i class="bi bi-list"></i></button>
      <span class="fw-semibold d-none d-lg-inline">Admin Dashboard</span>
      <div class="ms-auto d-flex align-items-center gap-2">
        <?php include APP_ROOT . '/includes/partials/theme_toggle.php'; ?>
        <span class="small text-secondary d-none d-sm-inline"><?= e($cu['name'] ?? 'Admin') ?></span><span class="avatar avatar-sm"><?= e(initials($cu['name'] ?? 'A')) ?></span>
      </div>
    </header>
    <div class="app-content">
      <?php render_flashes(); ?>
