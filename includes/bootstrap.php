<?php
define('APP_ROOT', dirname(__DIR__));
require APP_ROOT . '/config/app.php';

error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
    session_name('AASRASESS');
    session_set_cookie_params(['lifetime' => 0, 'path' => BASE_URL ?: '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => $https]);
    session_start();
}

require APP_ROOT . '/includes/functions.php';
require APP_ROOT . '/includes/db.php';
require APP_ROOT . '/includes/auth.php';
require APP_ROOT . '/includes/content.php';

set_exception_handler(function (Throwable $e) {
    error_log('AASRA: ' . $e);
    while (ob_get_level()) ob_end_clean();
    if (!headers_sent()) http_response_code(500);
    if (is_ajax()) { header('Content-Type: application/json'); echo json_encode(['ok' => false, 'message' => 'Something went wrong. Please try again.']); exit; }
    show_error_page(500);
    exit;
});
