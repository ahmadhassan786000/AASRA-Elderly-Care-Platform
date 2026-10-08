<?php
$u = require_role('provider'); $p = current_provider(); require_once APP_ROOT . '/includes/stream_document.php';
$d = row('SELECT * FROM provider_documents WHERE id=? AND provider_id=?', [(int)get('id'), $p['id']]);
if (!$d) { show_error_page(404); exit; } stream_document($d);
