<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['status' => get('status'), 'from' => get('from'), 'to' => get('to'), 'q' => get('q')];
$w = 'EXISTS(SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id)'; $a = [];
if (in_array($f['status'], ['Pending', 'Approved', 'Rejected'])) { $w .= ' AND p.verification_status=?'; $a[] = $f['status']; }
if ($f['q'] !== '') { $w .= ' AND (p.name LIKE ? OR p.email LIKE ?)'; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l); }
if (valid_date($f['from'])) { $w .= ' AND (SELECT MAX(uploaded_at) FROM provider_documents WHERE provider_id=p.id)>=?'; $a[] = $f['from'] . ' 00:00:00'; }
if (valid_date($f['to'])) { $w .= ' AND (SELECT MAX(uploaded_at) FROM provider_documents WHERE provider_id=p.id)<=?'; $a[] = $f['to'] . ' 23:59:59'; }
$list = rows("SELECT p.*,(SELECT MAX(uploaded_at) FROM provider_documents WHERE provider_id=p.id) submitted,(SELECT COUNT(*) FROM provider_documents WHERE provider_id=p.id) docs FROM providers p WHERE $w ORDER BY FIELD(p.verification_status,'Pending','Rejected','Approved'), submitted DESC", $a);
layout('admin', ['title' => 'Verification', 'active' => 'verification']);
page_head('Provider verification', 'Review submitted documents and approve or reject providers.');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label for="q">Provider</label><input id="q" name="q" class="form-control" placeholder="Name or email" value="<?= e($f['q']) ?>"></div>
  <div class="col-md-2"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Pending', 'Approved', 'Rejected'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label for="from">Submitted from</label><input id="from" name="from" type="date" class="form-control" value="<?= e($f['from']) ?>"></div>
  <div class="col-6 col-md-2"><label for="to">to</label><input id="to" name="to" type="date" class="form-control" value="<?= e($f['to']) ?>"></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/verification') ?></div>
</div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Provider</th><th>Documents</th><th>Submitted</th><th>Verification</th><th>Account</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
<?php foreach ($list as $p): $kv = 'provider_verification:' . $p['id']; ?><tr data-row>
  <td class="ps-4 id-cell"><?= (int)$p['id'] ?></td><td><div class="d-flex align-items-center gap-2"><?= avatar($p, 'avatar-sm') ?><div><b><?= e($p['name']) ?></b><div class="small text-secondary"><?= e($p['email']) ?></div></div></div></td>
  <td><?= (int)$p['docs'] ?></td><td class="text-nowrap"><?= e(fmt_date($p['submitted'])) ?></td><td><?= live_badge($kv, $p['verification_status']) ?></td><td><?= live_badge('provider_status:' . $p['id'], $p['account_status']) ?></td>
  <td class="pe-4 text-end"><?php actions_open(); act_ajax('check-circle', 'Approve / Activate', 'verification', $p['id'], 'Approved'); act_ajax('x-circle', 'Inactive / Reject', 'verification', $p['id'], 'Rejected', 'text-danger'); act_divider(); act_link('search', 'Review', url('admin/verification/' . $p['id'])); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-patch-check"></i><p class="mb-0 mt-2">No verification requests match these filters.</p></div><?php endif; ?></div>
<?php layout_end();
