<?php
include __DIR__ . '/head.php';
$cu = current_user(); $pv = current_provider(); $active = $active ?? '';
$pendingReq = (int)val("SELECT COUNT(*) FROM bookings WHERE provider_id=? AND status='Pending'", [$pv['id']]);
$unread = (int)val('SELECT COUNT(*) FROM notifications WHERE provider_id=? AND is_read=0', [$pv['id']]);
$items = [
  ['dashboard','provider','bi-speedometer2','Dashboard',0],['profile','provider/profile','bi-person-vcard','Profile',0],
  ['services','provider/services','bi-grid','Services',0],
  ['requests','provider/requests','bi-inbox','Booking Requests',$pendingReq],['bookings','provider/bookings','bi-calendar-check','Bookings',0],
  ['reviews','provider/reviews','bi-star','Reviews',0],['verification','provider/verification','bi-patch-check','Verification',0],
  ['payments','provider/payments','bi-credit-card','Payments',0],['notifications','provider/notifications','bi-bell','Notifications',$unread],
];
?>
<body class="app-body provider-area">
<div class="app-shell">
  <button type="button" class="provider-mobile-menu" id="providerMobileMenu" aria-label="Open menu" aria-controls="sidebar" aria-expanded="false">
    <i class="bi bi-list"></i>
  </button>
  <div class="provider-sidebar-overlay" id="providerSidebarOverlay"></div>
  <aside class="sidebar sidebar-provider" id="sidebar" tabindex="-1">
    <div class="sidebar-head">
      <div class="provider-sidebar-profile"><?= avatar($pv, 'avatar-sm') ?><div><span class="provider-sidebar-name"><?= e($cu['name']) ?></span><span class="role-pill">Provider</span></div>
          </div>
    </div>
    <nav class="sidebar-nav">
      <?php foreach ($items as [$k, $path, $ic, $label, $count]): ?>
        <a href="<?= e(url($path)) ?>" class="side-link <?= $active === $k ? 'active' : '' ?>"><i class="bi <?= e($ic) ?>"></i><span><?= e($label) ?></span><?php if ($count): ?><span class="side-count"><?= $count ?></span><?php endif; ?></a>
      <?php endforeach; ?>
      <div class="side-sep"></div>
      <a href="<?= e(url('logout')) ?>" class="side-link"><i class="bi bi-box-arrow-right"></i><span>Logout</span></a>
      <a href="<?= e(url('')) ?>" target="_blank" rel="noopener" class="side-link"><i class="bi bi-box-arrow-up-right"></i><span>View Website</span></a>
    </nav>
  </aside>
  <div class="app-main">
    
    <div class="app-content">
      <?php render_flashes(); ?>











<script>
document.addEventListener("DOMContentLoaded", function () {
    const sidebar = document.getElementById("sidebar");
    const menu = document.getElementById("providerMobileMenu");
    const overlay = document.getElementById("providerSidebarOverlay");

    if (!sidebar || !menu || !overlay) {
        return;
    }

    function openSidebar() {
        sidebar.classList.add("mobile-open");
        overlay.classList.add("show");
        menu.setAttribute("aria-expanded", "true");
    }

    function closeSidebar() {
        sidebar.classList.remove("mobile-open");
        overlay.classList.remove("show");
        menu.setAttribute("aria-expanded", "false");
    }

    menu.addEventListener("click", function (event) {
        event.preventDefault();

        if (sidebar.classList.contains("mobile-open")) {
            closeSidebar();
        } else {
            openSidebar();
        }
    });

    overlay.addEventListener("click", function () {
        closeSidebar();
    });

    sidebar.querySelectorAll(".side-link").forEach(function (link) {
        link.addEventListener("click", function () {
            if (window.innerWidth <= 991) {
                closeSidebar();
            }
        });
    });

    window.addEventListener("resize", function () {
        if (window.innerWidth > 991) {
            closeSidebar();
        }
    });
});
</script>





