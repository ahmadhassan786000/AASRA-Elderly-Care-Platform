<?php
function current_user(): ?array { return $_SESSION['user'] ?? null; }
function home_for_role(string $role): string { return ['admin' => 'admin', 'provider' => 'provider'][$role] ?? 'user'; }

function login_user(array $u): void {
    session_regenerate_id(true);
    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
    $_SESSION['uid_role'] = $u['role'];
}
function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) { $p = session_get_cookie_params(); setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $p['path'], 'domain' => $p['domain'], 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Lax']); }
    session_destroy();
}
// Re-validates the signed-in account against the database on every protected request,
// so deactivating an account takes effect immediately.
function auth_refresh(): ?array {
    $s = current_user(); if (!$s) return null;
    $u = row('SELECT id,name,email,role,status FROM users WHERE id=?', [$s['id']]);
    if (!$u || $u['status'] !== 'Active') { logout_user(); session_start(); flash('warning', 'Your account is inactive or no longer exists.'); return null; }
    $_SESSION['user'] = ['id' => (int)$u['id'], 'name' => $u['name'], 'email' => $u['email'], 'role' => $u['role']];
    $_SESSION['uid_role'] = $u['role'];
    return $_SESSION['user'];
}
function require_role(string $role): array {
    $u = auth_refresh();
    if (!$u) {
        if (is_ajax()) json_out(['ok' => false, 'message' => 'Please sign in again.'], 401);
        redirect('login?next=' . rawurlencode('/' . request_path()));
    }
    if ($u['role'] !== $role) { if (is_ajax()) json_out(['ok' => false, 'message' => 'Access denied.'], 403); show_error_page(403); exit; }
    return $u;
}
function current_provider(): array {
    static $p = null;
    if ($p === null) { $p = row('SELECT * FROM providers WHERE user_id=?', [current_user()['id']]); if (!$p) { show_error_page(403); exit; } }
    return $p;
}

function csrf_token(): string { if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function csrf_field(): string { return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">'; }
function check_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    $t = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    $known = (string)($_SESSION['csrf'] ?? '');
    if ($known === '' || !hash_equals($known, (string)$t)) {
        if (is_ajax()) json_out(['ok' => false, 'message' => 'Security token expired. Refresh the page and try again.'], 419);
        show_error_page(419); exit;
    }
}


