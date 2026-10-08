<?php
$admin = require_role('admin'); require_once APP_ROOT . '/includes/stream_document.php';
$d = row('SELECT * FROM provider_documents WHERE id=?', [(int)get('id')]); if (!$d) { show_error_page(404); exit; } stream_document($d);
