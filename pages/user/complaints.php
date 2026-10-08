<?php
$u = require_role('user');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $bid = (int)($_POST['booking_id'] ?? 0); $subj = post('subject'); $desc = post('description'); $pid = null;
    if ($bid) { $b = row('SELECT provider_id FROM bookings WHERE id=? AND user_id=?', [$bid, $u['id']]); if (!$b) { flash('danger', 'Invalid booking.'); redirect('user/complaints'); } $pid = (int)$b['provider_id']; }
    if (mb_strlen($subj) < 3 || mb_strlen($subj) > 180 || mb_strlen($desc) < 10) flash('danger', 'Please add a subject and a description of at least 10 characters.');
    else { q("INSERT INTO complaints(user_id,provider_id,booking_id,subject,description,status) VALUES(?,?,?,?,?,'Pending')", [$u['id'], $pid, $bid ?: null, $subj, mb_substr($desc, 0, 3000)]); flash('success', 'Complaint submitted. We will review it shortly.'); }
    redirect('user/complaints');
}
$bs = rows('SELECT b.id,b.booking_date,p.name FROM bookings b JOIN providers p ON p.id=b.provider_id WHERE b.user_id=? ORDER BY b.created_at DESC', [$u['id']]);
$cs = rows('SELECT c.*,p.name provider_name FROM complaints c LEFT JOIN providers p ON p.id=c.provider_id WHERE c.user_id=? ORDER BY c.created_at DESC', [$u['id']]);
layout('user', ['title' => 'Complaints', 'nav' => '']);
?>
<div class="container py-4"><h1 class="mb-4">Complaints</h1><div class="row g-4">
  <div class="col-lg-5"><div class="card-soft"><h2 class="h5 mb-3">Submit a complaint</h2><form method="post"><?= csrf_field() ?>
    <div class="mb-3"><label class="form-label" for="booking_id">Related booking</label><select id="booking_id" name="booking_id" class="form-select"><option value="0">General (no booking)</option><?php foreach ($bs as $b): ?><option value="<?= (int)$b['id'] ?>">#<?= (int)$b['id'] ?> — <?= e($b['name']) ?> (<?= e(fmt_date($b['booking_date'])) ?>)</option><?php endforeach; ?></select></div>
    <div class="mb-3"><label class="form-label" for="subject">Subject</label><input id="subject" name="subject" class="form-control" maxlength="180" required></div>
    <div class="mb-3"><label class="form-label" for="description">Description</label><textarea id="description" name="description" class="form-control" rows="5" required></textarea></div>
    <button class="btn btn-success">Submit complaint</button></form></div></div>
  <div class="col-lg-7"><div class="card-soft"><h2 class="h5 mb-2">My complaints</h2>
    <?php foreach ($cs as $c): ?><div class="border-bottom py-3"><div class="d-flex justify-content-between gap-2"><b><?= e($c['subject']) ?></b><?= badge($c['status']) ?></div><div class="small text-secondary"><?= e(fmt_date($c['created_at'])) ?><?= $c['provider_name'] ? ' · ' . e($c['provider_name']) : '' ?></div><p class="small mb-1 mt-2"><?= e($c['description']) ?></p><?php if ($c['admin_response']): ?><div class="alert alert-success py-2 mb-0 small"><b>AASRA:</b> <?= e($c['admin_response']) ?></div><?php endif; ?></div><?php endforeach; ?>
    <?php if (!$cs): ?><div class="empty-state"><i class="bi bi-flag"></i><p class="mb-0 mt-2">You have not submitted any complaints.</p></div><?php endif; ?></div></div>
</div></div>
<?php layout_end();
