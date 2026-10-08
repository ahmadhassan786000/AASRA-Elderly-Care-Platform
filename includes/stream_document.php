<?php
function stream_document(array $d): void {
    $rel = (string)$d['document_path'];
    $abs = realpath(APP_ROOT . '/' . $rel);
    $base = realpath(APP_ROOT . '/uploads/documents');
    if (!$abs || !$base || !str_starts_with($abs, $base . DIRECTORY_SEPARATOR) || !is_file($abs)) { show_error_page(404); exit; }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($abs);
    if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) { show_error_page(404); exit; }
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: ' . $mime); header('Content-Length: ' . filesize($abs));
    header('Content-Disposition: inline; filename="document.' . pathinfo($abs, PATHINFO_EXTENSION) . '"');
    header('X-Content-Type-Options: nosniff'); header('Cache-Control: private, no-store');
    readfile($abs); exit;
}
