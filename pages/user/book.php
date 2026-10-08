<?php
$u = require_role('user');
$pid = (int)get('id');
$list = listed_providers(['ids' => [$pid]], 1); if (!$list) { show_error_page(404); exit; }
$p = $list[0];
$svcs = rows("SELECT s.* FROM services s JOIN provider_services ps ON ps.service_id=s.id WHERE ps.provider_id=? AND s.status='Active' ORDER BY s.service_name", [$pid]);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $sid = (int)($_POST['service_id'] ?? 0); $date = post('booking_date'); $time = post('booking_time'); $req = mb_substr(post('requirements'), 0, 1000);
    $okService = in_array($sid, array_column($svcs, 'id'));
    if (!$okService) $error = 'Please choose one of the services this provider offers.';
    elseif (!valid_date($date) || $date <= date('Y-m-d')) $error = 'Please choose a future date.';
    elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) $error = 'Please choose a valid time.';
    else {
        q("INSERT INTO bookings(user_id,provider_id,service_id,booking_date,booking_time,requirements,charges,status) VALUES(?,?,?,?,?,?,?,'Pending')", [$u['id'], $pid, $sid, $date, $time . ':00', $req, $p['charges']]);
        notify(null, $pid, 'New booking request', $u['name'] . ' sent you a booking request for ' . fmt_date($date) . '.', 'booking');
        flash('success', 'Booking request sent. You will be notified when the provider responds.');
        redirect('user/bookings');
    }
}
layout('user', ['title' => 'Book ' . $p['name'], 'nav' => 'providers']);
?>
<div class="container py-4"><div class="row justify-content-center"><div class="col-lg-7">
  <a href="<?= e(url('user/providers/' . $pid)) ?>" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to profile</a>
  <div class="card-soft mt-3">
    <div class="d-flex gap-3 align-items-center mb-3"><?= avatar($p, 'avatar-lg') ?><div><h1 class="h4 mb-0">Book <?= e($p['name']) ?></h1><span class="text-secondary"><?= e(money($p['charges'])) ?> per visit</span></div></div>
    <?php if ($error) echo alert_html('danger', $error); ?>
    <form method="post" data-validate novalidate><?= csrf_field() ?>
      <div class="mb-3"><label class="form-label" for="service_id">Service</label><select id="service_id" name="service_id" class="form-select" required><?php foreach ($svcs as $s): ?><option value="<?= (int)$s['id'] ?>" <?= (int)($_POST['service_id'] ?? 0) === (int)$s['id'] ? 'selected' : '' ?>><?= e($s['service_name']) ?></option><?php endforeach; ?></select></div>
      <div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="booking_date">Date</label><input id="booking_date" name="booking_date" type="date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="form-control" value="<?= e($_POST['booking_date'] ?? '') ?>" required></div>
      <div class="col-md-6 mb-3"><label class="form-label" for="booking_time">Time</label><input id="booking_time" name="booking_time" type="time" class="form-control" value="<?= e($_POST['booking_time'] ?? '') ?>" required></div></div>
      <div class="mb-3"><label class="form-label" for="requirements">Requirements</label><textarea id="requirements" name="requirements" class="form-control" rows="4" maxlength="1000" placeholder="Tell the provider what help you need, and anything they should know."><?= e($_POST['requirements'] ?? '') ?></textarea></div>
      <button class="btn btn-success btn-lg">Send booking request</button>
    </form>
  </div>
</div></div></div>
<?php layout_end();
