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
$experience = (string)($p['experience'] ?? '');
$charges = $p['charges'] !== null ? (string)$p['charges'] : '';
$availability = (string)($p['availability'] ?? '');
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
    $experience = post('experience');
    $availability = post('availability');
    $skills = post('skills');
    $bio = post('bio');
    $charges = post('charges');

    if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
        $errors['name'] = 'Please enter your full name (2â€“100 characters).';
    }

    if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if ($location !== '' && mb_strlen($location) > 190) {
        $errors['location'] = 'Location must not exceed 190 characters.';
    }

    if ($experience !== '' && mb_strlen($experience) > 120) {
        $errors['experience'] = 'Experience must not exceed 120 characters.';
    }

    if (
        $charges !== '' &&
        (!is_numeric($charges) || (float)$charges < 0 || (float)$charges > 1000000)
    ) {
        $errors['charges'] = 'Charges must be between 0 and 1,000,000 PKR.';
    }

    if ($availability !== '' && mb_strlen($availability) > 255) {
        $errors['availability'] = 'Availability must not exceed 255 characters.';
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
            'UPDATE providers SET name=?,phone=?,location=?,photo=?,bio=?,skills=?,experience=?,availability=?,charges=? WHERE id=?',
            [
                $name,
                $phone ?: null,
                $location ?: null,
                $photo,
                $bio ?: null,
                $skills ?: null,
                $experience ?: null,
                $availability ?: null,
                $charges === '' ? null : (float)$charges,
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

        save_provider_services(
            $pid,
            (array)($_POST['services'] ?? [])
        );

        flash('success', 'Account settings saved.');
        redirect('provider/settings');
    }
}

$all = active_services();

$mine = array_column(
    rows(
        'SELECT service_id FROM provider_services WHERE provider_id=?',
        [$pid]
    ),
    'service_id'
);

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
    class="card-soft mb-4"
    id="providerSettingsForm"
    novalidate
>
    <?= csrf_field() ?>

    <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
        <?= avatar($p, 'avatar-xl') ?>

        <div>
            <label class="form-label" for="photo">Profile photo</label>

            <input
                id="photo"
                type="file"
                name="photo"
                class="form-control"
                accept="image/jpeg,image/png,image/webp"
            >

            <div class="form-text">
                JPG, PNG or WebP, up to 2 MB.
            </div>

            <?php if (!empty($errors['photo'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['photo']) ?>
                </div>
            <?php endif; ?>

            <?php if ($p['photo']): ?>
                <div class="form-check mt-1">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remove_photo"
                        id="rp"
                        value="1"
                    >
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
                maxlength="100"
                required
            >

            <?php if (isset($errors['name'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['name']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label">Email</label>

            <input
                class="form-control"
                value="<?= e($p['email']) ?>"
                disabled
            >
        </div>

        <div class="col-md-6">
            <label class="form-label" for="phone">Phone</label>

            <input
                id="phone"
                name="phone"
                class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                value="<?= e($phone) ?>"
                maxlength="20"
                placeholder="03xx xxxxxxx"
            >

            <?php if (isset($errors['phone'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['phone']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="location">Location</label>

            <input
                id="location"
                name="location"
                class="form-control <?= isset($errors['location']) ? 'is-invalid' : '' ?>"
                value="<?= e($location) ?>"
                maxlength="190"
                placeholder="e.g. Gulberg, Lahore"
            >

            <?php if (isset($errors['location'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['location']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="experience">Experience</label>

            <input
                id="experience"
                name="experience"
                class="form-control <?= isset($errors['experience']) ? 'is-invalid' : '' ?>"
                value="<?= e($experience) ?>"
                maxlength="120"
                placeholder="e.g. 5 years"
            >

            <?php if (isset($errors['experience'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['experience']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-6">
            <label class="form-label" for="charges">
                Charges per visit (PKR)
            </label>

            <input
                id="charges"
                name="charges"
                type="number"
                min="0"
                max="1000000"
                step="50"
                class="form-control <?= isset($errors['charges']) ? 'is-invalid' : '' ?>"
                value="<?= e($charges) ?>"
                placeholder="e.g. 1500"
            >

            <?php if (isset($errors['charges'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['charges']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label class="form-label" for="availability">
                Availability
            </label>

            <input
                id="availability"
                name="availability"
                class="form-control <?= isset($errors['availability']) ? 'is-invalid' : '' ?>"
                value="<?= e($availability) ?>"
                maxlength="255"
                placeholder="e.g. Mon-Fri, 9 AM - 5 PM"
            >

            <?php if (isset($errors['availability'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['availability']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label class="form-label" for="skills">Skills</label>

            <input
                id="skills"
                name="skills"
                class="form-control <?= isset($errors['skills']) ? 'is-invalid' : '' ?>"
                value="<?= e($skills) ?>"
                maxlength="500"
                placeholder="e.g. Mobility support, medication reminders, cooking"
            >

            <?php if (isset($errors['skills'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['skills']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <label class="form-label" for="bio">Bio</label>

            <textarea
                id="bio"
                name="bio"
                class="form-control <?= isset($errors['bio']) ? 'is-invalid' : '' ?>"
                rows="4"
                maxlength="2000"
            ><?= e($bio) ?></textarea>

            <?php if (isset($errors['bio'])): ?>
                <div class="field-error text-danger small mt-1">
                    <?= e($errors['bio']) ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-12">
            <span class="form-label d-block">
                Services you offer
            </span>

            <div class="row g-2">
                <?php foreach ($all as $s): ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="form-check">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="services[]"
                                value="<?= (int)$s['id'] ?>"
                                id="s<?= (int)$s['id'] ?>"
                                <?= in_array($s['id'], $mine) ? 'checked' : '' ?>
                            >

                            <label
                                class="form-check-label"
                                for="s<?= (int)$s['id'] ?>"
                            >
                                <?= e($s['service_name']) ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div
                id="servicesError"
                class="field-error text-danger small mt-1"
            ></div>
        </div>

        <div class="col-12">
            <button class="btn btn-success">
                Save settings
            </button>

            <a
                class="btn btn-outline-secondary"
                href="<?= e(url('provider/verification')) ?>"
            >
                Go to verification
            </a>
        </div>
    </div>
</form>

<div class="card-soft">
    <h2 class="h5 mb-3">Change password</h2>
    <?= password_form_html() ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('providerSettingsForm');

    if (!form) return;

    const fields = {
        name: document.getElementById('name'),
        phone: document.getElementById('phone'),
        location: document.getElementById('location'),
        experience: document.getElementById('experience'),
        charges: document.getElementById('charges'),
        availability: document.getElementById('availability'),
        skills: document.getElementById('skills'),
        bio: document.getElementById('bio')
    };

    function removeClientError(input) {
        if (!input) return;

        input.classList.remove('is-invalid');

        const error = input.parentElement.querySelector(
            '.client-field-error'
        );

        if (error) {
            error.remove();
        }
    }

    function showClientError(input, message) {
        if (!input) return;

        removeClientError(input);

        input.classList.add('is-invalid');

        const error = document.createElement('div');

        error.className =
            'client-field-error text-danger small mt-1';

        error.textContent = message;

        input.parentElement.appendChild(error);
    }

    function validateName() {
        const value = fields.name.value.trim();

        if (value.length < 2) {
            showClientError(
                fields.name,
                'Please enter your full name.'
            );
            return false;
        }

        if (value.length > 100) {
            showClientError(
                fields.name,
                'Full name must not exceed 100 characters.'
            );
            return false;
        }

        removeClientError(fields.name);
        return true;
    }

    function validatePhone() {
        const value = fields.phone.value.trim();

        if (value === '') {
            removeClientError(fields.phone);
            return true;
        }

        const pattern = /^[0-9+\-\s]{7,20}$/;

        if (!pattern.test(value)) {
            showClientError(
                fields.phone,
                'Please enter a valid phone number.'
            );
            return false;
        }

        removeClientError(fields.phone);
        return true;
    }

    function validateLocation() {
        const value = fields.location.value.trim();

        if (value.length > 190) {
            showClientError(
                fields.location,
                'Location must not exceed 190 characters.'
            );
            return false;
        }

        removeClientError(fields.location);
        return true;
    }

    function validateExperience() {
        const value = fields.experience.value.trim();

        if (value.length > 120) {
            showClientError(
                fields.experience,
                'Experience must not exceed 120 characters.'
            );
            return false;
        }

        removeClientError(fields.experience);
        return true;
    }

    function validateCharges() {
        const value = fields.charges.value.trim();

        if (value === '') {
            removeClientError(fields.charges);
            return true;
        }

        const number = Number(value);

        if (!Number.isFinite(number) || number < 0) {
            showClientError(
                fields.charges,
                'Charges cannot be negative.'
            );
            return false;
        }

        if (number > 1000000) {
            showClientError(
                fields.charges,
                'Charges cannot exceed 1,000,000 PKR.'
            );
            return false;
        }

        removeClientError(fields.charges);
        return true;
    }

    function validateAvailability() {
        const value = fields.availability.value.trim();

        if (value.length > 255) {
            showClientError(
                fields.availability,
                'Availability must not exceed 255 characters.'
            );
            return false;
        }

        removeClientError(fields.availability);
        return true;
    }

    function validateSkills() {
        const value = fields.skills.value.trim();

        if (value.length > 500) {
            showClientError(
                fields.skills,
                'Skills must not exceed 500 characters.'
            );
            return false;
        }

        removeClientError(fields.skills);
        return true;
    }

    function validateBio() {
        const value = fields.bio.value.trim();

        if (value.length > 2000) {
            showClientError(
                fields.bio,
                'Bio must not exceed 2,000 characters.'
            );
            return false;
        }

        removeClientError(fields.bio);
        return true;
    }

    fields.name.addEventListener('blur', validateName);
    fields.phone.addEventListener('blur', validatePhone);
    fields.location.addEventListener('blur', validateLocation);
    fields.experience.addEventListener('blur', validateExperience);
    fields.charges.addEventListener('blur', validateCharges);
    fields.availability.addEventListener('blur', validateAvailability);
    fields.skills.addEventListener('blur', validateSkills);
    fields.bio.addEventListener('blur', validateBio);

    fields.name.addEventListener('input', function () {
        if (fields.name.classList.contains('is-invalid')) {
            validateName();
        }
    });

    fields.phone.addEventListener('input', function () {
        if (fields.phone.classList.contains('is-invalid')) {
            validatePhone();
        }
    });

    fields.location.addEventListener('input', function () {
        if (fields.location.classList.contains('is-invalid')) {
            validateLocation();
        }
    });

    fields.experience.addEventListener('input', function () {
        if (fields.experience.classList.contains('is-invalid')) {
            validateExperience();
        }
    });

    fields.charges.addEventListener('input', function () {
        if (fields.charges.classList.contains('is-invalid')) {
            validateCharges();
        }
    });

    fields.availability.addEventListener('input', function () {
        if (fields.availability.classList.contains('is-invalid')) {
            validateAvailability();
        }
    });

    fields.skills.addEventListener('input', function () {
        if (fields.skills.classList.contains('is-invalid')) {
            validateSkills();
        }
    });

    fields.bio.addEventListener('input', function () {
        if (fields.bio.classList.contains('is-invalid')) {
            validateBio();
        }
    });

    form.addEventListener('submit', function (event) {
        const validName = validateName();
        const validPhone = validatePhone();
        const validLocation = validateLocation();
        const validExperience = validateExperience();
        const validCharges = validateCharges();
        const validAvailability = validateAvailability();
        const validSkills = validateSkills();
        const validBio = validateBio();

        if (
            !validName ||
            !validPhone ||
            !validLocation ||
            !validExperience ||
            !validCharges ||
            !validAvailability ||
            !validSkills ||
            !validBio
        ) {
            event.preventDefault();

            const firstInvalid = form.querySelector('.is-invalid');

            if (firstInvalid) {
                firstInvalid.focus();
            }
        }
    });
});
</script>

<?php layout_end();

