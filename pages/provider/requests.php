<?php
$u = require_role('provider'); require_once APP_ROOT . '/includes/provider_helpers.php'; $p = current_provider(); $pid = (int)$p['id'];
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); [$t, $m] = provider_booking_action($pid, $u['name']); flash($t, $m); redirect('provider/requests'); }
$list = rows("SELECT b.*,u.name user_name,s.service_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN services s ON s.id=b.service_id WHERE b.provider_id=? AND b.status='Pending' ORDER BY b.booking_date,b.booking_time", [$pid]);
layout('provider', ['title' => 'Booking Requests', 'active' => 'requests']);
?>
<div class="page-head"><div><h1>Booking requests</h1><p>New requests waiting for your answer.</p></div></div>
<?php booking_table($list, true, 'provider/requests'); layout_end();
