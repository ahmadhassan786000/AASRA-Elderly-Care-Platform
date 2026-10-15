<?php
// Read-only for users: a user can never change a payment amount or status. Cash is paid to the provider directly;
// the provider confirms it and it is recorded as Paid.
$u = require_role('user');
require_once APP_ROOT . '/includes/payment_helpers.php';
$list = rows('SELECT pay.*,b.status bstatus,p.name provider_name,s.category_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id WHERE b.user_id=? ORDER BY pay.created_at DESC, pay.id DESC', [$u['id']]);
$hist = payment_histories(array_column($list, 'id'));
layout('user', ['title' => 'Payments', 'nav' => '']);
?>
<div class="container py-4">
    <h1 class="mb-1">Payments</h1>
    <p class="text-secondary mb-4">AASRA supports <b>cash payments only</b>. After the service is completed, pay the provider in cash. The provider then confirms receipt and the payment is recorded as Paid. AASRA charges <b>no commission</b>: the provider receives the full amount.</p>
    <div class="card-soft flush">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="ps-4">Booking</th>
                        <th>Service</th>
                        <th>Provider</th>
                        <th>Service status</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th class="pe-4">Payment</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($list as $x): ?><tr>
                            <td class="ps-4 id-cell"><?= (int)$x['booking_id'] ?></td>
                            <td><?= e($x['category_name']) ?></td>
                            <td><?= e($x['provider_name']) ?></td>
                            <td><?= badge($x['bstatus']) ?></td>
                            <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                            <td>Cash</td>
                            <td class="pe-4" style="min-width:16rem">
                                <?= payment_badge($x['payment_status']) ?>
                                <div class="small text-secondary mt-1"><?= e(payment_status_text($x, 'user')) ?></div>
                                <?= payment_history_html($hist[(int)$x['id']] ?? [], false) ?>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <?php if (!$list): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>
                <p class="mb-0 mt-2">No payment records yet. A record appears here when you book a service.</p>
            </div><?php endif; ?>
    </div>
</div>
<?php layout_end();
