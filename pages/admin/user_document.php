<?php
// Administrators only (require_role also rejects every other role).
$admin = require_role('admin');
require_once APP_ROOT . '/includes/stream_document.php';
$d = row('SELECT * FROM user_documents WHERE id=?', [(int)get('id')]);
if (!$d) {
    show_error_page(404);
    exit;
}
stream_document($d, USER_DOC_DIR);
