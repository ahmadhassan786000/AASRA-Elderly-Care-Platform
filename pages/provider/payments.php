<?php
$u = require_role('provider'); $p = current_provider();
$list = rows('SELECT pay.*,u.name user_name,s.service_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN services s ON s.id=b.service_id WHERE b.provider_id=? ORDER BY pay.created_at DESC', [$p['id']]);
layout('provider', ['title' => 'Payments', 'active' => 'payments']);
?>
<div class="page-head"><div><h1>Payment records</h1><p>Payments are settled directly with clients; AASRA keeps the record.</p></div></div>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Booking</th><th>Client</th><th>Service</th><th>Amount</th><th>Method</th><th class="pe-4">Status</th></tr></thead><tbody>
<?php foreach ($list as $x): ?><tr><td class="ps-4 id-cell"><?= (int)$x['booking_id'] ?></td><td><?= e($x['user_name']) ?></td><td><?= e($x['service_name']) ?></td><td><?= e(money($x['amount'])) ?></td><td><?= e($x['payment_method']) ?></td><td class="pe-4"><?= badge($x['payment_status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-credit-card"></i><p class="mb-0 mt-2">No payment records yet.</p></div><?php endif; ?></div>
<?php layout_end();
