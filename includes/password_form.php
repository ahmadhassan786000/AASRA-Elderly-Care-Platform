<?php
// Shared "change password" handler + form for user and provider areas. Returns error string or ''.
function handle_password_change(int $uid): string {
    $cur = (string)($_POST['current_password'] ?? ''); $new = (string)($_POST['new_password'] ?? ''); $cf = (string)($_POST['confirm_password'] ?? '');
    $hash = val('SELECT password FROM users WHERE id=?', [$uid]);
    if (!password_verify($cur, (string)$hash)) return 'Your current password is incorrect.';
    if (strlen($new) < 8 || strlen($new) > 72) return 'New password must be 8 to 72 characters.';
    if ($new !== $cf) return 'New passwords do not match.';
    q('UPDATE users SET password=? WHERE id=?', [password_hash($new, PASSWORD_DEFAULT), $uid]);
    return '';
}
function password_form_html(): string {
    return '<form method="post" class="row g-3" autocomplete="off">' . csrf_field() . '<input type="hidden" name="form" value="password">
    <div class="col-md-4"><label class="form-label" for="cp">Current password</label><input id="cp" type="password" name="current_password" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label" for="np">New password</label><input id="np" type="password" name="new_password" minlength="8" class="form-control" required></div>
    <div class="col-md-4"><label class="form-label" for="cn">Confirm new password</label><input id="cn" type="password" name="confirm_password" minlength="8" class="form-control" required></div>
    <div class="col-12"><button class="btn btn-outline-success">Change password</button></div></form>';
}
