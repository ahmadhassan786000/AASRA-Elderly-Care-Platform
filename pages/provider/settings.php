<?php
$u = require_role('provider');
require_once APP_ROOT . '/includes/provider_helpers.php';
require_once APP_ROOT . '/includes/password_form.php';

$p = current_provider();
$pid = (int)$p['id'];

$errors = [];

$name = (string)$p['name'];
$phone = (string)($p['phone'] ?? '');
$location = (string)($p['location'] ?? '');
$skills = (string)($p['skills'] ?? '');
$bio = (string)($p['bio'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();

    if (post('form') === 'password') {
        $err = handle_password_change($u['id']);
        $err
            ? flash('danger', $err)
            : flash('success', 'Password changed.');
        redirect('provider/settings');
    }

    $name = post('name');
    $phone = post('phone');
    $location = post('location');
    $skills = post('skills');
    $bio = post('bio');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors['name'] = 'Please enter your full name (2–100 characters).';
    }

    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if ($location !== '' && mb_strlen($location) > 190) {
        $errors['location'] = 'Location must not exceed 190 characters.';
    }

    if ($skills !== '' && mb_strlen($skills) > 500) {
        $errors['skills'] = 'Skills must not exceed 500 characters.';
    }

    if ($bio !== '' && mb_strlen($bio) > 2000) {
        $errors['bio'] = 'Bio must not exceed 2,000 characters.';
    }

    $photo = $p['photo'];

    if (!$errors && !empty($_FILES['photo']['name'])) {
        [$path, $perr] = save_upload(
            $_FILES['photo'],
            'providers',
            IMG_TYPES,
            2 * 1048576,
            true
        );

        if ($perr) {
            $errors['photo'] = $perr;
        } elseif ($path) {
            delete_upload($photo);
            $photo = $path;
        }
    }

    if (!$errors) {
        if (!empty($_POST['remove_photo']) && empty($_FILES['photo']['name'])) {
            delete_upload($photo);
            $photo = null;
        }

        q(
            'UPDATE providers SET name=?,phone=?,location=?,photo=?,bio=?,skills=? WHERE id=?',
            [
                $name,
                $phone ?: null,
                $location ?: null,
                $photo,
                $bio ?: null,
                $skills ?: null,
                $pid
            ]
        );

        q(
            'UPDATE users SET name=?,phone=?,address=? WHERE id=?',
            [
                $name,
                $phone ?: null,
                $location ?: null,
                $u['id']
            ]
        );

        flash('success', 'Account settings saved.');
        redirect('provider/settings');
    }
}

layout('provider', [
    'title' => 'Account Settings',
    'active' => 'profile'
]);
?>

<div class="page-head">
    <div>
        <h1>Account settings</h1>
        <p>Complete your details so users can find and book you.</p>
    </div>
</div>

<form
    method="post"
    enctype="multipart/form-data"
    class="card-soft mb-4 needs-validation"
    id="providerSettingsForm"
    novalidate>
    <?= csrf_field() ?>

    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <?= avatar($p, 'avatar-xl') ?>

        <div>
            <label class="form-label" for="photo">Profile photo</label>

            <input
                id="photo"
                type="file"
                name="photo"
                class="form-control<?= !empty($errors['photo']) ? ' is-invalid' : '' ?>"
                accept="image/jpeg,image/png,image/webp">

            <div class="invalid-feedback" data-default="Please choose a JPG, PNG or WebP image."><?= e($errors['photo'] ?? 'Please choose a JPG, PNG or WebP image.') ?></div>

            <div class="form-text">
                JPG, PNG or WebP, up to 2 MB.
            </div>

            <?php if ($p['photo']): ?>
                <div class="form-check mt-1">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remove_photo"
                        id="rp"
                        value="1">
                    <label class="form-check-label" for="rp">
                        Remove current photo
                    </label>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <label class="form-label" for="name">Full name</label>

            <input
                id="name"
                name="name"
                class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                value="<?= e($name) ?>"
                minlength="2"
                maxlength="100"
                required>

            <div class="invalid-feedback" data-default="Please enter your full name (2–100 characters)."><?= e($errors['name'] ?? 'Please enter your full name (2–100 characters).') ?></div>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email</label>

            <input
                class="form-control"
                value="<?= e($p['email']) ?>"
                disabled>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>

            <input
                id="phone"
                name="phone"
                class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                value="<?= e($phone) ?>"
                maxlength="20"
                pattern="[0-9+\s\-]{7,20}"
                inputmode="tel"
                placeholder="03xx xxxxxxx">

            <div class="invalid-feedback" data-default="Please enter a valid phone number (digits, +, - and spaces; 7–20 characters)."><?= e($errors['phone'] ?? 'Please enter a valid phone number (digits, +, - and spaces; 7–20 characters).') ?></div>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="location">Location</label>

            <input
                id="location"
                name="location"
                class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>"
                value="<?= e($location) ?>"
                maxlength="190"
                placeholder="e.g. Gulberg, Lahore">

            <div class="invalid-feedback" data-default="Location must not exceed 190 characters."><?= e($errors['location'] ?? 'Location must not exceed 190 characters.') ?></div>
        </div>

        <div class="col-12">
            <label class="form-label" for="skills">Skills</label>

            <input
                id="skills"
                name="skills"
                class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"
                value="<?= e($skills) ?>"
                maxlength="500"
                placeholder="e.g. Mobility support, medication reminders, cooking">

            <div class="invalid-feedback" data-default="Skills must not exceed 500 characters."><?= e($errors['skills'] ?? 'Skills must not exceed 500 characters.') ?></div>
        </div>

        <div class="col-12">
            <label class="form-label" for="bio">Bio</label>

            <textarea
                id="bio"
                name="bio"
                class="form-control <?= isset($errors['bio']) ? 'is-invalid' : '' ?>"
                rows="4"
                maxlength="2000"><?= e($bio) ?></textarea>

            <div class="invalid-feedback" data-default="Bio must not exceed 2,000 characters."><?= e($errors['bio'] ?? 'Bio must not exceed 2,000 characters.') ?></div>
        </div>

        <div class="col-12">
            <button class="btn btn-success">
                Save settings
            </button>

            <a
                class="btn btn-outline-secondary"
                href="<?= e(url('provider/verification')) ?>">
                Go to verification
            </a>
        </div>
    </div>
</form>

<div class="card-soft">
    <h2 class="h5 mb-3">Change password</h2>
    <?= password_form_html() ?>
</div>

<?php layout_end();
