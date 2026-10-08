<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php'; require_once APP_ROOT . '/includes/provider_helpers.php';
$id = (int)get('id'); $p = row('SELECT * FROM providers WHERE id=?', [$id]); if (!$p) { show_error_page(404); exit; }
$docs = rows('SELECT * FROM provider_documents WHERE provider_id=? ORDER BY uploaded_at DESC', [$id]);
$c = provider_completeness($p); $kv = 'provider_verification:' . $id; $ks = 'provider_status:' . $id;
layout('admin', ['title' => 'Review ' . $p['name'], 'active' => 'verification']);
page_head('Review: ' . $p['name'], 'Check the submitted information and documents.', '<a class="btn btn-outline-secondary" href="' . e(url('admin/verification')) . '">Back to list</a>');
?>
<div class="row g-4">
  <div class="col-lg-4"><div class="card-soft"><div class="d-flex align-items-center gap-3 mb-3"><?= avatar($p, 'avatar-lg') ?><div><b><?= e($p['name']) ?></b><div class="small text-secondary"><?= e($p['email']) ?></div></div></div>
    <div class="d-flex justify-content-between mb-2"><span class="text-secondary">Verification</span><?= live_badge($kv, $p['verification_status']) ?></div>
    <div class="d-flex justify-content-between mb-3"><span class="text-secondary">Account</span><?= live_badge($ks, $p['account_status']) ?></div>
    <?php if ($p['verification_note']): ?><div class="alert alert-secondary small">Last note: <?= e($p['verification_note']) ?></div><?php endif; ?>
    <div class="d-grid gap-2"><a href="#" class="btn btn-success" data-ajax-action="verification" data-id="<?= $id ?>" data-value="Approved"><i class="bi bi-check-circle"></i> Approve / Activate</a>
    <a href="#" class="btn btn-outline-danger" data-ajax-action="verification" data-id="<?= $id ?>" data-value="Rejected"><i class="bi bi-x-circle"></i> Inactive / Reject</a>
    <a href="#" class="btn btn-outline-secondary" data-ajax-action="verification" data-id="<?= $id ?>" data-value="Pending"><i class="bi bi-hourglass-split"></i> Set to pending</a></div>
    <hr><h2 class="h6">Profile completeness: <?= $c['pct'] ?>%</h2><ul class="list-unstyled small mb-0"><?php foreach ($c['checks'] as $l => $ok): ?><li><i class="bi <?= $ok ? 'bi-check-circle-fill text-success' : 'bi-x-circle text-danger' ?> me-1"></i><?= e($l) ?></li><?php endforeach; ?></ul>
    <p class="small text-secondary mt-2 mb-0">Providers with incomplete profiles stay hidden from users until they finish.</p></div></div>
  <div class="col-lg-8">
    <div class="card-soft mb-4"><h2 class="h5">Submitted information</h2><div class="row g-3"><div class="col-md-6"><div class="small text-secondary">Phone</div><?= e($p['phone'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Location</div><?= e($p['location'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Experience</div><?= e($p['experience'] ?: '—') ?></div><div class="col-md-6"><div class="small text-secondary">Charges</div><?= $p['charges'] !== null ? e(money($p['charges'])) : '—' ?></div><div class="col-12"><div class="small text-secondary">Skills</div><?= e($p['skills'] ?: '—') ?></div><div class="col-12"><div class="small text-secondary">Bio</div><?= e($p['bio'] ?: '—') ?></div></div></div>
    <div class="card-soft flush"><div class="card-head"><h2>Documents</h2></div><div class="table-responsive"><table class="table mb-0"><thead><tr><th class="ps-4">ID</th><th>Type</th><th>Uploaded</th><th>Status</th><th class="pe-4 text-end">Actions</th></tr></thead><tbody>
    <?php foreach ($docs as $d): ?><tr><td class="ps-4 id-cell"><?= (int)$d['id'] ?></td><td><?= e($d['document_type']) ?></td><td><?= e(fmt_date($d['uploaded_at'])) ?></td><td><?= live_badge('doc:' . $d['id'], $d['verification_status']) ?></td>
      <td class="pe-4 text-end"><?php actions_open(); act_link('eye', 'Open document', url('admin/documents/' . $d['id'])); act_ajax('check-circle', 'Approve document', 'doc_status', $d['id'], 'Approved'); act_ajax('x-circle', 'Reject document', 'doc_status', $d['id'], 'Rejected', 'text-danger'); actions_close(); ?></td></tr><?php endforeach; ?></tbody></table></div>
    <?php if (!$docs): ?><div class="empty-state"><i class="bi bi-file-earmark"></i><p class="mb-0 mt-2">No documents submitted yet.</p></div><?php endif; ?></div>
  </div>
</div>
<?php layout_end();
