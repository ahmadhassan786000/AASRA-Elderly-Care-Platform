<?php
// Front controller: every clean URL is routed through this file.
require __DIR__ . '/includes/bootstrap.php';
ob_start();

$path = request_path();
$routes = require APP_ROOT . '/includes/routes.php';

// Old-style URLs (/login.php, /admin/users.php): redirect to the clean URL if it exists, otherwise 404.
if (preg_match('#\.php$#', $path)) {
    $clean = preg_replace('#(/?index)?\.php$#', '', $path);
    foreach ($routes as $pattern => $t) if (preg_match('#^' . $pattern . '$#', $clean)) {
        $qs = $_SERVER['QUERY_STRING'] ?? ''; redirect($clean . ($qs ? '?' . $qs : ''), 301);
    }
    show_error_page(404); exit;
}
// Canonicalise trailing slashes.
if ($path !== '' && str_ends_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')) {
    $qs = $_SERVER['QUERY_STRING'] ?? ''; redirect($path . ($qs ? '?' . $qs : ''), 301);
}

foreach ($routes as $pattern => $target) {
    if (!preg_match('#^' . $pattern . '$#', $path, $m)) continue;
    $file = is_array($target) ? $target[0] : $target;
    if (is_array($target)) foreach (array_slice($target, 1) as $i => $name) $_GET[$name] = $m[$i + 1];
    $abs = APP_ROOT . '/pages/' . $file . '.php';
    if (!is_file($abs)) break;
    (function () use ($abs) { include $abs; })();
    exit;
}
show_error_page(404);
