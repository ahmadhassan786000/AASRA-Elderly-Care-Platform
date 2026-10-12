<?php
// Provider dashboard › edit one of the provider's own offerings.
$u = require_role('provider');
require_once APP_ROOT . '/includes/provider_helpers.php';
$p = current_provider();
$pid = (int)$p['id'];
$id = (int)get('id');
$existing = provider_offering($pid, $id);          // null/false unless it belongs to this provider
if (!$existing) {
    show_error_page(404);
    exit;
}
$c = [
    'service_id' => (int)$existing['service_id'],
    'title' => $existing['title'],
    'description' => (string)$existing['description'],
    'price' => $existing['price'] !== null ? (string)$existing['price'] : '',
    'availability' => (string)$existing['availability'],
    'experience' => (string)$existing['experience']
];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    [$c, $err] = offering_validate($pid, $existing);
    if (!$err) {
        try {
            offering_update($pid, $id, $c);
            flash('success', 'Offering updated.');
            redirect('provider/services');
        } catch (mysqli_sql_exception $e) {
            if (!db_is_integrity_error($e)) throw $e;
            $err['title'] = 'You already have an offering with this title under this service.';
        }
    }
}
$types = null;
$submitLabel = 'Save changes';
layout('provider', ['title' => 'Edit offering', 'active' => 'services']);
?>
<div class="page-head">
    <div>
        <h1>Edit offering</h1>
        <p><?= e($existing['service_name']) ?></p>
    </div>
</div>
<div class="card-soft col-xl-7"><?php include APP_ROOT . '/includes/partials/offering_form.php'; ?></div>
<?php layout_end();
