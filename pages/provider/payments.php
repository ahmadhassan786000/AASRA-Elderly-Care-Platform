<?php
$u = require_role('provider');
$p = current_provider();
require_once APP_ROOT . '/includes/payment_helpers.php';

// The provider can only submit a cash-received confirmation for their OWN completed booking. They can never set "Paid".
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (post('action') === 'confirm_cash') {
        [$type, $msg] = payment_provider_confirm((int)$p['id'], (int)($_POST['payment_id'] ?? 0), (int)$u['id']);
        flash($type, $msg);
    } else flash('danger', 'Unknown action.');
    redirect('provider/payments');
}

$list = rows('SELECT pay.*,b.status bstatus,u.name user_name,s.category_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN categories s ON s.id=b.category_id WHERE b.provider_id=? ORDER BY (pay.payment_status IN (\'Pending\',\'Rejected\') AND b.status=\'Completed\') DESC, pay.created_at DESC, pay.id DESC', [$p['id']]);
$hist = payment_histories(array_column($list, 'id'));
layout('provider', ['title' => 'Payments', 'active' => 'payments']);
?>
<div class="page-head">
    <div>
        <h1>Payments</h1>
        <p>Cash only. After a completed service, confirm the cash you received; an Admin then verifies it. AASRA charges 0% commission, so you receive the full amount.</p>
    </div>
</div>
<div class="card-soft flush">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Booking</th>
                    <th>Client</th>
                    <th>Service</th>
                    <th>Service status</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Payment</th>
                    <th class="pe-4 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $x):
                    $can = $x['bstatus'] === 'Completed' && in_array($x['payment_status'], ['Pending', 'Rejected'], true); ?><tr>
                        <td class="ps-4 id-cell">#<?= (int)$x['booking_id'] ?></td>
                        <td><?= e($x['user_name']) ?></td>
                        <td><?= e($x['category_name']) ?></td>
                        <td><?= badge($x['bstatus']) ?></td>
                        <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                        <td>Cash</td>
                        <td style="min-width:16rem">
                            <?= payment_badge($x['payment_status']) ?>
                            <div class="small text-secondary mt-1"><?= e(payment_status_text($x, 'provider')) ?></div>
                            <?= payment_history_html($hist[(int)$x['id']] ?? [], false) ?>
                        </td>
                        <td class="pe-4 text-end">
                            <?php if ($can): ?><form method="post" class="d-inline"><?= csrf_field() ?>
                                    <input type="hidden" name="action" value="confirm_cash"><input type="hidden" name="payment_id" value="<?= (int)$x['id'] ?>">
                                    <button class="btn btn-sm btn-success" data-confirm="Confirm that you received <?= e(money($x['amount'])) ?> in cash for booking #<?= (int)$x['booking_id'] ?>?"><?= $x['payment_status'] === 'Rejected' ? 'Resubmit confirmation' : 'Confirm cash received' ?></button>
                                </form>
                            <?php else: ?><span class="text-secondary small">—</span><?php endif; ?>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>
            <p class="mb-0 mt-2">No payment records yet.</p>
        </div><?php endif; ?>
</div>
<?php layout_end();
