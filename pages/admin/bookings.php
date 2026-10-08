<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['q' => get('q'), 'status' => get('status')]; $w = '1=1'; $a = [];
if ($f['q'] !== '') { $w .= ' AND (u.name LIKE ? OR p.name LIKE ? OR s.service_name LIKE ?)'; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l, $l); }
if (in_array($f['status'], ['Pending', 'Accepted', 'Rejected', 'Confirmed', 'Completed', 'Cancelled'])) { $w .= ' AND b.status=?'; $a[] = $f['status']; }
$list = rows("SELECT b.*,u.name user_name,p.name provider_name,s.service_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE $w ORDER BY b.created_at DESC, b.id DESC", $a);
layout('admin', ['title' => 'Bookings', 'active' => 'bookings']); page_head('Bookings', 'All bookings across the platform.');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-md-6"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="User, provider or service" value="<?= e($f['q']) ?>"></div>
  <div class="col-md-3"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Pending', 'Accepted', 'Confirmed', 'Completed', 'Rejected', 'Cancelled'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/bookings') ?></div></div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>User</th><th>Provider</th><th>Service</th><th>Date</th><th>Value</th><th class="pe-4">Status</th></tr></thead><tbody>
<?php foreach ($list as $b): ?><tr><td class="ps-4 id-cell"><?= (int)$b['id'] ?></td><td><?= e($b['user_name']) ?></td><td><?= e($b['provider_name']) ?></td><td><?= e($b['service_name']) ?></td><td class="text-nowrap"><?= e(fmt_date($b['booking_date'])) ?></td><td><?= e(money($b['charges'])) ?></td><td class="pe-4"><?= badge($b['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-calendar-x"></i><p class="mb-0 mt-2">No bookings found.</p></div><?php endif; ?></div>
<?php layout_end();
