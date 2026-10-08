<?php
$u = require_role('user');
$list = rows('SELECT pay.*,p.name provider_name,s.service_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.user_id=? ORDER BY pay.created_at DESC', [$u['id']]);
layout('user', ['title' => 'Payments', 'nav' => '']);
?>
<div class="container py-4"><h1 class="mb-1">Payment records</h1><p class="text-secondary mb-4">Payments are arranged directly with the provider. AASRA keeps a record only.</p>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Booking</th><th>Service</th><th>Provider</th><th>Amount</th><th>Method</th><th class="pe-4">Status</th></tr></thead><tbody>
<?php foreach ($list as $x): ?><tr><td class="ps-4 id-cell">#<?= (int)$x['booking_id'] ?></td><td><?= e($x['service_name']) ?></td><td><?= e($x['provider_name']) ?></td><td><?= e(money($x['amount'])) ?></td><td><?= e($x['payment_method']) ?></td><td class="pe-4"><?= badge($x['payment_status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-credit-card"></i><p class="mb-0 mt-2">No payment records yet.</p></div><?php endif; ?></div></div>
<?php layout_end();
