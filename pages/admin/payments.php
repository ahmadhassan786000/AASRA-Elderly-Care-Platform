<?php
$admin = require_role('admin');
require_once APP_ROOT . '/includes/admin_helpers.php';
require_once APP_ROOT . '/includes/payment_helpers.php';

// Read-only page: a provider's cash-received confirmation is recorded automatically and shown in the History column.
// The Admin does not verify or reject it, so this page has no POST actions.

// Summary. A payment is Paid once the provider has confirmed the cash was received.
$s = row("SELECT COUNT(*) total,
  COALESCE(SUM(" . PAYMENT_EFFECTIVE_STATUS_SQL . "='Pending'),0) pending,
  COALESCE(SUM(pay.payment_status='Paid'),0) paid,
  COALESCE(SUM(CASE WHEN pay.payment_status='Paid' THEN pay.amount END),0) paid_total
  FROM payments pay JOIN bookings b ON b.id=pay.booking_id");
$commission = round((float)$s['paid_total'] * PAYMENT_COMMISSION_PERCENT / 100, 2); // 0% policy -> always 0

$f = ['status' => get('status'), 'q' => get('q')];
$w = '1=1';
$a = [];
// Same rule as the Status column (payment_display_status): a cancelled/rejected booking counts as Cancelled/Rejected.
if (in_array($f['status'], ['Pending', 'Paid', 'Cancelled', 'Rejected'], true)) {
    $w .= ' AND ' . PAYMENT_EFFECTIVE_STATUS_SQL . '=?';
    $a[] = $f['status'];
}
if ($f['q'] !== '') {
    $n = (int)ltrim($f['q'], '#');
    $l = '%' . $f['q'] . '%';
    $w .= ' AND (pay.id=? OR pay.booking_id=? OR u.name LIKE ? OR p.name LIKE ? OR s.category_name LIKE ? OR ' . BOOKING_SERVICE_NAME_SQL . ' LIKE ?)';
    array_push($a, $n, $n, $l, $l, $l, $l);
}
$list = rows("SELECT pay.*,b.status bstatus,b.cancellation_reason,u.name user_name,p.name provider_name,s.category_name," . BOOKING_SERVICE_NAME_SQL . " AS service_name
  FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id" . BOOKING_SERVICE_JOIN . "
  WHERE $w ORDER BY COALESCE(pay.provider_confirmed_at, pay.created_at) DESC, pay.id DESC LIMIT 500", $a);
$hist = payment_histories(array_column($list, 'id'));

layout('admin', ['title' => 'Payments', 'active' => 'payments']);
page_head('Payments', 'Cash payments confirmed by providers. This is a read-only record; no Admin verification is needed. No online gateway; platform commission is 0%.');
$tiles = [
    ['Total payment records', (int)$s['total'], 'bi-receipt'],
    ['Pending', (int)$s['pending'], 'bi-hourglass-split'],
    ['Cash received (Paid)', (int)$s['paid'], 'bi-patch-check'],
    ['Cash received total', money($s['paid_total']), 'bi-cash-stack'],
];
?>
<div class="row g-3 mb-4"><?php foreach ($tiles as [$l, $v, $ic]): ?><div class="col-sm-6 col-xl-3">
        <div class="stat-tile"><span class="stat-ic"><i class="bi <?= $ic ?>"></i></span>
            <div><b><?= e((string)$v) ?></b><span><?= e($l) ?></span></div>
        </div>
    </div><?php endforeach; ?></div>
<p class="text-secondary small">"Cash received total" is money paid by clients directly to providers and confirmed by the provider. It is not AASRA revenue; AASRA charges no commission.</p>

<form class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-5"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Payment or booking ID, user, provider, service" value="<?= e($f['q']) ?>"></div>
        <div class="col-md-4"><label for="status">Status</label><select id="status" name="status" class="form-select">
                <option value="">All</option><?php foreach (['Pending', 'Paid', 'Cancelled', 'Rejected'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?>
            </select></div>
        <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/payments') ?></div>
    </div>
</form>

<div class="card-soft flush">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="ps-4">Payment</th>
                    <th>Booking</th>
                    <th>User</th>
                    <th>Provider</th>
                    <th>Service</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th class="pe-4">History</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $x): ?><tr>
                        <td class="ps-4 id-cell"><?= (int)$x['id'] ?></td>
                        <td><?= (int)$x['booking_id'] ?></td>
                        <td><?= e($x['user_name']) ?></td>
                        <td><?= e($x['provider_name']) ?></td>
                        <td><?= e($x['service_name']) ?><br></td>
                        <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                        <td>Cash</td>
                        <?php $ds = payment_display_status($x); ?>
                        <td><?= payment_badge($ds) ?><?php if ($ds === $x['payment_status'] && $x['bstatus'] !== 'Completed'): ?><?php endif; ?></td>
                        <td class="pe-4"><?= payment_admin_history_html($x, $hist[(int)$x['id']] ?? []) ?></td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>
            <p class="mb-0 mt-2">No payments match these filters.</p>
        </div><?php endif; ?>
</div>
<?php layout_end();
