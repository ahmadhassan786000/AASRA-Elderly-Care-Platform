<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$id = (int)get('id'); $c = row('SELECT c.*,u.name user_name,u.email user_email,p.name provider_name FROM complaints c JOIN users u ON u.id=c.user_id LEFT JOIN providers p ON p.id=c.provider_id WHERE c.id=?', [$id]);
if (!$c) { show_error_page(404); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $st = post('status'); $resp = mb_substr(post('admin_response'), 0, 2000);
    if (!in_array($st, ['Pending', 'Under Review', 'Resolved'])) { flash('danger', 'Invalid status.'); redirect('admin/complaints/' . $id); }
    q('UPDATE complaints SET status=?,admin_response=?,resolved_at=IF(?="Resolved",NOW(),NULL) WHERE id=?', [$st, $resp ?: null, $st, $id]);
    notify((int)$c['user_id'], null, 'Complaint update', 'Your complaint #' . $id . ' is now ' . $st . '.' . ($resp ? ' Response: ' . $resp : ''), 'complaint');
    flash('success', 'Complaint updated successfully.'); redirect('admin/complaints');
}
layout('admin', ['title' => 'Complaint ' . $id, 'active' => 'complaints']);
page_head('Complaint ' . $id, $c['subject'], '<a class="btn btn-outline-secondary" href="' . e(url('admin/complaints')) . '">Back to list</a>');
?>
<div class="row g-4"><div class="col-lg-7"><div class="card-soft"><div class="d-flex justify-content-between align-items-start mb-3"><h2 class="h5 mb-0"><?= e($c['subject']) ?></h2><?= badge($c['status']) ?></div>
  <p class="text-secondary small">From <b><?= e($c['user_name']) ?></b> (<?= e($c['user_email']) ?>) · <?= e($c['provider_name'] ? 'About ' . $c['provider_name'] : 'General') ?> · <?= e(fmt_date($c['created_at'], 'd M Y, g:i A')) ?></p>
  <p style="white-space:pre-wrap"><?= e($c['description']) ?></p></div></div>
<div class="col-lg-5"><div class="card-soft"><h2 class="h5">Respond</h2><form method="post"><?= csrf_field() ?>
  <div class="mb-3"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><?php foreach (['Pending', 'Under Review', 'Resolved'] as $x): ?><option <?= $c['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="mb-3"><label class="form-label" for="admin_response">Response to the user</label><textarea id="admin_response" name="admin_response" class="form-control" rows="5" maxlength="2000"><?= e($c['admin_response']) ?></textarea></div>
  <button class="btn btn-success">Update complaint</button></form></div></div></div>
<?php layout_end();
