<?php
function db(): PDO {
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = require APP_ROOT . '/config/database.php';
    try {
        $pdo = new PDO("mysql:host={$c['host']};dbname={$c['name']};charset=utf8mb4", $c['user'], $c['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log('AASRA DB connection failed: ' . $e->getMessage());
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        show_error_page(500);
        exit;
    }
    return $pdo;
}
function q(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
function row(string $sql, array $args = []) { return q($sql, $args)->fetch(); }
function rows(string $sql, array $args = []): array { return q($sql, $args)->fetchAll(); }
function val(string $sql, array $args = []) { return q($sql, $args)->fetchColumn(); }
