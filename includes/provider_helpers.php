<?php
function provider_completeness(array $p): array {
    $n = (int)val('SELECT COUNT(*) FROM provider_services WHERE provider_id=?', [$p['id']]);
    $checks = ['Phone number' => !empty($p['phone']), 'Location' => !empty($p['location']), 'Services' => $n > 0,
               'Bio' => !empty($p['bio']), 'Skills' => !empty($p['skills'])];
    $done = count(array_filter($checks));
    return ['pct' => (int)round($done / count($checks) * 100), 'checks' => $checks];
}
/* ---------- provider service offerings ----------
   A provider may create many offerings, but each one must sit under a service type the administrator created and
   left Active. provider_services (the "listed under" table used by search and booking) is kept in step: it gains a
   row with a provider's first offering of a type and loses it when the last one of that type is deleted. */
const OFFERING_MAX_PER_PROVIDER = 50;

function provider_offerings(int $pid): array {
    return rows('SELECT o.*,s.service_name,s.status service_status FROM provider_offerings o JOIN services s ON s.id=o.service_id WHERE o.provider_id=? ORDER BY s.service_name,o.title', [$pid]);
}
// One offering, only if it belongs to provider $pid (the id always comes from the logged-in session, never the browser).
function provider_offering(int $pid, int $id) {
    return row('SELECT o.*,s.service_name,s.status service_status FROM provider_offerings o JOIN services s ON s.id=o.service_id WHERE o.id=? AND o.provider_id=?', [$id, $pid]);
}
// Validates the offering form. Returns [cleanValues, errors]. $pid/$ignoreId are used for the duplicate-title rule;
// $existing is the offering being edited (its service type is fixed), or null when creating.
function offering_validate(int $pid, ?array $existing): array {
    $sid = $existing ? (int)$existing['service_id'] : (is_array($_POST['service_id'] ?? null) ? 0 : (int)($_POST['service_id'] ?? 0));
    $c = ['service_id' => $sid, 'title' => post('title'), 'description' => post('description'), 'price' => post('price'),
          'availability' => post('availability'), 'experience' => post('experience')];
    $err = [];
    if (!$existing && !row("SELECT id FROM services WHERE id=? AND status='Active'", [$sid])) $err['service_id'] = 'Please choose one of the available services.';
    if (mb_strlen($c['title']) < 3 || mb_strlen($c['title']) > 150) $err['title'] = 'Please enter a title (3–150 characters).';
    if (mb_strlen($c['description']) > 1000) $err['description'] = 'Description must not exceed 1,000 characters.';
    if (!preg_match('/^\d{1,7}(\.\d{1,2})?$/', $c['price']) || (float)$c['price'] > 1000000) $err['price'] = 'Price must be a number between 0 and 1,000,000 PKR.';
    if (mb_strlen($c['availability']) > 255) $err['availability'] = 'Availability must not exceed 255 characters.';
    if (mb_strlen($c['experience']) > 120) $err['experience'] = 'Experience must not exceed 120 characters.';
    if (!isset($err['title']) && !isset($err['service_id']) &&
        val('SELECT COUNT(*) FROM provider_offerings WHERE provider_id=? AND service_id=? AND title=? AND id<>?', [$pid, $sid, $c['title'], $existing ? (int)$existing['id'] : 0]))
        $err['title'] = 'You already have an offering with this title under this service.';
    if (!$existing && !$err && (int)val('SELECT COUNT(*) FROM provider_offerings WHERE provider_id=?', [$pid]) >= OFFERING_MAX_PER_PROVIDER)
        $err['general'] = 'You have reached the limit of ' . OFFERING_MAX_PER_PROVIDER . ' offerings. Delete one before adding another.';
    return [$c, $err];
}
function offering_create(int $pid, array $c): int {
    db_begin();
    try {
        q('INSERT IGNORE INTO provider_services(provider_id,service_id) VALUES(?,?)', [$pid, $c['service_id']]);
        $st = q('INSERT INTO provider_offerings(provider_id,service_id,title,description,price,availability,experience) VALUES(?,?,?,?,?,?,?)',
            [$pid, $c['service_id'], $c['title'], $c['description'] === '' ? null : $c['description'], $c['price'],
             $c['availability'] === '' ? null : $c['availability'], $c['experience'] === '' ? null : $c['experience']]);
        $id = (int)mysqli_stmt_insert_id($st);
        db_commit();
        return $id;
    } catch (Throwable $e) { db_rollback(); throw $e; }
}
function offering_update(int $pid, int $id, array $c): void {
    q('UPDATE provider_offerings SET title=?,description=?,price=?,availability=?,experience=? WHERE id=? AND provider_id=?',
        [$c['title'], $c['description'] === '' ? null : $c['description'], $c['price'],
         $c['availability'] === '' ? null : $c['availability'], $c['experience'] === '' ? null : $c['experience'], $id, $pid]);
}
function offering_delete(int $pid, int $id): bool {
    $o = provider_offering($pid, $id);
    if (!$o) return false;
    db_begin();
    try {
        q('DELETE FROM provider_offerings WHERE id=? AND provider_id=?', [$id, $pid]);
        if (!val('SELECT COUNT(*) FROM provider_offerings WHERE provider_id=? AND service_id=?', [$pid, $o['service_id']]))
            q('DELETE FROM provider_services WHERE provider_id=? AND service_id=?', [$pid, $o['service_id']]);
        db_commit();
    } catch (Throwable $e) { db_rollback(); throw $e; }
    return true;
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
