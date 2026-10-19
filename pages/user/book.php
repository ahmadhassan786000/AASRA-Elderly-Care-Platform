<?php
$u = require_role('user');
require_once APP_ROOT . '/includes/payment_helpers.php';
require_once APP_ROOT . '/includes/booking_helpers.php';
require_once APP_ROOT . '/includes/provider_load.php';
$pid = (int)get('id');
$list = listed_providers(['ids' => [$pid]], 1);
if (!$list) {
  show_error_page(404);
  exit;
}
$p = $list[0];
$svcs = rows("SELECT s.* FROM categories s JOIN provider_categories ps ON ps.category_id=s.id WHERE ps.provider_id=? AND s.status='Active' ORDER BY s.category_name", [$pid]);
// The provider's own services (each sits under an active, admin-defined category). Only services with a real price can be booked.
$offs = array_values(array_filter(listed_offerings($pid), fn($o) => $o['price'] !== null && (float)$o['price'] > 0));
$error = '';
// Prefill: the address saved in My Profile. Editing it here only changes THIS booking; the profile is never updated.
$profileAddress = (string)val('SELECT address FROM users WHERE id=?', [$u['id']]);
$addr = $profileAddress;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $date = post('booking_date');
  $time = post('booking_time');
  $req = mb_substr(post('requirements'), 0, 1000);
  $addr = booking_address_clean(post('service_address'));
  // The booked service must belong to THIS provider; its category and price are always taken from the database.
  $chosen = array_values(array_filter($offs, fn($o) => (int)$o['id'] === (int)($_POST['offering_id'] ?? 0)));
  $sid = $chosen ? (int)$chosen[0]['category_id'] : 0;
  $charge = $chosen ? $chosen[0]['price'] : 0;
  $okService = in_array($sid, array_column($svcs, 'id'));
  if (!$okService) $error = 'Please choose one of the services this provider offers.';
  elseif ($addrErr = booking_address_error($addr)) $error = $addrErr;
  elseif (!valid_date($date) || $date <= date('Y-m-d')) $error = 'Please choose a future date.';
  elseif (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time)) $error = 'Please choose a valid time.';
  else {
    $offId = (int)$chosen[0]['id'];
    $dup = false;
    db_begin();
    try {
      // Serialise this user's booking requests: a second/concurrent submit waits here, then sees the first booking.
      row('SELECT id FROM users WHERE id=? FOR UPDATE', [$u['id']]);
      if (booking_duplicate_exists((int)$u['id'], $pid, $offId, $sid, $date, $time . ':00')) {
        $dup = true;
        db_rollback();
      } else {
        if (booking_has_offering_column())
          q("INSERT INTO bookings(user_id,provider_id,category_id,offering_id,service_name,booking_date,booking_time,requirements,service_address,charges,status) VALUES(?,?,?,?,?,?,?,?,?,?,'Pending')", [$u['id'], $pid, $sid, $offId, $chosen[0]['title'], $date, $time . ':00', $req, $addr, $charge]);
        else
          q("INSERT INTO bookings(user_id,provider_id,category_id,booking_date,booking_time,requirements,charges,status) VALUES(?,?,?,?,?,?,?,'Pending')", [$u['id'], $pid, $sid, $date, $time . ':00', $req, $charge]);
        $newId = (int)db()->insert_id;
        payment_ensure_record($newId); // Pending cash payment record, amount read from the booking
        db_commit();
      }
    } catch (Throwable $e) {
      db_rollback();
      throw $e;
    }
    if ($dup) {
      $error = 'You have already booked this provider\'s service for this time. Please choose another time.';
    } else {
      notify_booking_requested($newId);
      flash('success', 'Booking request sent. You will be notified when the provider responds.');
      redirect('user/bookings');
    }
  }
}
layout('user', ['title' => 'Book ' . $p['name'], 'nav' => 'providers']);
?>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-lg-7">
      <a href="<?= e(url('user/providers/' . $pid)) ?>" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Back to profile</a>
      <div class="card-soft mt-3">
        <div class="d-flex gap-3 align-items-center mb-3"><?= avatar($p, 'avatar-lg') ?><div>
            <h1 class="h4 mb-0">Book <?= e($p['name']) ?></h1><span class="text-secondary">Choose a service below</span>
          </div>
        </div>
        <?php if ($error) echo alert_html('danger', $error); ?>
        <form method="post" class="needs-validation" novalidate><?= csrf_field() ?>
          <?php if ($offs): ?>
            <div class="mb-3"><label class="form-label" for="offering_id">Service</label><select id="offering_id" name="offering_id" class="form-select" required><?php foreach ($offs as $o): ?><option value="<?= (int)$o['id'] ?>" <?= (int)($_POST['offering_id'] ?? get('service', get('offering', '0'))) === (int)$o['id'] ? 'selected' : '' ?>><?= e($o['title']) ?> — <?= e($o['category_name']) ?> (<?= e(money($o['price'])) ?>)</option><?php endforeach; ?></select><div class="invalid-feedback">Please choose a service.</div></div>
          <?php else: ?>
            <div class="alert alert-info" data-no-autodismiss>This provider has no bookable services right now.</div>
          <?php endif; ?>
          <div class="row">
            <div class="col-md-6 mb-3"><label class="form-label" for="booking_date">Date</label><input id="booking_date" name="booking_date" type="date" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" class="form-control" value="<?= e($_POST['booking_date'] ?? '') ?>" required><div class="invalid-feedback">Please choose a date from tomorrow onwards.</div></div>
            <div class="col-md-6 mb-3"><label class="form-label" for="booking_time">Time</label><input id="booking_time" name="booking_time" type="time" class="form-control" value="<?= e($_POST['booking_time'] ?? '') ?>" required><div class="invalid-feedback">Please choose a time.</div></div>
          </div>
          <div class="mb-3"><label class="form-label" for="service_address">Service Location / Booking Address</label>
            <textarea id="service_address" name="service_address" class="form-control" rows="3" minlength="<?= BOOKING_ADDRESS_MIN ?>" maxlength="<?= BOOKING_ADDRESS_MAX ?>" placeholder="House / street, area and city where the service is needed" required><?= e($addr) ?></textarea>
            <div class="invalid-feedback">Please enter the service location.</div>
          </div>
          <div class="mb-3"><label class="form-label" for="requirements">Requirements</label><textarea id="requirements" name="requirements" class="form-control" rows="4" maxlength="1000" placeholder="Tell the provider what help you need, and anything they should know."><?= e($_POST['requirements'] ?? '') ?></textarea></div>
          <button class="btn btn-success btn-lg" <?= $offs ? '' : ' disabled' ?>>Send booking request</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php layout_end();
