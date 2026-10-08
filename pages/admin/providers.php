<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$f = ['q' => get('q'), 'status' => get('status'), 'verification' => get('verification'), 'service' => (int)get('service'), 'location' => get('location')];
$w = '1=1'; $a = [];
if ($f['q'] !== '') { $w .= ' AND (p.name LIKE ? OR p.email LIKE ? OR p.phone LIKE ?)'; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l, $l); }
if (in_array($f['status'], ['Active', 'Inactive'])) { $w .= ' AND p.account_status=?'; $a[] = $f['status']; }
if (in_array($f['verification'], ['Not Submitted', 'Pending', 'Approved', 'Rejected'])) { $w .= ' AND p.verification_status=?'; $a[] = $f['verification']; }
if ($f['service']) { $w .= ' AND EXISTS(SELECT 1 FROM provider_services ps WHERE ps.provider_id=p.id AND ps.service_id=?)'; $a[] = $f['service']; }
if ($f['location'] !== '') { $w .= ' AND p.location LIKE ?'; $a[] = '%' . $f['location'] . '%'; }
$list = rows("SELECT p.*,(SELECT GROUP_CONCAT(s.service_name SEPARATOR ', ') FROM provider_services ps JOIN services s ON s.id=ps.service_id WHERE ps.provider_id=p.id) services,
  (SELECT COALESCE(AVG(rating),0) FROM ratings WHERE provider_id=p.id) rating FROM providers p WHERE $w ORDER BY p.created_at DESC, p.id DESC", $a);
$allSvc = rows('SELECT id,service_name FROM services ORDER BY service_name');
layout('admin', ['title' => 'Providers', 'active' => 'providers']);
page_head('Providers', 'Manage provider accounts, activation and verification.');
?>
<form class="filter-bar"><div class="row g-2 align-items-end">
  <div class="col-md-3"><label for="q">Search</label><input id="q" name="q" class="form-control" placeholder="Name, email or phone" value="<?= e($f['q']) ?>"></div>
  <div class="col-6 col-md-2"><label for="status">Status</label><select id="status" name="status" class="form-select"><option value="">All</option><?php foreach (['Active', 'Inactive'] as $x): ?><option <?= $f['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label for="verification">Verification</label><select id="verification" name="verification" class="form-select"><option value="">All</option><?php foreach (['Not Submitted', 'Pending', 'Approved', 'Rejected'] as $x): ?><option <?= $f['verification'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-2"><label for="service">Service</label><select id="service" name="service" class="form-select"><option value="0">All</option><?php foreach ($allSvc as $s): ?><option value="<?= (int)$s['id'] ?>" <?= $f['service'] == $s['id'] ? 'selected' : '' ?>><?= e($s['service_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-6 col-md-1"><label for="location">Location</label><input id="location" name="location" class="form-control" value="<?= e($f['location']) ?>"></div>
  <div class="col-md-2 d-flex gap-2"><button class="btn btn-success flex-grow-1">Filter</button><?= filter_reset('admin/providers') ?></div>
</div></form>
<div class="card-soft flush"><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Provider</th><th>Location</th><th>Services</th><th>Rating</th><th>Verification</th><th>Status</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
<?php foreach ($list as $p): $ks = 'provider_status:' . $p['id']; $kv = 'provider_verification:' . $p['id']; ?><tr data-row>
  <td class="ps-4 id-cell"><?= (int)$p['id'] ?></td>
  <td><div class="d-flex align-items-center gap-2"><?= avatar($p, 'avatar-sm') ?><div><b><?= e($p['name']) ?></b><div class="small text-secondary"><?= e($p['email']) ?></div></div></div></td>
  <td><?= e($p['location'] ?: '—') ?></td><td style="max-width:220px"><div class="small clamp-2"><?= e($p['services'] ?: '—') ?></div></td>
  <td><?= $p['rating'] > 0 ? '<span class="stars"><i class="bi bi-star-fill"></i></span> ' . number_format((float)$p['rating'], 1) : '—' ?></td>
  <td><?= live_badge($kv, $p['verification_status']) ?></td><td><?= live_badge($ks, $p['account_status']) ?></td>
  <td class="pe-4 text-end"><?php actions_open(); act_link('eye', 'View', url('admin/providers/' . $p['id'])); act_link('pencil-square', 'Edit', url('admin/providers/' . $p['id'] . '/edit')); act_link('patch-check', 'Review verification', url('admin/verification/' . $p['id'])); act_divider();
    act_ajax('check-circle', 'Activate', 'provider_status', $p['id'], 'Active', '', '', [$ks, 'Inactive', $p['account_status']]); act_ajax('slash-circle', 'Deactivate', 'provider_status', $p['id'], 'Inactive', '', '', [$ks, 'Active', $p['account_status']]); act_divider();
    act_ajax('trash', 'Delete', 'delete_provider', $p['id'], '', 'text-danger', 'Delete this provider permanently?'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-person-badge"></i><p class="mb-0 mt-2">No providers match these filters.</p></div><?php endif; ?></div>
<?php layout_end();
