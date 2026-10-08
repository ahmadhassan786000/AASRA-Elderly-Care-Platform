<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$s = [];
foreach (['users' => "SELECT COUNT(*) FROM users WHERE role='user'", 'providers' => 'SELECT COUNT(*) FROM providers', 'approved' => "SELECT COUNT(*) FROM providers WHERE verification_status='Approved'",
          'pending' => "SELECT COUNT(*) FROM providers WHERE verification_status='Pending'", 'bookings' => 'SELECT COUNT(*) FROM bookings', 'completed' => "SELECT COUNT(*) FROM bookings WHERE status='Completed'",
          'value' => 'SELECT COALESCE(SUM(charges),0) FROM bookings', 'complaints' => "SELECT COUNT(*) FROM complaints WHERE status='Pending'"] as $k => $sql) $s[$k] = val($sql);
$tiles = [['users', 'Users', 'bi-people'], ['providers', 'Providers', 'bi-person-badge'], ['approved', 'Verified providers', 'bi-patch-check'], ['pending', 'Pending verification', 'bi-hourglass-split'],
          ['bookings', 'Bookings', 'bi-calendar-check'], ['completed', 'Completed', 'bi-check2-circle'], ['value', 'Booking value', 'bi-cash-stack'], ['complaints', 'Pending complaints', 'bi-exclamation-triangle']];
$complaints = rows('SELECT c.*,u.name user_name,p.name provider_name FROM complaints c JOIN users u ON u.id=c.user_id LEFT JOIN providers p ON p.id=c.provider_id ORDER BY c.created_at DESC LIMIT 5');
$verif = rows('SELECT p.id,p.name,p.verification_status,(SELECT MAX(uploaded_at) FROM provider_documents WHERE provider_id=p.id) submitted FROM providers p WHERE EXISTS(SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id) ORDER BY submitted DESC LIMIT 5');
layout('admin', ['title' => 'Admin Dashboard', 'active' => 'dashboard']);
page_head('Dashboard', 'AASRA platform overview.');
?>
<div class="row g-3 mb-4"><?php foreach ($tiles as [$k, $l, $ic]): ?><div class="col-sm-6 col-xl-3"><div class="stat-tile"><span class="stat-ic"><i class="bi <?= $ic ?>"></i></span><div><b><?= $k === 'value' ? e(money($s[$k])) : (int)$s[$k] ?></b><span><?= e($l) ?></span></div></div></div><?php endforeach; ?></div>

<div class="row g-4">
  <div class="col-12"><div class="card-soft flush"><div class="card-head"><h2>Recent Complaints</h2><a class="btn btn-sm btn-outline-success" href="<?= e(url('admin/complaints')) ?>">View All</a></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>User</th><th>Provider</th><th>Subject</th><th>Status</th><th>Date</th><th class="pe-4 text-end">Action</th></tr></thead><tbody>
    <?php foreach ($complaints as $c): ?><tr data-row>
      <td class="ps-4 id-cell"><?= (int)$c['id'] ?></td><td><?= e($c['user_name']) ?></td><td><?= e($c['provider_name'] ?? '—') ?></td><td style="max-width:240px"><div class="clamp-2"><?= e($c['subject']) ?></div></td>
      <td><?= live_badge('complaint_status:' . $c['id'], $c['status']) ?></td><td class="text-nowrap"><?= e(fmt_date($c['created_at'])) ?></td>
      <td class="pe-4 text-end"><?php actions_open(); act_link('eye', 'View', url('admin/complaints/' . $c['id'])); act_ajax('hourglass-split', 'Mark Under Review', 'complaint_status', $c['id'], 'Under Review'); act_ajax('check2-circle', 'Mark Resolved', 'complaint_status', $c['id'], 'Resolved'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$complaints): ?><div class="empty-state"><i class="bi bi-emoji-smile"></i><p class="mb-0 mt-2">No complaints.</p></div><?php endif; ?></div></div>
  <div class="col-12"><div class="card-soft flush"><div class="card-head"><h2>Recent Verification Requests</h2><a class="btn btn-sm btn-outline-success" href="<?= e(url('admin/verification')) ?>">View All</a></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Provider</th><th>Submitted</th><th>Status</th><th class="pe-4 text-end">Action</th></tr></thead><tbody>
    <?php foreach ($verif as $v): ?><tr>
      <td class="ps-4"><?= e($v['name']) ?></td><td class="text-nowrap"><?= e(fmt_date($v['submitted'])) ?></td><td><?= live_badge('provider_verification:' . $v['id'], $v['verification_status']) ?></td>
      <td class="pe-4 text-end"><?php actions_open(); act_link('search', 'Review', url('admin/verification/' . $v['id'])); act_ajax('check-circle', 'Approve / Activate', 'verification', $v['id'], 'Approved'); act_ajax('x-circle', 'Inactive / Reject', 'verification', $v['id'], 'Rejected', 'text-danger'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$verif): ?><div class="empty-state"><i class="bi bi-patch-check"></i><p class="mb-0 mt-2">No verification requests yet.</p></div><?php endif; ?></div></div>
</div>
<?php layout_end();
