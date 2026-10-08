<?php require_once __DIR__ . '/_base.php';
$title = 'User Dashboard';
include __DIR__ . '/../includes/header.php'; ?><div class="dashboard-shell"><?php include __DIR__ . '/../includes/sidebar.php'; ?><section class="content-area"><?php $uid = $u['id'];
                                                                                                                                                                                                                                $counts = [];
                                                                                                                                                                                                                                foreach (['Pending', 'Accepted', 'Confirmed', 'Completed'] as $st) {
                                                                                                                                                                                                                                    $s = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE user_id=? AND status=?');
                                                                                                                                                                                                                                    $s->execute([$uid, $st]);
                                                                                                                                                                                                                                    $counts[$st] = $s->fetchColumn();
                                                                                                                                                                                                                                }
                                                                                                                                                                                                                                $recent = $pdo->prepare("SELECT b.*,p.name provider_name,s.service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.user_id=? ORDER BY b.created_at DESC LIMIT 5");
                                                                                                                                                                                                                                $recent->execute([$uid]); ?><h2>Welcome, <?= e($u['name']) ?></h2>
        <p class="text-secondary">Manage assistance, bookings and support from one place.</p>
        <div class="row g-3 mb-4"><?php foreach (['Pending', 'Accepted', 'Confirmed', 'Completed'] as $x): ?><div class="col-md-3">
                    <div class="stat-card">
                        <div class="text-secondary"><?= e($x) ?> bookings</div>
                        <div class="display-6 fw-bold"><?= $counts[$x] ?></div>
                    </div>
                </div><?php endforeach; ?></div>
        <div class="service-card p-4">
            <div class="d-flex justify-content-between">
                <h4>Recent bookings</h4><a href="bookings.php">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Provider</th>
                            <th>Service</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody><?php foreach ($recent->fetchAll() as $b): ?><tr>
                                <td>#<?= $b['id'] ?></td>
                                <td><?= e($b['provider_name']) ?></td>
                                <td><?= e($b['service_name']) ?></td>
                                <td><?= e($b['booking_date']) ?></td>
                                <td><?= badge($b['status']) ?></td>
                            </tr><?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
    </section>
</div><?php include __DIR__ . '/../includes/footer.php'; ?>