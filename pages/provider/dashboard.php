<?php
$u = require_role('provider'); require_once APP_ROOT . '/includes/provider_helpers.php';
$p = current_provider(); $pid = (int)$p['id']; $c = provider_completeness($p);
$counts = array_column(rows('SELECT status,COUNT(*) c FROM bookings WHERE provider_id=? GROUP BY status', [$pid]), 'c', 'status');
$rt = row('SELECT COALESCE(AVG(rating),0) a,COUNT(*) c FROM ratings WHERE provider_id=?', [$pid]);
$recent = rows('SELECT b.*,u.name user_name,s.service_name FROM bookings b JOIN users u ON u.id=b.user_id JOIN services s ON s.id=b.service_id WHERE b.provider_id=? ORDER BY b.created_at DESC LIMIT 6', [$pid]);
$tiles = [['Pending requests', $counts['Pending'] ?? 0, 'bi-inbox'], ['Upcoming', ($counts['Accepted'] ?? 0) + ($counts['Confirmed'] ?? 0), 'bi-calendar-event'], ['Completed', $counts['Completed'] ?? 0, 'bi-check2-circle'], ['Rating', $rt['c'] ? number_format((float)$rt['a'], 1) . ' ★' : '—', 'bi-star']];
layout('provider', ['title' => 'Dashboard', 'active' => 'dashboard']);
?>
<div class="page-head"><div><h1>Welcome, <?= e(explode(' ', $u['name'])[0]) ?></h1><p>Manage your profile, requests and reviews.</p></div></div>
<?php if ($p['verification_status'] === 'Rejected'): ?><div class="alert alert-danger"><b>Verification rejected.</b> <?= e($p['verification_note'] ?: 'Please upload corrected documents.') ?> <a href="<?= e(url('provider/verification')) ?>" class="alert-link">Update documents</a></div>
<?php elseif ($p['verification_status'] !== 'Approved'): ?><div class="alert alert-warning"><b>You are not visible to users yet.</b> <?= $p['verification_status'] === 'Pending' ? 'Your documents are being reviewed by an admin.' : 'Complete your profile and upload your documents to get verified.' ?> <a href="<?= e(url('provider/verification')) ?>" class="alert-link">Verification</a></div><?php endif; ?>
<div class="row g-3 mb-4"><?php foreach ($tiles as [$l, $v, $ic]): ?><div class="col-sm-6 col-xl-3"><div class="stat-tile"><span class="stat-ic"><i class="bi <?= $ic ?>"></i></span><div><b><?= e($v) ?></b><span><?= e($l) ?></span></div></div></div><?php endforeach; ?></div>
<div class="row g-4">
  <div class="col-xl-4"><div class="card-soft h-100"><div class="d-flex justify-content-between"><h2 class="h5">Profile completeness</h2><b><?= $c['pct'] ?>%</b></div>
    <div class="progress mb-3" style="height:10px" role="progressbar" aria-valuenow="<?= $c['pct'] ?>" aria-valuemin="0" aria-valuemax="100"><div class="progress-bar bg-success" style="width:<?= $c['pct'] ?>%"></div></div>
    <ul class="list-unstyled small mb-3"><?php foreach ($c['checks'] as $l => $ok): ?><li class="mb-1"><i class="bi <?= $ok ? 'bi-check-circle-fill text-success' : 'bi-circle text-secondary' ?> me-2"></i><?= e($l) ?></li><?php endforeach; ?></ul>
    <a class="btn btn-success btn-sm" href="<?= e(url('provider/settings')) ?>">Open Account Settings</a></div></div>
  <div class="col-xl-8"><div class="card-soft flush h-100"><div class="card-head"><h2>Recent requests</h2><a href="<?= e(url('provider/bookings')) ?>" class="btn btn-sm btn-outline-success">View all</a></div>
    <div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">Client</th><th>Service</th><th>Date</th><th class="pe-4">Status</th></tr></thead><tbody>
    <?php foreach ($recent as $b): ?><tr><td class="ps-4"><?= e($b['user_name']) ?></td><td><?= e($b['service_name']) ?></td><td><?= e(fmt_date($b['booking_date'])) ?></td><td class="pe-4"><?= badge($b['status']) ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$recent): ?><div class="empty-state"><i class="bi bi-inbox"></i><p class="mb-0 mt-2">No requests yet. Complete your profile to get discovered.</p></div><?php endif; ?></div></div>
</div>
<?php layout_end();
