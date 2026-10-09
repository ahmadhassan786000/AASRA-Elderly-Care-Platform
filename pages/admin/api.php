<?php
// AJAX endpoint for simple admin actions. POST only, admin only, CSRF protected.
$admin = require_role('admin'); require_once APP_ROOT . '/includes/admin_helpers.php'; check_csrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'message' => 'Invalid request.'], 405);
$action = post('action'); $id = (int)post('id'); $value = post('value');
function fail(string $m, int $c = 422): void { json_out(['ok' => false, 'message' => $m], $c); }

switch ($action) {
case 'user_status':
    if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
    $t = row('SELECT id,role,name FROM users WHERE id=?', [$id]); if (!$t) fail('User not found.', 404);
    if ($t['role'] === 'admin' || $id === $admin['id']) fail('Administrator accounts cannot be changed here.');
    q('UPDATE users SET status=? WHERE id=?', [$value, $id]);
    if ($t['role'] === 'provider') q('UPDATE providers SET account_status=? WHERE user_id=?', [$value, $id]);
    json_out(['ok' => true, 'message' => ($value === 'Active' ? 'User activated' : 'User deactivated') . ' successfully.', 'key' => "user_status:$id", 'status' => $value, 'badge' => badge($value)]);

case 'provider_status':
    if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
    $p = row('SELECT id,user_id FROM providers WHERE id=?', [$id]); if (!$p) fail('Provider not found.', 404);
    q('UPDATE providers SET account_status=? WHERE id=?', [$value, $id]);
    q('UPDATE users SET status=? WHERE id=?', [$value, $p['user_id']]);
    notify(null, $id, 'Account ' . strtolower($value), 'An administrator set your provider account to ' . $value . '.', 'account');
    json_out(['ok' => true, 'message' => 'Provider ' . ($value === 'Active' ? 'activated' : 'deactivated') . ' successfully.', 'key' => "provider_status:$id", 'status' => $value, 'badge' => badge($value)]);

case 'verification':
    if (!in_array($value, ['Approved', 'Rejected', 'Pending'], true)) fail('Invalid status.');
    $p = row('SELECT id,user_id FROM providers WHERE id=?', [$id]); if (!$p) fail('Provider not found.', 404);
    $note = $value === 'Rejected' ? mb_substr(post('note'), 0, 500) : null;
    q('UPDATE providers SET verification_status=?,verification_note=? WHERE id=?', [$value, $note ?: null, $id]);
    $extra = [];
    if ($value === 'Approved') {
        q("UPDATE providers SET account_status='Active' WHERE id=?", [$id]); q("UPDATE users SET status='Active' WHERE id=?", [$p['user_id']]);
        q("UPDATE provider_documents SET verification_status='Approved' WHERE provider_id=? AND verification_status='Pending'", [$id]);
        $extra[] = ['key' => "provider_status:$id", 'badge' => badge('Active')];
    }
    notify(null, $id, 'Verification ' . strtolower($value), 'Your verification status is now ' . $value . '.' . ($note ? ' Note: ' . $note : ''), 'verification');
    $pendingVerificationCount = (int) val("SELECT COUNT(*) FROM providers p WHERE p.verification_status='Pending' AND EXISTS (SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id)");
json_out(['ok' => true, 'message' => $value === 'Approved' ? 'Provider approved and activated.' : ($value === 'Rejected' ? 'Provider rejected.' : 'Provider set to pending.'), 'key' => "provider_verification:$id", 'status' => $value, 'badge' => badge($value), 'extra' => $extra, 'pendingVerificationCount' => $pendingVerificationCount]);

case 'doc_status':
    if (!in_array($value, ['Approved', 'Rejected'], true)) fail('Invalid status.');
    if (!row('SELECT id FROM provider_documents WHERE id=?', [$id])) fail('Document not found.', 404);
    q('UPDATE provider_documents SET verification_status=? WHERE id=?', [$value, $id]);
    json_out(['ok' => true, 'message' => 'Document ' . strtolower($value) . '.', 'key' => "doc:$id", 'status' => $value, 'badge' => badge($value)]);

case 'service_status':
    if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
    if (!row('SELECT id FROM services WHERE id=?', [$id])) fail('Service not found.', 404);
    q('UPDATE services SET status=? WHERE id=?', [$value, $id]);
    json_out(['ok' => true, 'message' => 'Status updated successfully.', 'key' => "service_status:$id", 'status' => $value, 'badge' => badge($value)]);

case 'complaint_status':
    if (!in_array($value, ['Pending', 'Under Review', 'Resolved'], true)) fail('Invalid status.');
    $c = row('SELECT id,user_id FROM complaints WHERE id=?', [$id]); if (!$c) fail('Complaint not found.', 404);
    q('UPDATE complaints SET status=?,resolved_at=IF(?="Resolved",NOW(),NULL) WHERE id=?', [$value, $value, $id]);
    notify((int)$c['user_id'], null, 'Complaint update', 'Your complaint #' . $id . ' is now ' . $value . '.', 'complaint');
    json_out(['ok' => true, 'message' => 'Status updated successfully.', 'key' => "complaint_status:$id", 'status' => $value, 'badge' => badge($value)]);

case 'delete_user':
    $t = row('SELECT id,role FROM users WHERE id=?', [$id]); if (!$t) fail('User not found.', 404);
    if ($t['role'] === 'admin' || $id === $admin['id']) fail('Administrator accounts cannot be deleted.');
    $docs = $t['role'] === 'provider' ? rows('SELECT d.document_path,p.photo FROM providers p LEFT JOIN provider_documents d ON d.provider_id=p.id WHERE p.user_id=?', [$id]) : [];
    try { q('DELETE FROM users WHERE id=?', [$id]); } catch (PDOException $e) { fail(delete_error($e)); }
    foreach ($docs as $d) { delete_upload($d['document_path'] ?? null); delete_upload($d['photo'] ?? null); }
    json_out(['ok' => true, 'removed' => true, 'message' => 'Account deleted successfully.']);

case 'delete_provider':
    $p = row('SELECT id,user_id,photo FROM providers WHERE id=?', [$id]); if (!$p) fail('Provider not found.', 404);
    $docs = rows('SELECT document_path FROM provider_documents WHERE provider_id=?', [$id]);
    try { q('DELETE FROM users WHERE id=?', [$p['user_id']]); } catch (PDOException $e) { fail(delete_error($e)); }
    foreach ($docs as $d) delete_upload($d['document_path']); delete_upload($p['photo']);
    json_out(['ok' => true, 'removed' => true, 'message' => 'Provider deleted successfully.']);

case 'delete_service':
    $s = row('SELECT id,image FROM services WHERE id=?', [$id]); if (!$s) fail('Service not found.', 404);
    try { q('DELETE FROM services WHERE id=?', [$id]); } catch (PDOException $e) { fail('This service is used by bookings and cannot be deleted. Set it to Inactive instead.'); }
    delete_upload($s['image']);
    json_out(['ok' => true, 'removed' => true, 'message' => 'Service deleted successfully.']);

case 'delete_service_image':
    $s = row('SELECT id,image FROM services WHERE id=?', [$id]); if (!$s) fail('Service not found.', 404);
    delete_upload($s['image']); q('UPDATE services SET image=NULL WHERE id=?', [$id]);
    json_out(['ok' => true, 'removed' => true, 'redirect' => "admin/services/$id/edit", 'message' => 'Image removed.']);
}
fail('Unknown action.', 400);
