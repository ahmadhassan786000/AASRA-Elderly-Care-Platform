<?php
include __DIR__ . '/head.php';

$cu = current_user();
$active = $active ?? '';

$items = [
    ['dashboard', 'admin', 'bi-speedometer2', 'Dashboard'],
    ['users', 'admin/users', 'bi-people', 'Users'],
    ['providers', 'admin/providers', 'bi-person-badge', 'Providers'],
    ['verification', 'admin/verification', 'bi-patch-check', 'Verification'],
    ['services', 'admin/services', 'bi-grid-3x3-gap', 'Services'],
    ['bookings', 'admin/bookings', 'bi-calendar-check', 'Bookings'],
    ['payments', 'admin/payments', 'bi-credit-card', 'Payments'],
    ['complaints', 'admin/complaints', 'bi-exclamation-triangle', 'Complaints'],
];

$pendingV = (int) val(
    "SELECT COUNT(*) FROM providers WHERE verification_status='Pending'"
);
?>

<body class="app-body admin-area">
<div class="app-shell">

    <button
        type="button"
        class="provider-mobile-menu"
        id="adminMobileMenu"
        aria-label="Open admin menu"
        aria-controls="sidebar"
        aria-expanded="false">
        <i class="bi bi-list"></i>
    </button>

    <div class="provider-sidebar-overlay" id="adminSidebarOverlay"></div>

    <aside class="sidebar sidebar-admin" id="sidebar" tabindex="-1">

        <div class="sidebar-head">
            <div class="provider-sidebar-profile">
                <?= avatar($cu, 'avatar-sm') ?>

                <div>
                    <span class="provider-sidebar-name">
                        <?= e($cu['name'] ?? 'Administrator') ?>
                    </span>
                    <span class="role-pill">Administrator</span>
                </div>
            </div>
        </div>

        <nav class="sidebar-nav" aria-label="Admin navigation">
            <?php foreach ($items as [$k, $path, $ic, $label]): ?>
                <a
                    href="<?= e(url($path)) ?>"
                    class="side-link <?= $active === $k ? 'active' : '' ?>"
                    <?= $active === $k ? 'aria-current="page"' : '' ?>>

                    <i class="bi <?= e($ic) ?>"></i>
                    <span><?= e($label) ?></span>

                    <?php if ($k === 'verification' && $pendingV > 0): ?>
                        <span class="side-count"><?= $pendingV ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>

            <div class="side-sep"></div>

            <a href="<?= e(url('logout')) ?>" class="side-link">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>

            <a
                href="<?= e(url('')) ?>"
                target="_blank"
                rel="noopener"
                class="side-link">
                <i class="bi bi-box-arrow-up-right"></i>
                <span>View Website</span>
            </a>
        </nav>
    </aside>

    <main class="app-main">
        <div class="app-content">
            <?php render_flashes(); ?>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const sidebar = document.getElementById('sidebar');
                const menu = document.getElementById('adminMobileMenu');
                const overlay = document.getElementById('adminSidebarOverlay');

                if (!sidebar || !menu || !overlay) return;

                function openSidebar() {
                    sidebar.classList.add('mobile-open');
                    overlay.classList.add('show');
                    menu.setAttribute('aria-expanded', 'true');
                }

                function closeSidebar() {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('show');
                    menu.setAttribute('aria-expanded', 'false');
                }

                menu.addEventListener('click', function () {
                    if (sidebar.classList.contains('mobile-open')) {
                        closeSidebar();
                    } else {
                        openSidebar();
                    }
                });

                overlay.addEventListener('click', closeSidebar);

                sidebar.querySelectorAll('.side-link').forEach(function (link) {
                    link.addEventListener('click', function () {
                        if (window.innerWidth <= 991) closeSidebar();
                    });
                });

                window.addEventListener('resize', function () {
                    if (window.innerWidth > 991) closeSidebar();
                });
            });
            </script>
