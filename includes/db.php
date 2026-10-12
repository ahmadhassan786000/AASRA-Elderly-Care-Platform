<?php
// Database layer — procedural MySQLi.
//
// Every query goes through q()/row()/rows()/val(), which use prepared statements, so user input is
// always bound as parameters and never concatenated into SQL. Errors are raised as mysqli_sql_exception
// (see mysqli_report below), logged server-side and shown to visitors only as the generic error page.
// Requires the mysqli extension with the mysqlnd driver (the default in XAMPP and in PHP >= 5.4).

// Shared connection holder. db_link(false) opens/returns the connection; db_link(true) closes it.
function db_link(bool $close = false): ?mysqli {
    static $link = null;
    if ($close) {
        if ($link instanceof mysqli) { try { mysqli_close($link); } catch (Throwable $e) { /* already closed */ } }
        $link = null;
        return null;
    }
    if ($link instanceof mysqli) return $link;

    $c = AASRA_DB_CONFIG;
    // Optional private overrides (git-ignored): config/local.php may return e.g. ['pass' => 'secret'].
    if (is_file(APP_ROOT . '/config/local.php')) $c = array_merge($c, (array)(require APP_ROOT . '/config/local.php'));
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // failures throw mysqli_sql_exception
    try {
        $link = mysqli_connect($c['host'], $c['user'], $c['pass'], $c['name'], (int)($c['port'] ?? 3306), $c['socket'] ?? null);
        mysqli_set_charset($link, 'utf8mb4');
    } catch (mysqli_sql_exception $e) {
        $link = null;
        error_log('AASRA DB connection failed: ' . $e->getMessage()); // credentials/details stay in the log only
        while (ob_get_level()) ob_end_clean();
        http_response_code(500);
        show_error_page(500);
        exit;
    }
    return $link;
}
function db(): mysqli { return db_link(false); }
function db_close(): void { db_link(true); }
register_shutdown_function('db_close'); // release the connection at the end of every request

// Bind types are inferred from the PHP value; null is sent as SQL NULL.
function db_bind_types(array $args): string {
    $t = '';
    foreach ($args as $a) $t .= is_int($a) || is_bool($a) ? 'i' : (is_float($a) ? 'd' : 's');
    return $t;
}

// Prepare + bind + execute. Returns the executed statement (use mysqli_stmt_affected_rows / mysqli_stmt_insert_id on it).
function q(string $sql, array $args = []): mysqli_stmt {
    $st = mysqli_prepare(db(), $sql);
    if ($args) {
        $args = array_map(fn($a) => is_bool($a) ? (int)$a : $a, array_values($args));
        mysqli_stmt_bind_param($st, db_bind_types($args), ...$args);
    }
    mysqli_stmt_execute($st);
    return $st;
}
function rows(string $sql, array $args = []): array {
    $st = q($sql, $args);
    $res = mysqli_stmt_get_result($st);
    $out = $res ? mysqli_fetch_all($res, MYSQLI_ASSOC) : [];
    if ($res) mysqli_free_result($res);
    mysqli_stmt_close($st);
    return $out;
}
function row(string $sql, array $args = []) { // first row as an associative array, or false when there is none
    $st = q($sql, $args);
    $res = mysqli_stmt_get_result($st);
    $r = $res ? mysqli_fetch_assoc($res) : null;
    if ($res) mysqli_free_result($res);
    mysqli_stmt_close($st);
    return $r ?: false;
}
function val(string $sql, array $args = []) { // first column of the first row, or false when there is none
    $r = row($sql, $args);
    return $r === false ? false : reset($r);
}

// Transactions (InnoDB).
function db_begin(): void { mysqli_begin_transaction(db()); }
function db_commit(): void { mysqli_commit(db()); }
function db_rollback(): void { mysqli_rollback(db()); }

// True when $e is an integrity-constraint violation (duplicate key or foreign key), SQLSTATE 23000.
function db_is_integrity_error(Throwable $e): bool {
    return $e instanceof mysqli_sql_exception && $e->getSqlState() === '23000';
}
