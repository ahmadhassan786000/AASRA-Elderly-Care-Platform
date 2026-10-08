<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['q' => get('q'), 'status' => get('status'), 'type' => get('type')];
$w = '1=1'; $a = [];
if ($f['q'] !== '') { $w .= ' AND (name LIKE ? OR email LIKE ? OR phone LIKE ?)'; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l, $l); }
if (in_array($f['status'], ['Active', 'Inactive'])) { $w .= ' AND status=?'; $a[] = $f['status']; }
if (in_array($f['type'], ['user', 'provider', 'admin'])) { $w .= ' AND role=?'; $a[] = $f['type']; }
$list = rows("SELECT * FROM users WHERE $w ORDER BY created_at DESC, id DESC", $a);
layout('admin', ['title' => 'Users', 'active' => 'users']);
page_head('Users', 'All accounts on the platform.');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-md-5"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Name, email or phone" value="<?= e($f['q']) ?>"></div>
  <div class="col-6 col-md-2"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Active', 'Inactive'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label for="type">Account type</label><select id="type" name="type" class="form-select"><option value="">All</option><?php foreach (['user' => 'User', 'provider' => 'Provider', 'admin' => 'Admin'] as $k => $x): ?><option value="<?= $k ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/users') ?></div>
</div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Type</th><th>Status</th><th>Joined</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
<?php foreach ($list as $x): $k = 'user_status:' . $x['id']; $locked = $x['role'] === 'admin'; ?><tr data-row>
  <td class="ps-4 id-cell"><?= (int)$x['id'] ?></td><td><b><?= e($x['name']) ?></b></td><td><?= e($x['email']) ?></td><td><?= e($x['phone'] ?: '—') ?></td>
  <td><span class="tag"><?= e(ucfirst($x['role'])) ?></span></td><td><?= live_badge($k, $x['status']) ?></td><td class="text-nowrap"><?= e(fmt_date($x['created_at'])) ?></td>
  <td class="pe-4 text-end"><?php actions_open(); act_link('eye', 'View', url('admin/users/' . $x['id']));
    if (!$locked) { act_link('pencil-square', 'Edit', url('admin/users/' . $x['id'] . '/edit')); act_divider();
      act_ajax('check-circle', 'Activate', 'user_status', $x['id'], 'Active', '', '', [$k, 'Inactive', $x['status']]); act_ajax('slash-circle', 'Deactivate', 'user_status', $x['id'], 'Inactive', '', '', [$k, 'Active', $x['status']]);
      act_divider(); act_ajax('trash', 'Delete', 'delete_user', $x['id'], '', 'text-danger', 'Delete this account permanently?'); }
    actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-people"></i><p class="mb-0 mt-2">No accounts match these filters.</p></div><?php endif; ?></div>
<?php layout_end();
