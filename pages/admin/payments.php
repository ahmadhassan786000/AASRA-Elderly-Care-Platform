<?php
$admin = require_role('admin');
require_once APP_ROOT . '/includes/admin_helpers.php';
require_once APP_ROOT . '/includes/payment_helpers.php';

// Verify / reject. Only an authenticated admin reaches this; the helpers re-check status and booking on the server.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $pid = (int)($_POST['payment_id'] ?? 0);
    $action = post('action');
    if ($action === 'verify') [$type, $msg] = payment_admin_verify((int)$admin['id'], $pid);
    elseif ($action === 'reject') [$type, $msg] = payment_admin_reject((int)$admin['id'], $pid, post('reason'));
    else [$type, $msg] = ['danger', 'Unknown action.'];
    flash($type, $msg);
    redirect('admin/payments' . (get('status') !== '' ? '?status=' . rawurlencode(get('status')) : ''));
}

// Summary. Only payments an Admin actually verified count as paid; pending and rejected never do.
$s = row("SELECT COUNT(*) total,
  COALESCE(SUM(payment_status='Pending'),0) pending,
  COALESCE(SUM(payment_status='Verification Requested'),0) requested,
  COALESCE(SUM(payment_status='Paid' AND verified_by IS NOT NULL),0) paid,
  COALESCE(SUM(payment_status='Rejected'),0) rejected,
  COALESCE(SUM(CASE WHEN payment_status='Paid' AND verified_by IS NOT NULL THEN amount END),0) verified_total
  FROM payments");
$commission = round((float)$s['verified_total'] * PAYMENT_COMMISSION_PERCENT / 100, 2); // 0% policy -> always 0

$f = ['status' => get('status'), 'q' => get('q')];
$w = '1=1';
$a = [];
if (in_array($f['status'], ['Pending', 'Verification Requested', 'Paid', 'Rejected'], true)) {
    $w .= ' AND pay.payment_status=?';
    $a[] = $f['status'];
}
if ($f['q'] !== '') {
    $n = (int)ltrim($f['q'], '#');
    $l = '%' . $f['q'] . '%';
    $w .= ' AND (pay.id=? OR pay.booking_id=? OR u.name LIKE ? OR p.name LIKE ? OR s.category_name LIKE ?)';
    array_push($a, $n, $n, $l, $l, $l);
}
$list = rows("SELECT pay.*,b.status bstatus,u.name user_name,p.name provider_name,s.category_name,v.name verifier_name
  FROM payments pay JOIN bookings b ON b.id=pay.booking_id JOIN users u ON u.id=b.user_id JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id LEFT JOIN users v ON v.id=pay.verified_by
  WHERE $w ORDER BY (pay.payment_status='Verification Requested') DESC, COALESCE(pay.provider_confirmed_at, pay.created_at) DESC, pay.id DESC LIMIT 500", $a);
$hist = payment_histories(array_column($list, 'id'));

layout('admin', ['title' => 'Payments', 'active' => 'payments']);
page_head('Payments', 'Cash payments confirmed by providers, waiting for your verification. No online gateway; platform commission is 0%.');
$tiles = [
    ['Total payment records', (int)$s['total'], 'bi-receipt'],
    ['Pending', (int)$s['pending'], 'bi-hourglass-split'],
    ['Awaiting verification', (int)$s['requested'], 'bi-patch-question'],
    ['Verified (Paid)', (int)$s['paid'], 'bi-patch-check'],
    ['Rejected confirmations', (int)$s['rejected'], 'bi-x-octagon'],
    ['Verified service payments', money($s['verified_total']), 'bi-cash-stack'],
    ['Platform commission earnings (0%)', money($commission), 'bi-percent'],
];
?>
<div class="row g-3 mb-4"><?php foreach ($tiles as [$l, $v, $ic]): ?><div class="col-sm-6 col-xl-3">
        <div class="stat-tile"><span class="stat-ic"><i class="bi <?= $ic ?>"></i></span>
            <div><b><?= e((string)$v) ?></b><span><?= e($l) ?></span></div>
        </div>
    </div><?php endforeach; ?></div>
<p class="text-secondary small">"Verified service payments" is money paid by clients directly to providers and verified by an Admin. It is not AASRA revenue; AASRA charges no commission.</p>

<form class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-5"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Payment or booking ID, user, provider, service" value="<?= e($f['q']) ?>"></div>
        <div class="col-md-4"><label for="status">Status</label><select id="status" name="status" class="form-select">
                <option value="">All</option><?php foreach (['Pending', 'Verification Requested', 'Paid', 'Rejected'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= e($x) ?></option><?php endforeach; ?>
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
                    <th>Provider confirmed</th>
                    <th class="pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($list as $x):
                    $ready = $x['payment_status'] === 'Verification Requested' && $x['bstatus'] === 'Completed'; ?><tr>
                        <td class="ps-4 id-cell">#<?= (int)$x['id'] ?></td>
                        <td><?= (int)$x['booking_id'] ?><br><?= badge($x['bstatus']) ?></td>
                        <td><?= e($x['user_name']) ?></td>
                        <td><?= e($x['provider_name']) ?></td>
                        <td><?= e($x['category_name']) ?></td>
                        <td class="text-nowrap"><b><?= e(money($x['amount'])) ?></b></td>
                        <td>Cash</td>
                        <td style="min-width:14rem">
                            <?= payment_badge($x['payment_status']) ?>
                            <?php if ($x['payment_status'] === 'Paid'): ?><div class="small text-secondary mt-1">Verified <?= e(payment_dt($x['verified_at'])) ?><?= $x['verifier_name'] ? ' by ' . e($x['verifier_name']) : '' ?></div><?php endif; ?>
                            <?php if ($x['payment_status'] === 'Rejected' && $x['admin_note']): ?><div class="small text-secondary mt-1">Reason: <?= e($x['admin_note']) ?></div><?php endif; ?>
                            <?= payment_history_html($hist[(int)$x['id']] ?? [], true) ?>
                        </td>
                        <td class="small"><?= e(payment_dt($x['provider_confirmed_at'])) ?></td>
                        <td class="pe-4" style="min-width:13rem">
                            <?php if ($ready): ?>
                                <form method="post" class="mb-2"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="payment_id" value="<?= (int)$x['id'] ?>">
                                    <button class="btn btn-sm btn-success" data-confirm="Verify that the provider received <?= e(money($x['amount'])) ?> in cash for booking #<?= (int)$x['booking_id'] ?>? The payment will be marked Paid.">Verify</button>
                                </form>
                                <details>
                                    <summary class="btn btn-sm btn-outline-danger">Reject</summary>
                                    <form method="post" class="mt-2"><?= csrf_field() ?><input type="hidden" name="action" value="reject"><input type="hidden" name="payment_id" value="<?= (int)$x['id'] ?>">
                                        <label class="form-label small" for="reason<?= (int)$x['id'] ?>">Reason (required)</label>
                                        <textarea id="reason<?= (int)$x['id'] ?>" name="reason" class="form-control form-control-sm mb-2" rows="2" maxlength="500" required></textarea>
                                        <button class="btn btn-sm btn-danger" data-confirm="Reject this confirmation? The provider will see your reason and can resubmit.">Reject confirmation</button>
                                    </form>
                                </details>
                            <?php else: ?><span class="text-secondary small">—</span><?php endif; ?>
                        </td>
                    </tr><?php endforeach; ?></tbody>
        </table>
    </div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-cash-coin"></i>
            <p class="mb-0 mt-2">No payments match these filters.</p>
        </div><?php endif; ?>
</div>
<?php layout_end();
