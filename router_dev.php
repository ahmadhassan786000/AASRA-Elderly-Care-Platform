<?php
// Dev server without Apache:  php -S localhost:8000 router_dev.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;
if ($path !== '/' && is_file($file) && !preg_match('#^/(config|includes|pages|database)/#', $path) && !str_ends_with($path, '.php') && !str_contains($path, '/uploads/documents/')) return false;
$_SERVER['SCRIPT_NAME'] = '/index.php';
require __DIR__ . '/index.php';
