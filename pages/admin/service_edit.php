<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php';
$id = (int)get('id'); $s = $id ? row('SELECT * FROM services WHERE id=?', [$id]) : ['id' => 0, 'service_name' => '', 'description' => '', 'status' => 'Active', 'image' => null];
if (!$s) { show_error_page(404); exit; }
$err = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf(); $name = post('service_name'); $desc = post('description'); $st = post('status');
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) $err = 'Service name must be 2–120 characters.';
    elseif (mb_strlen($desc) > 1000) $err = 'Description is too long (max 1000 characters).';
    elseif (!in_array($st, ['Active', 'Inactive'])) $err = 'Invalid status.';
    elseif (val('SELECT COUNT(*) FROM services WHERE service_name=? AND id<>?', [$name, $id])) $err = 'A service with this name already exists.';
    $image = $s['image'];
    if (!$err && !empty($_FILES['image']['name'])) {
        [$path, $perr] = save_upload($_FILES['image'], 'services', IMG_TYPES, 2 * 1048576, true);
        if ($perr) $err = $perr; elseif ($path) { delete_upload($image); $image = $path; }
    } elseif (!$err && !empty($_POST['remove_image'])) { delete_upload($image); $image = null; }
    if (!$err) {
        if ($id) q('UPDATE services SET service_name=?,description=?,status=?,image=? WHERE id=?', [$name, $desc ?: null, $st, $image, $id]);
        else q('INSERT INTO services(service_name,description,status,image) VALUES(?,?,?,?)', [$name, $desc ?: null, $st, $image]);
        flash('success', $id ? 'Service updated successfully.' : 'Service added successfully.'); redirect('admin/services');
    }
    $s = array_merge($s, ['service_name' => $name, 'description' => $desc, 'status' => $st]);
}
layout('admin', ['title' => $id ? 'Edit service' : 'Add service', 'active' => 'services']);
page_head($id ? 'Edit service' : 'Add service', $id ? $s['service_name'] : 'Create a new service.');
?>
<div class="card-soft col-xl-8"><?php if ($err) echo alert_html('danger', $err); ?>
<form method="post" enctype="multipart/form-data" class="row g-3" data-validate novalidate><?= csrf_field() ?>
  <div class="col-md-8"><label class="form-label" for="service_name">Service name</label><input id="service_name" name="service_name" class="form-control" value="<?= e($s['service_name']) ?>" required maxlength="120"></div>
  <div class="col-md-4"><label class="form-label" for="status">Status</label><select id="status" name="status" class="form-select"><?php foreach (['Active', 'Inactive'] as $x): ?><option <?= $s['status'] === $x ? 'selected' : '' ?>><?= $x ?></option><?php endforeach; ?></select></div>
  <div class="col-12"><label class="form-label" for="description">Description</label><textarea id="description" name="description" class="form-control" rows="4" maxlength="1000"><?= e($s['description']) ?></textarea></div>
  <div class="col-12"><label class="form-label" for="image">Service image</label>
    <div class="d-flex gap-3 align-items-start flex-wrap"><div style="width:220px"><?= service_thumb($s, 'svc-thumb rounded-3') ?></div>
    <div class="flex-grow-1"><input id="image" name="image" type="file" class="form-control" accept="image/jpeg,image/png,image/webp"><div class="form-text">JPG, PNG or WebP, up to 2 MB. Uploading a new image replaces the current one.</div>
    <?php if ($s['image']): ?><div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="remove_image" id="ri" value="1"><label class="form-check-label" for="ri">Remove current image</label></div><?php endif; ?></div></div></div>
  <div class="col-12 d-flex gap-2"><button class="btn btn-success"><?= $id ? 'Save changes' : 'Add service' ?></button><a class="btn btn-outline-secondary" href="<?= e(url('admin/services')) ?>">Cancel</a></div>
</form></div>
<?php layout_end();
