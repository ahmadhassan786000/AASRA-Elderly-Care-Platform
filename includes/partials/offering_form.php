<?php
/* Shared add/edit form for a provider's service.
   Expects: $c (current values), $err (field errors), $types (admin-defined active categories; null when editing),
   $existing (the service being edited, or null), $submitLabel. */
// Bootstrap feedback: shows the server-side message when there is one, otherwise the default for the browser check.
$defaults = ['category_id' => 'Please choose a category.', 'title' => 'Please enter a title (3–150 characters).', 'description' => 'The description must not exceed 1,000 characters.',
  'price' => 'Please enter a price between 0 and 1,000,000 (up to 2 decimals).', 'experience' => 'Experience must not exceed 120 characters.', 'availability' => 'Availability must not exceed 255 characters.'];
$f = fn(string $k) => '<div class="invalid-feedback" data-default="' . e($defaults[$k] ?? '') . '">' . e($err[$k] ?? ($defaults[$k] ?? '')) . '</div>';
$bad = fn(string $k) => isset($err[$k]) ? ' is-invalid' : '';
?>
<form method="post" class="needs-validation" novalidate><?= csrf_field() ?>
  <input type="hidden" name="action" value="<?= $existing ? 'update' : 'create' ?>">
  <?php if (!empty($err['general'])) echo alert_html('danger', $err['general']); ?>
  <div class="mb-3">
    <label class="form-label" for="category_id">Category</label>
    <?php if ($existing): ?>
      <input class="form-control" id="category_id" value="<?= e($existing['category_name']) ?>" disabled>
      <div class="form-text">The category can't be changed. To use a different one, delete this service and add a new one.</div>
    <?php else: ?>
      <select id="category_id" name="category_id" class="form-select<?= $bad('category_id') ?>" required>
        <option value="">Choose a category…</option>
        <?php foreach ($types as $t): ?><option value="<?= (int)$t['id'] ?>" <?= (int)$c['category_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['category_name']) ?></option><?php endforeach; ?>
      </select>
      <div class="form-text">Only categories created by AASRA are listed here.</div>
    <?php endif; ?>
    <?= $f('category_id') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="title">Service title</label>
    <input id="title" name="title" class="form-control<?= $bad('title') ?>" value="<?= e($c['title']) ?>" minlength="3" maxlength="150" placeholder="e.g. Overnight care for bedridden patients" required>
    <?= $f('title') ?>
  </div>
  <div class="mb-3">
    <label class="form-label" for="description">Description <span class="text-secondary fw-normal">(optional)</span></label>
    <textarea id="description" name="description" class="form-control<?= $bad('description') ?>" rows="3" maxlength="1000" placeholder="What does this service include?"><?= e($c['description']) ?></textarea>
    <?= $f('description') ?>
  </div>
  <div class="row g-3 mb-3">
    <div class="col-sm-6">
      <label class="form-label" for="price">Price per visit (PKR)</label>
      <input id="price" name="price" type="number" min="0" max="1000000" step="0.01" class="form-control<?= $bad('price') ?>" value="<?= e($c['price']) ?>" required>
      <?= $f('price') ?>
    </div>
    <div class="col-sm-6">
      <label class="form-label" for="experience">Experience <span class="text-secondary fw-normal">(optional)</span></label>
      <input id="experience" name="experience" class="form-control<?= $bad('experience') ?>" value="<?= e($c['experience']) ?>" maxlength="120" placeholder="e.g. 5 years">
      <?= $f('experience') ?>
    </div>
  </div>
  <div class="mb-4">
    <label class="form-label" for="availability">Availability <span class="text-secondary fw-normal">(optional)</span></label>
    <input id="availability" name="availability" class="form-control<?= $bad('availability') ?>" value="<?= e($c['availability']) ?>" maxlength="255" placeholder="e.g. Mon–Fri, 9 AM – 5 PM">
    <?= $f('availability') ?>
  </div>
  <div class="d-flex gap-2">
    <button class="btn btn-success"><?= e($submitLabel) ?></button>
    <?php if ($existing): ?><a class="btn btn-outline-secondary" href="<?= e(url('provider/services')) ?>">Cancel</a><?php endif; ?>
  </div>
</form>