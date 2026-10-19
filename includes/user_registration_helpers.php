<?php
/* User sign-up eligibility: a user may register if they are 50 or older (calculated from the date of birth) OR select
   Handicapped. Handicapped users must upload 1-5 supporting documents. Everything here runs on the server; the JavaScript on
   the form is only a convenience. Documents are stored in uploads/user_documents (Apache: Require all denied) under random
   names and are only ever streamed by PHP to the owner (user/documents/ID) or an administrator (admin/user-documents/ID). */

const USER_MIN_AGE = 50;
const USER_DOC_MIN = 1;
const USER_DOC_MAX = 5;
const USER_DOC_MAX_BYTES = 5 * 1048576;
const USER_DOC_EXTENSIONS = ['pdf', 'jpg', 'jpeg', 'png'];
const USER_DOC_DIR = 'user_documents';

class UserDocumentException extends RuntimeException
{
}

function user_age(string $dob): int
{
    return (int)(new DateTime($dob))->diff(new DateTime('today'))->y; // full years; the birthday counts only once it has occurred
}

// Error message for an invalid date of birth, or null when it is acceptable.
function user_dob_error(string $dob): ?string
{
    if ($dob === '') return 'Please enter your date of birth.';
    if (!valid_date($dob)) return 'Please enter a valid date of birth.';
    if (new DateTime($dob) > new DateTime('today')) return 'Date of birth cannot be in the future.';
    if (user_age($dob) > 120) return 'Please enter a valid date of birth.';
    return null;
}

// Files chosen in <input type="file" name="FIELD[]" multiple>, without the empty "no file selected" entries.
function user_uploaded_files(string $field): array
{
    $f = $_FILES[$field] ?? null;
    if (!is_array($f) || !isset($f['name'])) return [];
    foreach (['name', 'tmp_name', 'error', 'size'] as $k) if (!is_array($f[$k] ?? null)) $f[$k] = [$f[$k] ?? null];
    $out = [];
    foreach ($f['name'] as $i => $name) {
        if (($f['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) continue;
        $out[] = ['name' => (string)$name, 'tmp_name' => (string)($f['tmp_name'][$i] ?? ''), 'error' => (int)($f['error'][$i] ?? UPLOAD_ERR_NO_FILE), 'size' => (int)($f['size'][$i] ?? 0)];
    }
    return $out;
}

// Validates the whole set (count, then every file individually: upload error, size, extension, real MIME type, extension/MIME match).
function user_docs_error(array $files): ?string
{
    $n = count($files);
    if ($n < USER_DOC_MIN) return 'Please upload at least one supporting document.';
    if ($n > USER_DOC_MAX) return 'You can upload a maximum of five documents.';
    $bad = 'One or more files have an unsupported format or exceed the allowed size (PDF, JPG or PNG, up to ' . (USER_DOC_MAX_BYTES / 1048576) . ' MB each).';
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $extOk = ['application/pdf' => ['pdf'], 'image/jpeg' => ['jpg', 'jpeg'], 'image/png' => ['png']];
    foreach ($files as $f) {
        if ($f['error'] !== UPLOAD_ERR_OK) return in_array($f['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true) ? $bad : 'One or more files could not be uploaded. Please try again.';
        if ($f['size'] <= 0 || $f['size'] > USER_DOC_MAX_BYTES || !is_uploaded_file($f['tmp_name'])) return $bad;
        $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, USER_DOC_EXTENSIONS, true)) return $bad;
        $mime = (string)$finfo->file($f['tmp_name']);
        if (!isset($extOk[$mime]) || !in_array($ext, $extOk[$mime], true)) return $bad;
        if ($mime !== 'application/pdf' && !@getimagesize($f['tmp_name'])) return $bad;
    }
    return null;
}

// Moves the files into private storage and records them for $userId. Run inside the registration transaction; $saved collects
// the stored paths so the caller can delete them again if anything fails (no orphaned files).
function user_docs_store(int $userId, array $files, array &$saved): void
{
    foreach ($files as $f) {
        [$path, $err] = save_upload($f, USER_DOC_DIR, DOC_TYPES, USER_DOC_MAX_BYTES, false);
        if ($err !== null || $path === null) throw new UserDocumentException($err ?? 'A document could not be saved.');
        $saved[] = $path;
        $label = mb_substr(preg_replace('/[^\p{L}\p{N} ._()\-]+/u', '', pathinfo($f['name'], PATHINFO_FILENAME)) ?? '', 0, 100);
        q('INSERT INTO user_documents(user_id,document_path,original_name) VALUES(?,?,?)', [$userId, $path, $label !== '' ? $label : null]);
    }
}
function user_docs_cleanup(array $paths): void
{
    foreach ($paths as $rel) {
        $abs = realpath(APP_ROOT . '/' . $rel);
        $base = realpath(APP_ROOT . '/uploads/' . USER_DOC_DIR);
        if ($abs && $base && str_starts_with($abs, $base . DIRECTORY_SEPARATOR) && is_file($abs)) @unlink($abs);
    }
}
function user_documents(int $userId): array
{
    try {
        return rows('SELECT id,document_path,original_name,uploaded_at FROM user_documents WHERE user_id=? ORDER BY id', [$userId]);
    } catch (Throwable $e) {
        return []; // migration 006 not applied yet
    }
}
function handicapped_label($v): string
{
    return $v === null || $v === '' ? 'Not recorded' : ((int)$v === 1 ? 'Yes' : 'No');
}
