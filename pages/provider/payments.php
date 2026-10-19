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

// ---- filters (server-side, always limited to THIS provider's payments) ----
$statusOptions = ['Pending', 'Paid', 'Cancelled', 'Rejected', 'Verification Requested', 'Failed']; // the statuses payments.payment_status supports
$f = ['status' => get('status'), 'from' => get('from'), 'to' => get('to'), 'service' => get('service'), 'booking' => get('booking'), 'q' => get('q')];
$fErr = [];
if ($f['status'] !== '' && !in_array($f['status'], $statusOptions, true)) { $fErr[] = 'Unknown payment status ignored.'; $f['status'] = ''; }
if ($f['from'] !== '' && !valid_date($f['from'])) { $fErr[] = 'The start date is not valid and was ignored.'; $f['from'] = ''; }
if ($f['to'] !== '' && !valid_date($f['to'])) { $fErr[] = 'The end date is not valid and was ignored.'; $f['to'] = ''; }
if ($f['from'] !== '' && $f['to'] !== '' && $f['from'] > $f['to']) { $fErr[] = 'The start date is after the end date; the date filter was ignored.'; $f['from'] = $f['to'] = ''; }
if ($f['booking'] !== '' && !ctype_digit($f['booking'])) { $fErr[] = 'Booking ID must be a number and was ignored.'; $f['booking'] = ''; }
$f['service'] = mb_substr($f['service'], 0, 150);
$f['q'] = mb_substr($f['q'], 0, 100);

$base = ' FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN categories s ON s.id=b.category_id' . BOOKING_SERVICE_JOIN . 'WHERE b.provider_id=?';
$w = '';
$a = [(int)$p['id']];
if ($f['status'] !== '') { $w .= ' AND ' . PAYMENT_EFFECTIVE_STATUS_SQL . '=?'; $a[] = $f['status']; }
if ($f['from'] !== '') { $w .= ' AND DATE(pay.created_at)>=?'; $a[] = $f['from']; }
if ($f['to'] !== '') { $w .= ' AND DATE(pay.created_at)<=?'; $a[] = $f['to']; }
if ($f['service'] !== '') { $w .= ' AND ' . BOOKING_SERVICE_NAME_SQL . '=?'; $a[] = $f['service']; }
if ($f['booking'] !== '') { $w .= ' AND b.id=?'; $a[] = (int)$f['booking']; }
if ($f['q'] !== '') {
    $like = '%' . addcslashes($f['q'], '%_\\') . '%';
    $w .= ' AND (' . (ctype_digit($f['q']) ? 'b.id=? OR pay.id=? OR ' : '') . BOOKING_SERVICE_NAME_SQL . ' LIKE ? OR u.name LIKE ?)';
    if (ctype_digit($f['q'])) array_push($a, (int)$f['q'], (int)$f['q']);
    array_push($a, $like, $like);
}
$filtered = $w !== '';
$list = rows('SELECT pay.*,b.status bstatus,b.cancellation_reason,u.name user_name,s.category_name,' . BOOKING_SERVICE_NAME_SQL . ' AS service_name' . $base . $w . ' ORDER BY (pay.payment_status IN (\'Pending\',\'Rejected\') AND b.status=\'Completed\') DESC, pay.created_at DESC, pay.id DESC', $a);
// Summary of exactly the rows listed below.
$sum = row('SELECT COUNT(*) n, COALESCE(SUM(pay.payment_status=\'Paid\'),0) paid_n, COALESCE(SUM(CASE WHEN pay.payment_status=\'Paid\' THEN pay.amount END),0) paid_total, COALESCE(SUM(CASE WHEN pay.payment_status IN (\'Pending\',\'Rejected\') AND b.status=\'Completed\' THEN pay.amount END),0) due_total' . $base . $w, $a);
// Service dropdown: the exact service names that appear in this provider's own payments (never other providers').
$serviceNames = array_column(rows('SELECT DISTINCT ' . BOOKING_SERVICE_NAME_SQL . ' AS service_name' . $base . ' ORDER BY service_name', [(int)$p['id']]), 'service_name');
layout('provider', ['title' => 'Payments', 'active' => 'payments']);
?>
<div class="page-head">
    <div>
        <h1>Payments</h1>
        <p>Cash only. After a completed service, confirm the cash you received and the payment is recorded as Paid. AASRA charges 0% commission, so you receive the full amount.</p>
    </div>
</div>
<!-- <form method="get" class="card-soft mb-3" id="paymentFilters">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label small mb-1" for="f_status">Payment status</label><select id="f_status" name="status" class="form-select form-select-sm"><option value="">All</option><?php foreach ($statusOptions as $o): ?><option <?= $f['status'] === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label small mb-1" for="f_from">From (payment date)</label><input id="f_from" type="date" name="from" value="<?= e($f['from']) ?>" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label small mb-1" for="f_to">To (payment date)</label><input id="f_to" type="date" name="to" value="<?= e($f['to']) ?>" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label small mb-1" for="f_service">Service</label><select id="f_service" name="service" class="form-select form-select-sm"><option value="">All</option><?php foreach ($serviceNames as $o): ?><option <?= $f['service'] === $o ? 'selected' : '' ?>><?= e($o) ?></option><?php endforeach; ?></select></div>
        <div class="col-6 col-md-3 col-xl-1"><label class="form-label small mb-1" for="f_booking">Booking ID</label><input id="f_booking" type="number" min="1" name="booking" value="<?= e($f['booking']) ?>" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-3 col-xl-2"><label class="form-label small mb-1" for="f_q">Search</label><input id="f_q" type="search" name="q" maxlength="100" value="<?= e($f['q']) ?>" placeholder="Booking, service, client" class="form-control form-control-sm"></div>
        <div class="col-12 col-xl-1 d-flex gap-2"><button class="btn btn-success btn-sm">Filter</button><a class="btn btn-outline-secondary btn-sm" href="<?= e(url('provider/payments')) ?>">Clear</a></div>
    </div>
    <?php foreach ($fErr as $m): ?><div class="small text-danger mt-2"><?= e($m) ?></div><?php endforeach; ?>
    <div class="small text-secondary mt-2"><?= $filtered ? 'Showing' : 'Total:' ?> <b><?= (int)$sum['n'] ?></b> payment<?= (int)$sum['n'] === 1 ? '' : 's' ?> · Paid: <b><?= (int)$sum['paid_n'] ?></b> (<?= e(money($sum['paid_total'])) ?>) · Awaiting your cash confirmation: <b><?= e(money($sum['due_total'])) ?></b></div>
</form> -->
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
                    $can = $x['bstatus'] === 'Completed' && in_array($x['payment_status'], ['Pending', 'Rejected'], true); ?><tr id="booking-<?= (int)$x['booking_id'] ?>">
                        <td class="ps-4 id-cell"><?= (int)$x['booking_id'] ?></td>
                        <td><?= e($x['user_name']) ?></td>
                        <td><?= e($x['service_name']) ?><br></td>
                        <td><?= badge($x['bstatus']) ?></td>
                        <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                        <td>Cash</td>
                        <td style="min-width:16rem">
                            <?= payment_badge(payment_display_status($x)) ?>
                            <div class="small text-secondary mt-1"><?= e(payment_status_text($x, 'provider')) ?></div>
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
            <p class="mb-0 mt-2"><?= $filtered ? 'No payments match these filters.' : 'No payment records yet.' ?></p>
        </div><?php endif; ?>
</div>
<?php layout_end();
