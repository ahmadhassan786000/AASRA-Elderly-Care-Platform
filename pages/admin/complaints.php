<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['status' => get('status'), 'from' => get('from'), 'to' => get('to'), 'user' => get('user'), 'provider' => get('provider')];
$w = '1=1'; $a = [];
if (in_array($f['status'], ['Pending', 'Under Review', 'Resolved'])) { $w .= ' AND c.status=?'; $a[] = $f['status']; }
if (valid_date($f['from'])) { $w .= ' AND c.created_at>=?'; $a[] = $f['from'] . ' 00:00:00'; }
if (valid_date($f['to'])) { $w .= ' AND c.created_at<=?'; $a[] = $f['to'] . ' 23:59:59'; }
if ($f['user'] !== '') { $w .= ' AND u.name LIKE ?'; $a[] = '%' . $f['user'] . '%'; }
if ($f['provider'] !== '') { $w .= ' AND p.name LIKE ?'; $a[] = '%' . $f['provider'] . '%'; }
$list = rows("SELECT c.*,u.name user_name,p.name provider_name FROM complaints c JOIN users u ON u.id=c.user_id LEFT JOIN providers p ON p.id=c.provider_id WHERE $w ORDER BY c.created_at DESC, c.id DESC", $a);
layout('admin', ['title' => 'Complaints', 'active' => 'complaints']); page_head('Complaints', 'Review and respond to user complaints.');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-6 col-md-2"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Pending', 'Under Review', 'Resolved'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label for="from">From</label><input id="from" name="from" type="date" class="form-control" value="<?= e($f['from']) ?>"></div>
  <div class="col-6 col-md-2"><label for="to">To</label><input id="to" name="to" type="date" class="form-control" value="<?= e($f['to']) ?>"></div>
  <div class="col-6 col-md-2"><label for="user">User</label><input id="user" name="user" class="form-control" value="<?= e($f['user']) ?>"></div>
  <div class="col-6 col-md-2"><label for="provider">Provider</label><input id="provider" name="provider" class="form-control" value="<?= e($f['provider']) ?>"></div>
  <div class="col-6 col-md-2 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/complaints') ?></div></div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>User</th><th>Provider</th><th>Subject</th><th>Status</th><th>Date</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
<?php foreach ($list as $c): $k = 'complaint_status:' . $c['id']; ?><tr data-row>
  <td class="ps-4 id-cell"><?= (int)$c['id'] ?></td><td><?= e($c['user_name']) ?></td><td><?= e($c['provider_name'] ?? '—') ?></td><td style="max-width:280px"><div class="clamp-2"><?= e($c['subject']) ?></div></td>
  <td><?= live_badge($k, $c['status']) ?></td><td class="text-nowrap"><?= e(fmt_date($c['created_at'])) ?></td>
  <td class="pe-4 text-end"><?php actions_open(); act_link('eye', 'View / Respond', url('admin/complaints/' . $c['id'])); act_divider(); act_ajax('clock', 'Mark Pending', 'complaint_status', $c['id'], 'Pending'); act_ajax('hourglass-split', 'Mark Under Review', 'complaint_status', $c['id'], 'Under Review'); act_ajax('check2-circle', 'Mark Resolved', 'complaint_status', $c['id'], 'Resolved'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-emoji-smile"></i><p class="mb-0 mt-2">No complaints match these filters.</p></div><?php endif; ?></div>
<?php layout_end();
