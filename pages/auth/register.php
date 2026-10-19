<?php
if (current_user() && auth_refresh()) redirect(home_for_role(current_user()['role']));

$errors = [];
$general_error = '';
$eligibility_error = ''; // age requirement not met (shown as an alert, never on the Handicapped field)
$name = '';
$email = '';
$dob = '';
$handicapped = ''; // 'yes' | 'no'
$type = get('type') === 'provider' ? 'provider' : 'user';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  check_csrf();

  $type = ($_POST['account_type'] ?? '') === 'provider' ? 'provider' : 'user';
  $name = post('name');
  $email = post('email');
  $pass = (string)($_POST['password'] ?? '');
  $confirm = (string)($_POST['confirm_password'] ?? '');
  // Date of birth, Handicapped and the documents belong to regular users only. A provider request carrying them is ignored.
  $dob = $type === 'user' ? post('date_of_birth') : '';
  $handicapped = $type === 'user' ? post('handicapped') : '';
  $files = [];

  if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors['name'] = 'Please enter your full name (2–100 characters).';
  }

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    $errors['email'] = 'Please enter a valid email address.';
  }

  if (strlen($pass) < 8 || strlen($pass) > 72) {
    $errors['password'] = 'Password must be 8 to 72 characters.';
  }

  if ($pass !== $confirm) {
    $errors['confirm_password'] = 'Passwords do not match.';
  }

  if ($type === 'user') {
    // Eligibility, enforced on the server: age 50+ (from the date of birth) OR Handicapped = Yes.
    if ($e = user_dob_error($dob)) $errors['date_of_birth'] = $e;
    if (!in_array($handicapped, ['yes', 'no'], true)) $errors['handicapped'] = 'Please select Yes or No.';
    if (empty($errors['date_of_birth']) && empty($errors['handicapped']) && $handicapped === 'no' && user_age($dob) < USER_MIN_AGE)
      $eligibility_error = 'You must be at least ' . USER_MIN_AGE . ' years old to register if you are not handicapped.';
    if ($handicapped === 'yes') { // 1-5 documents, each checked individually; with "No" any submitted file is ignored
      $files = user_uploaded_files('disability_documents');
      if ($de = user_docs_error($files)) $errors['disability_documents'] = $de;
    }
  }

  if (!$errors && $eligibility_error === '' && row('SELECT id FROM users WHERE email=?', [$email])) {
    $errors['email'] = 'This email is already registered. Try logging in instead.';
  }

  if (!$errors && $eligibility_error === '') {
    $saved = []; // stored document paths, removed again if the registration fails
    db_begin();

    try {
      $ins = q(
        'INSERT INTO users(name,email,password,role,status,date_of_birth,is_handicapped) VALUES(?,?,?,?,?,?,?)',
        [$name, $email, password_hash($pass, PASSWORD_DEFAULT), $type, 'Active', $type === 'user' ? $dob : null, $type === 'user' ? ($handicapped === 'yes' ? 1 : 0) : null]
      );

      $uid = (int)mysqli_stmt_insert_id($ins);

      if ($type === 'provider') {
        q(
          "INSERT INTO providers(user_id,name,email,verification_status,account_status) VALUES(?,?,?,'Pending','Active')",
          [$uid, $name, $email]
        );
      }

      if ($type === 'user' && $handicapped === 'yes') user_docs_store($uid, $files, $saved);

      db_commit();
    } catch (UserDocumentException $e) {
      db_rollback();
      user_docs_cleanup($saved);
      $errors['disability_documents'] = $e->getMessage();
    } catch (Throwable $e) {
      db_rollback();
      user_docs_cleanup($saved);
      error_log($e);
      $general_error = 'Registration failed. Please try again.';
    }

    if (!$errors && $general_error === '') {
      flash(
        'success',
        $type === 'provider'
          ? 'Account created. Log in, then complete your Account Settings and verification.'
          : 'Account created. Please log in.'
      );

      redirect('login');
    }
  }
}

layout('auth', ['title' => 'Create account', 'width' => 500]);
?>

<h1 class="text-center mb-1">Create your account</h1>
<p class="text-secondary text-center mb-4">It only takes a minute.</p>

<?php if ($eligibility_error): ?>
  <div class="alert alert-danger d-flex align-items-start gap-2" role="alert" data-no-autodismiss><i class="bi bi-exclamation-triangle-fill mt-1"></i><div class="flex-grow-1"><?= e($eligibility_error) ?></div></div>
<?php endif; ?>

<?php if ($general_error): ?>
  <?= alert_html('danger', $general_error) ?>
<?php endif; ?>

<form method="post" id="registerForm" class="needs-validation" enctype="multipart/form-data" novalidate>
  <?= csrf_field() ?>

  <div class="mb-3">
    <label class="form-label" for="account_type">Account type</label>
    <select id="account_type" name="account_type" class="form-select<?= !empty($errors['account_type']) ? ' is-invalid' : '' ?>" required>
      <option value="user" <?= $type === 'user' ? 'selected' : '' ?>>User</option>
      <option value="provider" <?= $type === 'provider' ? 'selected' : '' ?>>Provider</option>
    </select>
    <div class="invalid-feedback" data-default="Please choose an account type."><?= e($errors['account_type'] ?? 'Please choose an account type.') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="name">Full name</label>
    <input
      id="name"
      name="name"
      class="form-control<?= !empty($errors['name']) ? ' is-invalid' : '' ?>"
      value="<?= e($name) ?>"
      minlength="2"
      maxlength="100"
      autocomplete="name"
      required>
    <div class="invalid-feedback" data-default="Please enter your full name (2–100 characters)."><?= e($errors['name'] ?? 'Please enter your full name (2–100 characters).') ?></div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="email">Email</label>
    <input
      id="email"
      name="email"
      type="email"
      class="form-control<?= !empty($errors['email']) ? ' is-invalid' : '' ?>"
      value="<?= e($email) ?>"
      maxlength="190"
      autocomplete="email"
      required>
    <div class="invalid-feedback" data-default="Please enter a valid email address."><?= e($errors['email'] ?? 'Please enter a valid email address.') ?></div>
  </div>

  <div id="userOnlyFields" class="<?= $type === 'provider' ? 'd-none' : '' ?>">
    <div class="mb-3">
      <label class="form-label" for="date_of_birth">Date of birth</label>
      <input
        id="date_of_birth"
        name="date_of_birth"
        type="date"
        class="form-control<?= !empty($errors['date_of_birth']) ? ' is-invalid' : '' ?>"
        value="<?= e($dob) ?>"
        min="1900-01-01"
        max="<?= e(date('Y-m-d')) ?>"
        autocomplete="bday"
        <?= $type === 'user' ? 'required' : 'disabled' ?>>
      <div class="invalid-feedback" data-default="Please enter a valid date of birth."><?= e($errors['date_of_birth'] ?? 'Please enter a valid date of birth.') ?></div>
    </div>

    <fieldset class="mb-3">
      <legend class="form-label fs-6">Handicapped</legend>
      <div class="d-flex gap-4">
        <div class="form-check">
          <input class="form-check-input<?= !empty($errors['handicapped']) ? ' is-invalid' : '' ?>" type="radio" name="handicapped" id="handicapped_yes" value="yes" <?= $handicapped === 'yes' ? 'checked' : '' ?> <?= $type === 'user' ? 'required' : 'disabled' ?>>
          <label class="form-check-label" for="handicapped_yes">Yes</label>
        </div>
        <div class="form-check">
          <input class="form-check-input<?= !empty($errors['handicapped']) ? ' is-invalid' : '' ?>" type="radio" name="handicapped" id="handicapped_no" value="no" <?= $handicapped === 'no' ? 'checked' : '' ?> <?= $type === 'user' ? 'required' : 'disabled' ?>>
          <label class="form-check-label" for="handicapped_no">No</label>
        </div>
      </div>
      <div class="invalid-feedback d-block<?= empty($errors['handicapped']) ? ' d-none' : '' ?>" id="handicappedError"><?= e($errors['handicapped'] ?? 'Please select Yes or No.') ?></div>
      <div class="form-text">To register you must be at least <?= USER_MIN_AGE ?> years old <b>or</b> select Handicapped.</div>
    </fieldset>

    <div class="mb-3 <?= $handicapped === 'yes' && $type === 'user' ? '' : 'd-none' ?>" id="disabilityDocs">
      <label class="form-label" for="disability_documents">Supporting documents</label>
      <input
        id="disability_documents"
        name="disability_documents[]"
        type="file"
        class="form-control<?= !empty($errors['disability_documents']) ? ' is-invalid' : '' ?>"
        accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
        multiple
        <?= $handicapped === 'yes' && $type === 'user' ? 'required' : 'disabled' ?>>
      <div class="invalid-feedback" id="disabilityDocsError"><?= e($errors['disability_documents'] ?? 'Please upload 1 to ' . USER_DOC_MAX . ' supporting documents.') ?></div>
      <div class="form-text">Upload <?= USER_DOC_MIN ?> to <?= USER_DOC_MAX ?> supporting documents (PDF, JPG or PNG, up to <?= USER_DOC_MAX_BYTES / 1048576 ?> MB each).<?= $errors ? ' Please select your files again.' : '' ?></div>
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="password">Password</label>
    <div class="pw-wrap">
      <input
        id="password"
        name="password"
        type="password"
        minlength="8"
        maxlength="72"
        class="form-control<?= !empty($errors['password']) ? ' is-invalid' : '' ?>"
        autocomplete="new-password"
        required>
      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    <div class="invalid-feedback" data-default="Password must be 8 to 72 characters."><?= e($errors['password'] ?? 'Password must be 8 to 72 characters.') ?></div>
  </div>

  <div class="mb-4">
    <label class="form-label" for="confirm_password">Confirm password</label>
    <div class="pw-wrap">
      <input
        id="confirm_password"
        name="confirm_password"
        type="password"
        minlength="8"
        maxlength="72"
        class="form-control<?= !empty($errors['confirm_password']) ? ' is-invalid' : '' ?>"
        data-match="#password"
        autocomplete="new-password"
        required>
      <button type="button" class="pw-toggle" aria-label="Show password">
        <i class="bi bi-eye"></i>
      </button>
    </div>
    <div class="invalid-feedback" data-default="The two passwords must match."><?= e($errors['confirm_password'] ?? 'The two passwords must match.') ?></div>
  </div>

  <button type="submit" class="btn btn-success btn-lg w-100">
    Create account
  </button>
</form>

<script>
  // Date of birth / Handicapped / documents are for regular users only. This is a convenience; the server enforces every rule.
  (function() {
    var MAX_DOCS = <?= USER_DOC_MAX ?>,
      t = document.getElementById('account_type'),
      box = document.getElementById('userOnlyFields'),
      dob = document.getElementById('date_of_birth'),
      yes = document.getElementById('handicapped_yes'),
      no = document.getElementById('handicapped_no'),
      docs = document.getElementById('disabilityDocs'),
      file = document.getElementById('disability_documents'),
      fileErr = document.getElementById('disabilityDocsError'),
      hErr = document.getElementById('handicappedError');
    if (!t || !box) return;

    function checkCount() {
      var n = file.files ? file.files.length : 0;
      file.setCustomValidity(n > MAX_DOCS ? 'You can upload a maximum of five documents.' : '');
      if (n > MAX_DOCS) {
        fileErr.textContent = 'You can upload a maximum of five documents.';
        file.classList.add('is-invalid');
      }
    }

    function sync() {
      var provider = t.value === 'provider',
        needDocs = !provider && yes.checked;
      box.classList.toggle('d-none', provider);
      dob.disabled = yes.disabled = no.disabled = provider;
      dob.required = yes.required = no.required = !provider;
      docs.classList.toggle('d-none', !needDocs);
      file.disabled = !needDocs; // a disabled file input is not submitted
      file.required = needDocs;
      if (!needDocs) {
        file.value = ''; // switching to No (or Provider) must not submit a previously chosen file
        file.setCustomValidity('');
      }
      if (hErr) hErr.classList.add('d-none');
    }
    document.getElementById('registerForm').addEventListener('submit', function() {
      if (t.value !== 'provider' && !yes.checked && !no.checked && hErr) hErr.classList.remove('d-none');
    }, true);
    t.addEventListener('change', sync);
    yes.addEventListener('change', sync);
    no.addEventListener('change', sync);
    file.addEventListener('change', checkCount);
    sync();
  })();
</script>

<p class="mt-4 mb-0 text-center">
  Already have an account? <a href="<?= e(url('login')) ?>">Log in</a>
</p>

<?php layout_end();
