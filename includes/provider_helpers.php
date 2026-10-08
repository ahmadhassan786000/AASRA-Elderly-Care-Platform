<?php
function provider_completeness(array $p): array {
    $n = (int)val('SELECT COUNT(*) FROM provider_services WHERE provider_id=?', [$p['id']]);
    $checks = ['Phone number' => !empty($p['phone']), 'Location' => !empty($p['location']), 'Services' => $n > 0, 'Experience' => !empty($p['experience']),
               'Charges' => $p['charges'] !== null, 'Availability' => !empty($p['availability']), 'Bio' => !empty($p['bio']), 'Skills' => !empty($p['skills'])];
    $done = count(array_filter($checks));
    return ['pct' => (int)round($done / count($checks) * 100), 'checks' => $checks];
}
function save_provider_services(int $pid, array $ids): void {
    $valid = array_column(rows("SELECT id FROM services WHERE status='Active'"), 'id');
    $ids = array_values(array_intersect(array_map('intval', $ids), array_map('intval', $valid)));
    $pdo = db(); $pdo->beginTransaction();
    q('DELETE FROM provider_services WHERE provider_id=?', [$pid]);
    foreach ($ids as $sid) q('INSERT INTO provider_services(provider_id,service_id) VALUES(?,?)', [$pid, $sid]);
    $pdo->commit();
}
// Accept/reject/complete a booking that belongs to provider $pid. Returns a flash [type,msg].
function provider_booking_action(int $pid, string $name): array {
    $id = (int)($_POST['booking_id'] ?? 0); $action = post('action');
    $map = ['accept' => ['Accepted', ['Pending']], 'reject' => ['Rejected', ['Pending']], 'complete' => ['Completed', ['Accepted', 'Confirmed']], 'cancel' => ['Cancelled', ['Accepted', 'Confirmed']]];
    $b = row('SELECT * FROM bookings WHERE id=? AND provider_id=?', [$id, $pid]);
    if (!$b || !isset($map[$action]) || !in_array($b['status'], $map[$action][1])) return ['danger', 'That action is not available for this booking.'];
    $new = $map[$action][0];
    q('UPDATE bookings SET status=? WHERE id=?', [$new, $id]);
    notify((int)$b['user_id'], null, 'Booking ' . strtolower($new), $name . ' marked your booking on ' . fmt_date($b['booking_date']) . ' as ' . $new . '.', 'booking');
    if ($new === 'Completed') q("INSERT IGNORE INTO payments(booking_id,amount,payment_method,payment_status) VALUES(?,?,'Cash','Pending')", [$id, $b['charges']]);
    return ['success', 'Booking ' . strtolower($new) . '.'];
}
function booking_table(array $list, bool $actions, string $redirect): void { ?>
  <div class="card-soft flush"><div class="table-responsive"><table class="table mb-0">
    <thead><tr><th class="ps-4">Booking</th><th>Client</th><th>Service</th><th>When</th><th>Charge</th><th>Status</th><?php if ($actions): ?><th class="pe-4 text-end">Action</th><?php endif; ?></tr></thead><tbody>
    <?php foreach ($list as $b): ?><tr>
      <td class="ps-4 id-cell"><?= (int)$b['id'] ?></td><td><?= e($b['user_name']) ?><?php if ($b['requirements']): ?><div class="small text-secondary clamp-2" style="max-width:280px"><?= e($b['requirements']) ?></div><?php endif; ?></td><td><?= e($b['service_name']) ?></td>
      <td><?= e(fmt_date($b['booking_date'])) ?><br><small class="text-secondary"><?= e(date('g:i A', strtotime($b['booking_time']))) ?></small></td><td><?= e(money($b['charges'])) ?></td><td><?= badge($b['status']) ?></td>
      <?php if ($actions): ?><td class="pe-4 text-end text-nowrap"><form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= (int)$b['id'] ?>">
        <?php if ($b['status'] === 'Pending'): ?><button name="action" value="accept" class="btn btn-sm btn-success">Accept</button> <button name="action" value="reject" data-confirm="Reject this request?" class="btn btn-sm btn-outline-danger">Reject</button>
        <?php elseif (in_array($b['status'], ['Accepted', 'Confirmed'])): ?><button name="action" value="complete" class="btn btn-sm btn-success">Mark completed</button> <button name="action" value="cancel" data-confirm="Cancel this booking?" class="btn btn-sm btn-outline-danger">Cancel</button><?php endif; ?></form></td><?php endif; ?>
    </tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$list): ?><div class="empty-state"><i class="bi bi-inbox"></i><p class="mb-0 mt-2">Nothing here yet.</p></div><?php endif; ?></div>
<?php }
