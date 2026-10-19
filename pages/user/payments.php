<?php
// Read-only for users: a user can never change a payment amount or status. Cash is paid to the provider directly;
// the provider confirms it and it is recorded as Paid.
$u = require_role('user');
require_once APP_ROOT . '/includes/payment_helpers.php';
// Only valid Pending and Paid payments (PAYMENT_LIST_SQL): a Cancelled / Rejected booking's leftover 'Pending' row is not listed.
// Filters mirror Admin > Payments: a search box and a Pending / Paid status.
$f = ['status' => get('status'), 'q' => get('q')];
$w = 'b.user_id=? AND ' . PAYMENT_LIST_SQL;
$a = [(int)$u['id']];
if (in_array($f['status'], PAYMENT_FILTER_STATUSES, true)) {
    $w .= ' AND pay.payment_status=?';
    $a[] = $f['status'];
}
if ($f['q'] !== '') {
    $n = (int)ltrim($f['q'], '#');
    $l = '%' . $f['q'] . '%';
    $w .= ' AND (b.id=? OR p.name LIKE ? OR ' . BOOKING_SERVICE_NAME_SQL . ' LIKE ?)';
    array_push($a, $n, $l, $l);
}
$filtered = $f['status'] !== '' || $f['q'] !== '';
$list = rows('SELECT pay.*,b.status bstatus,b.cancellation_reason,p.name provider_name,' . BOOKING_SERVICE_NAME_SQL . ' AS service_name FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id' . BOOKING_SERVICE_JOIN . 'WHERE ' . $w . ' ORDER BY pay.created_at DESC, pay.id DESC', $a);
$hist = payment_histories(array_column($list, 'id'));
layout('user', ['title' => 'Payments', 'nav' => '']);
?>
<div class="container py-4">
    <h1 class="mb-1">Payments</h1>
    <p class="text-secondary mb-4">AASRA supports <b>cash payments only</b>. After the service is completed, pay the provider in cash. The provider then confirms receipt and the payment is recorded as Paid. AASRA charges <b>no commission</b>: the provider receives the full amount.</p>
    <form class="filter-bar">
        <div class="row g-2 align-items-end">
            <div class="col-md-5"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Booking ID, provider, service" value="<?= e($f['q']) ?>"></div>
            <div class="col-md-4"><label for="status">Status</label><select id="status" name="status" class="form-select">
                    <option value="">All</option><?php foreach (PAYMENT_FILTER_STATUSES as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?>
                </select></div>
            <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><a class="btn btn-outline-secondary" href="<?= e(url('user/payments')) ?>">Reset</a></div>
        </div>
    </form>
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
                    <?php foreach ($list as $x): ?><tr id="booking-<?= (int)$x['booking_id'] ?>">
                            <td class="ps-4 id-cell"><?= (int)$x['booking_id'] ?></td>
                            <td><b><?= e($x['service_name']) ?></b><br></td>
                            <td><?= e($x['provider_name']) ?></td>
                            <td><?= badge($x['bstatus']) ?></td>
                            <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                            <td>Cash</td>
                            <td class="pe-4" style="min-width:16rem">
                                <?= payment_badge(payment_display_status($x)) ?>
                                <div class="small text-secondary mt-1"><?= e(payment_status_text($x, 'user')) ?></div>
                            </td>
                        </tr><?php endforeach; ?></tbody>
            </table>
        </div>
        <?php if (!$list): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>
                <p class="mb-0 mt-2"><?= $filtered ? 'No payments match these filters.' : 'No payment records yet. A record appears here when you book a service.' ?></p>
            </div><?php endif; ?>
    </div>
</div>
<?php layout_end();
