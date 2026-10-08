<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['q' => get('q'), 'status' => get('status')];
$w = '1=1'; $a = [];
if ($f['q'] !== '') { $w .= ' AND (s.service_name LIKE ? OR s.description LIKE ?)'; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l); }
if (in_array($f['status'], ['Active', 'Inactive'])) { $w .= ' AND s.status=?'; $a[] = $f['status']; }
$list = rows("SELECT s.*,(SELECT COUNT(*) FROM provider_services ps WHERE ps.service_id=s.id) providers FROM services s WHERE $w ORDER BY s.service_name", $a);
layout('admin', ['title' => 'Services', 'active' => 'services']);
page_head('Services', 'Manage the services offered on AASRA.', '<a class="btn btn-success" href="' . e(url('admin/services/new')) . '"><i class="bi bi-plus-lg"></i> Add service</a>');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-md-6"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Service name or description" value="<?= e($f['q']) ?>"></div>
  <div class="col-md-3"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Active', 'Inactive'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-md-3 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/services') ?></div>
</div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Service</th><th>Description</th><th>Providers</th><th>Status</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
<?php foreach ($list as $s): $k = 'service_status:' . $s['id']; ?><tr data-row>
  <td class="ps-4 id-cell"><?= (int)$s['id'] ?></td>
  <td><div class="d-flex align-items-center gap-3"><?= service_thumb($s, 'thumb') ?><b><?= e($s['service_name']) ?></b></div></td>
  <td style="max-width:320px"><div class="small text-secondary clamp-2"><?= e($s['description']) ?></div></td><td><?= (int)$s['providers'] ?></td>
  <td><div class="dropdown"><button class="status-pill dropdown-toggle" data-live-pill="<?= e($k) ?>" data-status="<?= e($s['status']) ?>" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Status"><?= e($s['status']) ?></button>
    <ul class="dropdown-menu shadow-sm"><?php act_ajax('check-circle', 'Active', 'service_status', $s['id'], 'Active'); act_ajax('slash-circle', 'Inactive', 'service_status', $s['id'], 'Inactive'); ?></ul></div></td>
  <td class="pe-4 text-end"><?php actions_open(); act_link('pencil-square', 'Edit', url('admin/services/' . $s['id'] . '/edit'));
    if ($s['image']) act_ajax('image', 'Remove image', 'delete_service_image', $s['id'], '', '', 'Remove this image?');
    act_divider(); act_ajax('trash', 'Delete', 'delete_service', $s['id'], '', 'text-danger', 'Delete this service?'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-grid"></i><p class="mb-0 mt-2">No services match these filters.</p></div><?php endif; ?></div>
<?php layout_end();
