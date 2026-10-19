<?php
$u = require_role('user');
require_once APP_ROOT . '/includes/payment_helpers.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $id = (int)($_POST['booking_id'] ?? 0);
  $b = row("SELECT * FROM bookings WHERE id=? AND user_id=? AND status IN ('Pending','Accepted','Confirmed')", [$id, $u['id']]);
  // conditional UPDATE: a double click or stale page cannot cancel (and notify) twice
  if ($b && q("UPDATE bookings SET status='Cancelled',cancellation_reason=? WHERE id=? AND user_id=? AND status IN ('Pending','Accepted','Confirmed')", ['Cancelled by user', $id, $u['id']])->affected_rows === 1) {
    notify_booking_status($id, 'Cancelled', 'user');
    payment_record_booking_outcome($id, 'booking_cancelled_by_user', 'user', (int)$u['id']);
    flash('success', 'Booking cancelled.');
  } else flash('danger', 'That booking cannot be cancelled.');
  redirect('user/bookings');
}
$st = get('status');
$sql = 'SELECT b.*,p.name provider_name,p.id pid,s.category_name,' . BOOKING_SERVICE_NAME_SQL . ' AS service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN categories s ON s.id=b.category_id' . BOOKING_SERVICE_JOIN . 'WHERE b.user_id=?';
$a = [$u['id']];
if ($st !== '') {
  $sql .= ' AND b.status=?';
  $a[] = $st;
}
$list = rows($sql . ' ORDER BY b.created_at DESC, b.id DESC', $a);
$rated = array_column(rows('SELECT booking_id FROM ratings WHERE user_id=?', [$u['id']]), 'booking_id');
layout('user', ['title' => 'My Bookings', 'nav' => 'bookings']);
?>
<div class="container py-4">
  <div class="page-head">
    <div>
      <h1>My bookings</h1>
      <p>Track requests, cancel plans and rate completed visits.</p>
    </div><a class="btn btn-success" href="<?= e(url('user/providers')) ?>"><i class="bi bi-plus-lg"></i> New booking</a>
  </div>
  <div class="chips mb-3"><?php foreach (['' => 'All', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $k => $l): ?><a class="chip <?= $st === $k ? 'active' : '' ?>" href="<?= e(url('user/bookings' . ($k ? '?status=' . $k : ''))) ?>"><?= e($l) ?></a><?php endforeach; ?></div>
  <div class="card-soft flush">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th class="ps-4">Booking</th>
            <th>Provider</th>
            <th>Service</th>
            <th>Service location</th>
            <th>When</th>
            <th>Charge</th>
            <th>Status</th>
            <th class="pe-4 text-end">Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($list as $b): ?><tr id="booking-<?= (int)$b['id'] ?>">
              <td class="ps-4 id-cell"><?= (int)$b['id'] ?></td>
              <td><a href="<?= e(url('user/providers/' . $b['pid'])) ?>"><?= e($b['provider_name']) ?></a></td>
              <td><b><?= e($b['service_name']) ?></b><br></td>
              <td style="max-width:220px"><?= booking_address_html($b['service_address']) ?></td>
              <td><?= e(fmt_date($b['booking_date'])) ?><br><small class="text-secondary"><?= e(date('g:i A', strtotime($b['booking_time']))) ?></small></td>
              <td><?= e(money($b['charges'])) ?></td>
              <td><?= badge($b['status']) ?></td>
              <td class="pe-4 text-end"><?php if (in_array($b['status'], ['Pending', 'Accepted', 'Confirmed'])): ?><form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>"><button data-confirm="Cancel this booking?" class="btn btn-sm btn-outline-danger">Cancel</button></form>
                <?php elseif ($b['status'] === 'Completed' && !in_array($b['id'], $rated)): ?><a class="btn btn-sm btn-outline-success" href="<?= e(url('user/rating?booking=' . $b['id'])) ?>">Rate</a><?php elseif ($b['status'] === 'Completed'): ?><span class="text-secondary small">Rated</span><?php endif; ?></td>
            </tr><?php endforeach; ?></tbody>
      </table>
    </div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-calendar-x"></i>
        <p class="mb-0 mt-2">No bookings yet.</p>
      </div><?php endif; ?>
  </div>
</div>
<?php layout_end();
