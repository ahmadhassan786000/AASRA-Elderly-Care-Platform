<?php
$u = require_role('user');
$bid = (int)get('booking', (string)($_POST['booking_id'] ?? 0));
$b = row("SELECT b.*,p.name provider_name,s.service_name FROM bookings b JOIN providers p ON p.id=b.provider_id JOIN services s ON s.id=b.service_id WHERE b.id=? AND b.user_id=? AND b.status='Completed'", [$bid, $u['id']]);
if (!$b) { flash('danger', 'Only your completed bookings can be rated.'); redirect('user/bookings'); }
if (row('SELECT id FROM ratings WHERE booking_id=?', [$bid])) { flash('info', 'This booking has already been rated.'); redirect('user/bookings'); }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $r = (int)($_POST['rating'] ?? 0);
    if ($r >= 1 && $r <= 5) {
        q('INSERT INTO ratings(booking_id,user_id,provider_id,rating,review) VALUES(?,?,?,?,?)', [$bid, $u['id'], $b['provider_id'], $r, mb_substr(post('review'), 0, 1000)]);
        notify(null, (int)$b['provider_id'], 'New review', $u['name'] . ' rated you ' . $r . ' out of 5.', 'rating');
        flash('success', 'Thank you for your feedback.'); redirect('user/bookings');
    }
    flash('danger', 'Please choose a star rating.'); redirect('user/rating?booking=' . $bid);
}
layout('user', ['title' => 'Rate booking', 'nav' => 'bookings']);
?>
<div class="container py-4"><div class="row justify-content-center"><div class="col-lg-6"><div class="card-soft">
  <h1 class="h3">Rate <?= e($b['provider_name']) ?></h1><p class="text-secondary"><?= e($b['service_name']) ?> · <?= e(fmt_date($b['booking_date'])) ?></p>
  <form method="post"><?= csrf_field() ?><input type="hidden" name="booking_id" value="<?= $bid ?>">
    <fieldset class="mb-3"><legend class="form-label fs-6">Your rating</legend><div class="rating-input">
      <?php for ($i = 5; $i >= 1; $i--): ?><input type="radio" name="rating" id="r<?= $i ?>" value="<?= $i ?>" required><label for="r<?= $i ?>" title="<?= $i ?> star<?= $i > 1 ? 's' : '' ?>">★</label><?php endfor; ?></div></fieldset>
    <div class="mb-3"><label class="form-label" for="review">Review (optional)</label><textarea id="review" name="review" class="form-control" rows="4" maxlength="1000"></textarea></div>
    <button class="btn btn-success">Submit review</button></form>
</div></div></div></div>
<?php layout_end();
