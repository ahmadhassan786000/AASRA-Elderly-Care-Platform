<?php
function e($v): string { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function url(string $path = ''): string { return BASE_URL . '/' . ltrim($path, '/'); }
function asset(string $path): string { $f = APP_ROOT . '/assets/' . $path; return url('assets/' . $path) . (is_file($f) ? '?v=' . filemtime($f) : ''); }
function upload_url(?string $path): string { return $path ? url($path) : ''; }
function redirect(string $path, int $code = 302): void {
    $target = preg_match('#^https?://#', $path) ? $path : url($path);
    header('Location: ' . $target, true, $code); exit;
}
function is_ajax(): bool {
    return (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest') || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');
}
function json_out(array $data, int $code = 200): void {
    http_response_code($code); header('Content-Type: application/json; charset=utf-8'); echo json_encode($data); exit;
}
function post(string $key, string $default = ''): string { return trim((string)($_POST[$key] ?? $default)); }
function get(string $key, string $default = ''): string { $v = $_GET[$key] ?? $default; return is_array($v) ? $default : trim((string)$v); }
function valid_date(string $d): bool { $x = DateTime::createFromFormat('Y-m-d', $d); return $x && $x->format('Y-m-d') === $d; }
function money($v): string { return 'PKR ' . number_format((float)$v, 0); }
function fmt_date($d, string $f = 'd M Y'): string { return $d ? date($f, strtotime((string)$d)) : '—'; }

/* ---------- flash messages & alerts (every alert is dismissible) ---------- */
function flash(string $type, string $msg): void { $_SESSION['flash'][] = ['type' => $type, 'msg' => $msg]; }
function alert_html(string $type, string $msg): string {
    $icons = ['success' => 'check-circle-fill', 'danger' => 'exclamation-triangle-fill', 'warning' => 'exclamation-circle-fill', 'info' => 'info-circle-fill'];
    $i = $icons[$type] ?? 'info-circle-fill';
    return '<div class="alert alert-' . e($type) . ' alert-dismissible fade show d-flex align-items-start gap-2" role="alert"><i class="bi bi-' . $i . ' mt-1"></i><div class="flex-grow-1">' . e($msg) . '</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
}
function render_flashes(): void {
    echo '<div id="alertHost">';
    foreach ($_SESSION['flash'] ?? [] as $f) echo alert_html($f['type'], $f['msg']);
    unset($_SESSION['flash']);
    echo '</div>';
}

function badge(?string $status): string {
    $map = ['Approved'=>'success','Active'=>'success','Completed'=>'success','Confirmed'=>'success','Resolved'=>'success','Paid'=>'success',
            'Accepted'=>'primary','Pending'=>'warning','Under Review'=>'info','In Progress'=>'info',
            'Rejected'=>'danger','Failed'=>'danger','Inactive'=>'secondary','Cancelled'=>'secondary','Not Submitted'=>'secondary'];
    $status = (string)$status;
    return '<span class="badge text-bg-' . ($map[$status] ?? 'secondary') . '">' . e($status) . '</span>';
}
function stars(float $r): string {
    $h = ''; for ($i = 1; $i <= 5; $i++) $h .= '<i class="bi bi-star' . ($r >= $i ? '-fill' : ($r >= $i - .5 ? '-half' : '')) . '"></i>';
    return '<span class="stars" aria-label="Rated ' . number_format($r, 1) . ' out of 5">' . $h . '</span>';
}
function initials(string $n): string { return strtoupper(mb_substr(trim($n) ?: '?', 0, 1)); }
function avatar(array $p, string $cls = ''): string {
    if (!empty($p['photo'])) return '<img class="avatar ' . e($cls) . '" src="' . e(upload_url($p['photo'])) . '" alt="' . e($p['name']) . '">';
    return '<span class="avatar ' . e($cls) . '">' . e(initials($p['name'])) . '</span>';
}
function service_icon(string $name): string {
    $n = strtolower($name);
    foreach (['nurs'=>'bi-heart-pulse','elder'=>'bi-person-hearts','compan'=>'bi-chat-heart','transport'=>'bi-car-front','home'=>'bi-house-heart','daily'=>'bi-calendar2-check','personal'=>'bi-person-raised-hand'] as $k => $i) if (str_contains($n, $k)) return $i;
    return 'bi-heart';
}
function service_thumb(array $s, string $cls = 'svc-thumb'): string {
    if (!empty($s['image'])) return '<img class="' . e($cls) . '" src="' . e(upload_url($s['image'])) . '" alt="' . e($s['service_name']) . '" loading="lazy">';
    return '<div class="' . e($cls) . ' svc-thumb-ph"><i class="bi ' . service_icon($s['service_name']) . '"></i></div>';
}

function notify(?int $userId, ?int $providerId, string $title, string $message, string $type = 'system'): void {
    q('INSERT INTO notifications(user_id,provider_id,title,message,type) VALUES(?,?,?,?,?)', [$userId, $providerId, $title, $message, $type]);
}

/* ---------- uploads ---------- */
// Validates an uploaded file; returns [relativePath|null, error|null].
function save_upload(array $file, string $dir, array $allowed, int $maxBytes, bool $isImage): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return [null, null];
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) return [null, 'The file is too large (max ' . round($maxBytes / 1048576) . ' MB).'];
    if ($file['error'] !== UPLOAD_ERR_OK) return [null, 'The file could not be uploaded. Please try again.'];
    if ($file['size'] > $maxBytes) return [null, 'The file is too large (max ' . round($maxBytes / 1048576) . ' MB).'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) return [null, 'Unsupported file type. Allowed: ' . implode(', ', array_unique(array_values($allowed))) . '.'];
    if ($isImage) { $info = @getimagesize($file['tmp_name']); if (!$info || $info[0] > 6000 || $info[1] > 6000) return [null, 'The image is invalid or too large in dimensions.']; }
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    $abs = APP_ROOT . '/uploads/' . $dir; if (!is_dir($abs)) mkdir($abs, 0755, true);
    if (!move_uploaded_file($file['tmp_name'], $abs . '/' . $name)) return [null, 'Could not save the file.'];
    return ['uploads/' . $dir . '/' . $name, null];
}
const IMG_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
const DOC_TYPES = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
function delete_upload(?string $rel): void {
    if ($rel && str_starts_with($rel, 'uploads/') && !str_contains($rel, '..')) { $f = APP_ROOT . '/' . $rel; if (is_file($f)) @unlink($f); }
}

/* ---------- marketplace queries ---------- */
function listed_providers(array $f = [], int $limit = 0): array {
    $where = "p.verification_status='Approved' AND p.account_status='Active' AND u.status='Active' AND p.charges IS NOT NULL";
    $a = [];
    if (!empty($f['q'])) { $where .= " AND (p.name LIKE ? OR p.location LIKE ? OR p.bio LIKE ? OR p.skills LIKE ? OR EXISTS(SELECT 1 FROM provider_services ps JOIN services s ON s.id=ps.service_id WHERE ps.provider_id=p.id AND s.service_name LIKE ?))"; $l = '%' . $f['q'] . '%'; array_push($a, $l, $l, $l, $l, $l); }
    if (!empty($f['service'])) { $where .= " AND EXISTS(SELECT 1 FROM provider_services ps JOIN services s ON s.id=ps.service_id WHERE ps.provider_id=p.id AND ps.service_id=? AND s.status='Active')"; $a[] = (int)$f['service']; }
    if (!empty($f['location'])) { $where .= ' AND p.location LIKE ?'; $a[] = '%' . $f['location'] . '%'; }
    if (!empty($f['min'])) { $where .= ' AND p.charges>=?'; $a[] = (float)$f['min']; }
    if (!empty($f['max'])) { $where .= ' AND p.charges<=?'; $a[] = (float)$f['max']; }
    if (!empty($f['ids'])) { $where .= ' AND p.id IN (' . implode(',', array_map('intval', $f['ids'])) . ')'; }
    $order = ['price_asc' => 'p.charges ASC', 'price_desc' => 'p.charges DESC', 'rating' => 'rating DESC, reviews DESC'][$f['sort'] ?? 'rating'] ?? 'rating DESC, reviews DESC';
    $sql = "SELECT p.*, COALESCE(r.avg_rating,0) rating, COALESCE(r.cnt,0) reviews,
        (SELECT GROUP_CONCAT(s.service_name ORDER BY s.service_name SEPARATOR ', ') FROM provider_services ps JOIN services s ON s.id=ps.service_id AND s.status='Active' WHERE ps.provider_id=p.id) services
        FROM providers p JOIN users u ON u.id=p.user_id
        LEFT JOIN (SELECT provider_id, AVG(rating) avg_rating, COUNT(*) cnt FROM ratings GROUP BY provider_id) r ON r.provider_id=p.id
        WHERE $where ORDER BY $order, p.id" . ($limit ? ' LIMIT ' . (int)$limit : '');
    return rows($sql, $a);
}
function active_services(): array { return rows("SELECT * FROM services WHERE status='Active' ORDER BY service_name"); }

// $area: 'public' | 'user'
function provider_card(array $p, string $area = 'public', bool $fav = false): void {
    $href = url(($area === 'user' ? 'user/providers/' : 'providers/') . $p['id']);
    ?>
    <div class="prov-card h-100">
      <div class="d-flex gap-3 align-items-center">
        <?= avatar($p, 'avatar-lg') ?>
        <div class="min-w-0">
          <h3 class="h6 mb-1 text-truncate"><a class="stretched-link text-reset text-decoration-none" href="<?= e($href) ?>"><?= e($p['name']) ?></a></h3>
          <div class="small"><?= stars((float)$p['rating']) ?> <span class="text-secondary"><?= $p['reviews'] ? number_format((float)$p['rating'], 1) . ' (' . (int)$p['reviews'] . ')' : 'New' ?></span></div>
        </div>
      </div>
      <p class="text-secondary small mt-3 mb-2 clamp-2"><?= e($p['bio'] ?: 'Verified AASRA provider.') ?></p>
      <div class="small text-secondary mb-3"><i class="bi bi-geo-alt"></i> <?= e($p['location'] ?: 'Lahore') ?> &nbsp;·&nbsp; <?= e(money($p['charges'])) ?>/visit</div>
      <div class="d-flex flex-wrap gap-1 mb-3">
        <?php foreach (array_slice(array_filter(explode(', ', (string)$p['services'])), 0, 2) as $sv): ?><span class="tag"><?= e($sv) ?></span><?php endforeach; ?>
      </div>
      <span class="btn btn-outline-success btn-sm">View profile</span>
    </div>
    <?php
}

/* ---------- layouts & errors ---------- */
function layout(string $name, array $o = []): void {
    $GLOBALS['__layout'] = ['name' => $name, 'o' => $o];
    extract($o); include APP_ROOT . "/includes/layouts/$name.top.php";
}
function layout_end(): void {
    $l = $GLOBALS['__layout']; extract($l['o']); include APP_ROOT . "/includes/layouts/{$l['name']}.bottom.php";
}
function show_error_page(int $code): void {
    $titles = [403 => 'Access denied', 404 => 'Page Not Found', 419 => 'Session expired', 500 => 'Something went wrong'];
    $msgs = [403 => 'You do not have permission to open this page.', 404 => 'The page you are looking for could not be found.', 419 => 'Your security token expired. Please go back, refresh the page and try again.', 500 => 'We hit an unexpected problem. Please try again in a moment.'];
    $o = ['title' => $titles[$code] ?? 'Error', 'code' => $code, 'heading' => $titles[$code] ?? 'Error', 'message' => $msgs[$code] ?? ''];
    http_response_code($code);
    // Inside the admin area, a signed-in admin keeps the admin layout.
    $inAdmin = $code !== 500 && str_starts_with(request_path(), 'admin') && (($_SESSION['uid_role'] ?? '') === 'admin');
    $o['inAdmin'] = $inAdmin;
    $o['active'] = '';
    layout($inAdmin ? 'admin' : 'plain', $o); extract($o); include APP_ROOT . '/includes/partials/error.php'; layout_end();
}
function request_path(): string {
    $p = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $p = rawurldecode($p);
    if (BASE_URL !== '' && str_starts_with($p, BASE_URL)) $p = substr($p, strlen(BASE_URL));
    return trim($p, '/');
}
