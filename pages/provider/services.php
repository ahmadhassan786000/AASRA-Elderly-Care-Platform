<?php
// Provider dashboard › My services: create and manage several services, each under an admin-defined category.
$u = require_role('provider');
require_once APP_ROOT . '/includes/provider_helpers.php';
$p = current_provider();
$pid = (int)$p['id'];
$c = ['category_id' => 0, 'title' => '', 'description' => '', 'price' => '', 'availability' => '', 'experience' => ''];
$err = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();
  $action = post('action');
  if ($action === 'delete') {
    // The service id comes from the browser, but ownership is enforced by provider_id = the logged-in provider.
    if (offering_delete($pid, (int)post('offering_id'))) flash('success', 'Service deleted.');
    else flash('danger', 'That service could not be found.');
    redirect('provider/services');
  }
  if ($action === 'create') {
    [$c, $err] = offering_validate($pid, null);
    if (!$err) {
      try {
        offering_create($pid, $c);
        flash('success', 'Service added.');
        redirect('provider/services');
      } catch (mysqli_sql_exception $e) {
        if (!db_is_integrity_error($e)) throw $e;
        $err['title'] = 'You already have a service with this title under this category.'; // lost a double-submit race
      }
    }
  }
}

$types = active_categories();
$offerings = provider_offerings($pid);
layout('provider', ['title' => 'Services', 'active' => 'services']);
?>
<div class="page-head">
  <div>
    <h1>My services</h1>
    <p>Add as many services as you like. Each one must belong to a category created by AASRA.</p>
  </div>
</div>
<div class="row g-4">
  <div class="col-lg-5">
    <div class="card-soft">
      <h2 class="h5 mb-3">Add a Service</h2>
      <?php if (!$types): ?>
        <p class="text-secondary mb-0">No categories are available right now. Please check back once an administrator has added them.</p>
      <?php else: $existing = null;
        $submitLabel = 'Add service';
        include APP_ROOT . '/includes/partials/offering_form.php';
      endif; ?>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card-soft flush">
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th class="ps-4">Services</th>
              <th>Price</th>
              <th>Details</th>
              <th class="pe-4 text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($offerings as $o): ?><tr>
                <td class="ps-4"><b><?= e($o['title']) ?></b>
                  <div class="mt-1"><span class="tag"><?= e($o['category_name']) ?></span><?php if ($o['category_status'] !== 'Active'): ?> <span class="badge text-bg-secondary" title="An administrator has turned this category off, so this service is hidden from users.">Category inactive</span><?php endif; ?></div>
                  <?php if ($o['description']): ?><div class="small text-secondary clamp-2 mt-1" style="max-width:320px"><?= e($o['description']) ?></div><?php endif; ?>
                </td>
                <td class="text-nowrap"><?= $o['price'] !== null ? e(money($o['price'])) : '<span class="text-secondary">—</span>' ?></td>
                <td class="small text-secondary"><?php if ($o['experience']): ?><div><i class="bi bi-briefcase me-1"></i><?= e($o['experience']) ?></div><?php endif; ?><?php if ($o['availability']): ?><div><i class="bi bi-clock me-1"></i><?= e($o['availability']) ?></div><?php endif; ?></td>
                <td class="pe-4 text-end text-nowrap">
                  <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('provider/services/' . (int)$o['id'] . '/edit')) ?>"><i class="bi bi-pencil-square"></i> Edit</a>
                  <form method="post" class="d-inline"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="offering_id" value="<?= (int)$o['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Delete this service?"><i class="bi bi-trash"></i> Delete</button></form>
                </td>
              </tr><?php endforeach; ?></tbody>
        </table>
      </div>
      <?php if (!$offerings): ?><div class="empty-state"><i class="bi bi-grid"></i>
          <p class="mb-0 mt-2">You haven't added any services yet.</p>
        </div><?php endif; ?>
    </div>
  </div>
</div>
<?php layout_end();
