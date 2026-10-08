<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$x = row('SELECT * FROM users WHERE id=?', [(int)get('id')]); if (!$x) { show_error_page(404); exit; }
$bk = rows('SELECT b.*,p.name provider_name,s.service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.user_id=? ORDER BY b.booking_date DESC LIMIT 10', [$x['id']]);
$pv = $x['role'] === 'provider' ? row('SELECT id FROM providers WHERE user_id=?', [$x['id']]) : null;
layout('admin', ['title' => $x['name'], 'active' => 'users']);
page_head($x['name'], 'Account details', '<div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="' . e(url('admin/users')) . '">Back</a>' . ($x['role'] !== 'admin' ? '<a class="btn btn-success" href="' . e(url('admin/users/' . $x['id'] . '/edit')) . '">Edit</a>' : '') . '</div>');
?>
<div class="row g-4"><div class="col-lg-4"><div class="card-soft"><dl class="mb-0">
  <dt>ID</dt><dd><?= (int)$x['id'] ?></dd><dt>Email</dt><dd><?= e($x['email']) ?></dd><dt>Phone</dt><dd><?= e($x['phone'] ?: '—') ?></dd><dt>Address</dt><dd><?= e($x['address'] ?: '—') ?></dd>
  <dt>Account type</dt><dd><span class="tag"><?= e(ucfirst($x['role'])) ?></span></dd><dt>Status</dt><dd><?= badge($x['status']) ?></dd><dt>Joined</dt><dd class="mb-0"><?= e(fmt_date($x['created_at'])) ?></dd></dl>
  <?php if ($pv): ?><a class="btn btn-outline-success w-100 mt-3" href="<?= e(url('admin/providers/' . $pv['id'])) ?>">Open provider profile</a><?php endif; ?></div></div>
<div class="col-lg-8"><div class="card-soft flush"><div class="card-head"><h2>Recent bookings</h2></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Provider</th><th>Service</th><th>Date</th><th class="pe-4">Status</th></tr></thead><tbody>
<?php foreach ($bk as $b): ?><tr><td class="ps-4 id-cell"><?= (int)$b['id'] ?></td><td><?= e($b['provider_name']) ?></td><td><?= e($b['service_name']) ?></td><td><?= e(fmt_date($b['booking_date'])) ?></td><td class="pe-4"><?= badge($b['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$bk): ?><div class="empty-state"><p class="mb-0">No bookings.</p></div><?php endif; ?></div></div></div>
<?php layout_end();
