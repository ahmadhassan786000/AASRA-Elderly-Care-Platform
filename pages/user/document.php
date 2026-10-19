<?php
// A user can open only their OWN sign-up documents. Anyone else gets a 404, so ids cannot be probed.
$u = require_role('user');
require_once APP_ROOT . '/includes/stream_document.php';
$d = row('SELECT * FROM user_documents WHERE id=? AND user_id=?', [(int)get('id'), $u['id']]);
if (!$d) {
    show_error_page(404);
    exit;
}
stream_document($d, USER_DOC_DIR);
