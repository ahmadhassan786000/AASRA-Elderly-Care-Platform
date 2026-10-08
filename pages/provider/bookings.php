<?php
$u = require_role('provider'); require_once APP_ROOT . '/includes/provider_helpers.php'; $p = current_provider(); $pid = (int)$p['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); [$t, $m] = provider_booking_action($pid, $u['name']); flash($t, $m); redirect('provider/bookings'); }
$st = get('status'); $sql = "SELECT b.*,u.name user_name,s.service_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN services s ON s.id=b.service_id WHERE b.provider_id=? AND b.status<>'Pending'"; $a = [$pid];
if ($st !== '') { $sql .= ' AND b.status=?'; $a[] = $st; }
$list = rows($sql . ' ORDER BY b.booking_date DESC,b.booking_time DESC', $a);
layout('provider', ['title' => 'Bookings', 'active' => 'bookings']);
?>
<div class="page-head"><div><h1>Bookings</h1><p>Accepted, completed and closed bookings.</p></div></div>
<div class="chips mb-3"><?php foreach (['' => 'All', 'Accepted' => 'Accepted', 'Confirmed' => 'Confirmed', 'Completed' => 'Completed', 'Rejected' => 'Rejected', 'Cancelled' => 'Cancelled'] as $k => $l): ?><a class="chip <?= $st === $k ? 'active' : '' ?>" href="<?= e(url('provider/bookings' . ($k ? '?status=' . $k : ''))) ?>"><?= e($l) ?></a><?php endforeach; ?></div>
<?php booking_table($list, true, 'provider/bookings'); layout_end();
