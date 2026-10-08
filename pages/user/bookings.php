<?php
$u = require_role('user');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $id = (int)($_POST['booking_id'] ?? 0);
    $b = row("SELECT * FROM bookings WHERE id=? AND user_id=? AND status IN ('Pending','Accepted','Confirmed')", [$id, $u['id']]);
    if ($b) {
        q("UPDATE bookings SET status='Cancelled',cancellation_reason=? WHERE id=?", ['Cancelled by user', $id]);
        notify(null, (int)$b['provider_id'], 'Booking cancelled', $u['name'] . ' cancelled the booking on ' . fmt_date($b['booking_date']) . '.', 'booking');
        flash('success', 'Booking cancelled.');
    } else flash('danger', 'That booking cannot be cancelled.');
    redirect('user/bookings');
}
$st = get('status');
$sql = 'SELECT b.*,p.name provider_name,p.id pid,s.service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.user_id=?';
$a = [$u['id']]; if ($st !== '') { $sql .= ' AND b.status=?'; $a[] = $st; }
$list = rows($sql . ' ORDER BY b.booking_date DESC,b.booking_time DESC', $a);
$rated = array_column(rows('SELECT booking_id FROM ratings WHERE user_id=?', [$u['id']]), 'booking_id');
layout('user', ['title' => 'My Bookings', 'nav' => 'bookings']);
?>
<div class="container py-4">
  <div class="page-head"><div><h1>My bookings</h1><p>Track requests, cancel plans and rate completed visits.</p></div><a class="btn btn-success" href="<?= e(url('user/providers')) ?>"><i class="bi bi-plus-lg"></i> New booking</a></div>
  <div class="chips mb-3"><?php foreach (['' => 'All', 'Pending' => 'Pending', 'Accepted' => 'Accepted', 'Confirmed' => 'Confirmed', 'Completed' => 'Completed', 'Cancelled' => 'Cancelled'] as $k => $l): ?><a class="chip <?= $st === $k ? 'active' : '' ?>" href="<?= e(url('user/bookings' . ($k ? '?status=' . $k : ''))) ?>"><?= e($l) ?></a><?php endforeach; ?></div>
  <div class="card-soft flush"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th class="ps-4">Booking</th><th>Provider</th><th>Service</th><th>When</th><th>Charge</th><th>Status</th><th class="pe-4 text-end">Action</th></tr></thead><tbody>
    <?php foreach ($list as $b): ?><tr>
      <td class="ps-4 id-cell">#<?= (int)$b['id'] ?></td><td><a href="<?= e(url('user/providers/' . $b['pid'])) ?>"><?= e($b['provider_name']) ?></a></td><td><?= e($b['service_name']) ?></td>
      <td><?= e(fmt_date($b['booking_date'])) ?><br><small class="text-secondary"><?= e(date('g:i A', strtotime($b['booking_time']))) ?></small></td><td><?= e(money($b['charges'])) ?></td><td><?= badge($b['status']) ?></td>
      <td class="pe-4 text-end"><?php if (in_array($b['status'], ['Pending', 'Accepted', 'Confirmed'])): ?><form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>"><button data-confirm="Cancel this booking?" class="btn btn-sm btn-outline-danger">Cancel</button></form>
      <?php elseif ($b['status'] === 'Completed' && !in_array($b['id'], $rated)): ?><a class="btn btn-sm btn-outline-success" href="<?= e(url('user/rating?booking=' . $b['id'])) ?>">Rate</a><?php elseif ($b['status'] === 'Completed'): ?><span class="text-secondary small">Rated</span><?php endif; ?></td>
    </tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-calendar-x"></i><p class="mb-0 mt-2">No bookings yet.</p></div><?php endif; ?></div>
</div>
<?php layout_end();
