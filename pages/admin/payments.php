<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$list = rows('SELECT pay.*,u.name user_name,p.name provider_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN providers p ON p.id=b.provider_id ORDER BY pay.created_at DESC, pay.id DESC');
layout('admin', ['title' => 'Payments', 'active' => 'payments']); page_head('Payments', 'Payment records (no real gateway is connected).');
?>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Booking</th><th>User</th><th>Provider</th><th>Amount</th><th>Method</th><th class="pe-4">Status</th></tr></thead><tbody>
<?php foreach ($list as $x): ?><tr><td class="ps-4 id-cell"><?= (int)$x['booking_id'] ?></td><td><?= e($x['user_name']) ?></td><td><?= e($x['provider_name']) ?></td><td><?= e(money($x['amount'])) ?></td><td><?= e($x['payment_method']) ?></td><td class="pe-4"><?= badge($x['payment_status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-credit-card"></i><p class="mb-0 mt-2">No payments yet.</p></div><?php endif; ?></div>
<?php layout_end();
