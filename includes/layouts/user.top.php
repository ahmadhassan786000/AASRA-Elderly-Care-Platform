<?php
include __DIR__ . '/head.php';
$cu = current_user(); $nav = $nav ?? '';
$navServices = active_services();
$unread = (int)val('SELECT COUNT(*) FROM notifications WHERE user_id=? AND is_read=0', [$cu['id']]);
?>
<body class="site-body user-area">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
  <nav class="navbar navbar-expand-lg container">
    <?php include APP_ROOT . '/includes/partials/brand.php'; ?>
    <button class="navbar-toggler border-0" data-bs-toggle="collapse" data-bs-target="#userNav" aria-label="Menu"><i class="bi bi-list fs-2"></i></button>
    <div class="collapse navbar-collapse" id="userNav">
      <ul class="navbar-nav me-auto ms-lg-4 gap-lg-1">
        <li class="nav-item"><a class="nav-link <?= $nav === 'home' ? 'active' : '' ?>" href="<?= e(url('user')) ?>">Home</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $nav === 'services' ? 'active' : '' ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">Services</a>
          <ul class="dropdown-menu shadow-sm">
            <li><a class="dropdown-item" href="<?= e(url('user/providers')) ?>">All services</a></li>
            <li><hr class="dropdown-divider"></li>
            <?php foreach ($navServices as $s): ?><li><a class="dropdown-item" href="<?= e(url('user/providers?service=' . $s['id'])) ?>"><i class="bi <?= e(service_icon($s['service_name'])) ?> me-2 text-success"></i><?= e($s['service_name']) ?></a></li><?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link <?= $nav === 'providers' ? 'active' : '' ?>" href="<?= e(url('user/providers')) ?>">Find Providers</a></li>
        <li class="nav-item"><a class="nav-link <?= $nav === 'bookings' ? 'active' : '' ?>" href="<?= e(url('user/bookings')) ?>">Bookings</a></li>

      </ul>
      <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
        <?php include APP_ROOT . '/includes/partials/theme_toggle.php'; ?>
        <a class="icon-btn position-relative" href="<?= e(url('user/notifications')) ?>" aria-label="Notifications"><i class="bi bi-bell"></i><?php if ($unread): ?><span class="dot-badge"><?= $unread ?></span><?php endif; ?></a>
        <div class="dropdown d-none d-lg-block">
          <button class="btn btn-outline-success d-flex align-items-center gap-2" data-bs-toggle="dropdown" aria-expanded="false"><i class="bi bi-person-circle"></i> Account</button>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li><span class="dropdown-item-text small text-secondary"><?= e($cu['name']) ?></span></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?= e(url('user/profile')) ?>"><i class="bi bi-person me-2"></i>My profile</a></li>
            <li><a class="dropdown-item" href="<?= e(url('user/payments')) ?>"><i class="bi bi-credit-card me-2"></i>Payments</a></li>
            <li><a class="dropdown-item" href="<?= e(url('user/favorites')) ?>"><i class="bi bi-heart me-2"></i>Favorites</a></li>
            <li><a class="dropdown-item" href="<?= e(url('user/complaints')) ?>"><i class="bi bi-flag me-2"></i>Complaints</a></li>
            <li><a class="dropdown-item" href="<?= e(url('user/notifications')) ?>"><i class="bi bi-bell me-2"></i>Notifications</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?= e(url('logout')) ?>"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
          </ul>
        </div>

        <div class="user-mobile-account d-lg-none w-100">
          <div class="user-mobile-account-title">
            <i class="bi bi-person-circle"></i>
            <span><?= e($cu['name']) ?></span>
          </div>

          <a class="user-mobile-account-link" href="<?= e(url('user/profile')) ?>">
            <i class="bi bi-person"></i>
            <span>My profile</span>
          </a>

          <a class="user-mobile-account-link" href="<?= e(url('user/payments')) ?>">
            <i class="bi bi-credit-card"></i>
            <span>Payments</span>
          </a>

          <a class="user-mobile-account-link" href="<?= e(url('user/favorites')) ?>">
            <i class="bi bi-heart"></i>
            <span>Favorites</span>
          </a>

          <a class="user-mobile-account-link" href="<?= e(url('user/complaints')) ?>">
            <i class="bi bi-flag"></i>
            <span>Complaints</span>
          </a>

          <a class="user-mobile-account-link" href="<?= e(url('user/notifications')) ?>">
            <i class="bi bi-bell"></i>
            <span>Notifications</span>
            <?php if ($unread): ?><span class="mobile-notification-badge"><?= $unread ?></span><?php endif; ?>
          </a>
<a href="#" class="user-mobile-account-link user-mobile-theme" id="mobileThemeToggle"><i class="bi bi-moon-stars"></i><span>Theme</span></a>

          <a class="user-mobile-account-link text-danger" href="<?= e(url('logout')) ?>">
            <i class="bi bi-box-arrow-right"></i>
            <span>Logout</span>
          </a>
        </div>
      </div>
    </div>
  </nav>
</header>
<main id="main"><?php if (!empty($_SESSION['flash'])): ?><div class="container pt-3"><?php render_flashes(); ?></div><?php endif; ?>











