<?php
$u = require_role('user');
$old = ['booking_id' => '', 'subject' => '', 'description' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $old = ['booking_id' => is_string($_POST['booking_id'] ?? null) ? trim($_POST['booking_id']) : '', 'subject' => post('subject'), 'description' => post('description')];
  // The related booking is mandatory and must be one of THIS user's bookings (the dropdown alone is never trusted).
  [$b, $err] = complaint_check_booking($_POST['booking_id'] ?? null, (int)$u['id']);
  if ($err) flash('danger', $err);
  elseif (mb_strlen($old['subject']) < 3 || mb_strlen($old['subject']) > 180 || mb_strlen($old['description']) < 10) flash('danger', 'Please add a subject and a description of at least 10 characters.');
  else {
    // provider_id always comes from the booking, never from the request.
    q("INSERT INTO complaints(user_id,provider_id,booking_id,subject,description,status) VALUES(?,?,?,?,?,'Pending')", [$u['id'], (int)$b['provider_id'], (int)$b['id'], $old['subject'], mb_substr($old['description'], 0, 3000)]);
    flash('success', 'Complaint submitted. We will review it shortly.');
    redirect('user/complaints');
  }
}
$bs = complaint_user_bookings((int)$u['id']);
$cs = rows('SELECT c.*,p.name provider_name,' . COMPLAINT_BOOKING_COLS . ' FROM complaints c LEFT JOIN providers p ON p.id=c.provider_id' . COMPLAINT_BOOKING_JOIN . 'WHERE c.user_id=? ORDER BY c.created_at DESC, c.id DESC', [$u['id']]);
layout('user', ['title' => 'Complaints', 'nav' => '']);
?>
<div class="container py-4">
  <h1 class="mb-4">Complaints</h1>
  <div class="row g-4">
    <div class="col-lg-5">
      <div class="card-soft">
        <h2 class="h5 mb-3">Submit a complaint</h2>
        <?php if (!$bs): ?>
          <p class="text-secondary mb-0">A complaint must be about one of your bookings. Once you have booked a service you can select it here.</p>
        <?php else: ?>
          <form method="post" class="needs-validation" novalidate><?= csrf_field() ?>
            <div class="mb-3"><label class="form-label" for="booking_id">Related booking <span class="text-danger" aria-hidden="true">*</span></label><select id="booking_id" name="booking_id" class="form-select" required>
                <option value="">Select a booking…</option><?php foreach ($bs as $b): ?><option value="<?= (int)$b['id'] ?>" <?= (string)$old['booking_id'] === (string)$b['id'] ? 'selected' : '' ?>><?= e(complaint_booking_option_label($b)) ?></option><?php endforeach; ?>
              </select><div class="invalid-feedback">Please select the booking this complaint is about.</div></div>
            <div class="mb-3"><label class="form-label" for="subject">Subject</label><input id="subject" name="subject" class="form-control" maxlength="180" required minlength="3" value="<?= e($old['subject']) ?>"><div class="invalid-feedback">Please enter a subject (3–180 characters).</div></div>
            <div class="mb-3"><label class="form-label" for="description">Description</label><textarea id="description" name="description" class="form-control" rows="5" required minlength="10" maxlength="3000"><?= e($old['description']) ?></textarea><div class="invalid-feedback">Please describe the problem (at least 10 characters).</div></div>
            <button class="btn btn-success">Submit complaint</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
    <div class="col-lg-7">
      <div class="card-soft">
        <h2 class="h5 mb-2">My complaints</h2>
        <?php foreach ($cs as $c): ?><div class="border-bottom py-3" id="complaint-<?= (int)$c['id'] ?>">
            <div class="d-flex justify-content-between gap-2"><b><?= e($c['subject']) ?></b><?= badge($c['status']) ?></div>
            <div class="small text-secondary">Complaint #<?= (int)$c['id'] ?> · <?= e(fmt_date($c['created_at'])) ?></div>
            <div class="mt-2"><?= complaint_booking_html($c) ?></div>
            <div class="complaint-details">
              <h3 class="complaint-section-label">Complaint details</h3>
              <?= expandable_text_html('cdesc-' . (int)$c['id'], $c['description']) ?>
            </div>
            <?= complaint_response_html((int)$c['id'], $c['admin_response']) ?>
          </div><?php endforeach; ?>
        <?php if (!$cs): ?><div class="empty-state"><i class="bi bi-flag"></i>
            <p class="mb-0 mt-2">You have not submitted any complaints.</p>
          </div><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php layout_end();
