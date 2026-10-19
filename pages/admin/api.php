<?php
// AJAX endpoint for simple admin actions. POST only, admin only, CSRF protected.
$admin = require_role('admin');
require_once APP_ROOT . '/includes/admin_helpers.php';
check_csrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'message' => 'Invalid request.'], 405);
$action = post('action');
$id = (int)post('id');
$value = post('value');
function fail(string $m, int $c = 422): void
{
    json_out(['ok' => false, 'message' => $m], $c);
}

switch ($action) {
    case 'user_status':
        if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
        $t = row('SELECT id,role,name FROM users WHERE id=?', [$id]);
        if (!$t) fail('User not found.', 404);
        if ($t['role'] === 'admin' || $id === $admin['id']) fail('Administrator accounts cannot be changed here.');
        q('UPDATE users SET status=? WHERE id=?', [$value, $id]);
        if ($t['role'] === 'provider') q('UPDATE providers SET account_status=? WHERE user_id=?', [$value, $id]);
        json_out(['ok' => true, 'message' => ($value === 'Active' ? 'User activated' : 'User deactivated') . ' successfully.', 'key' => "user_status:$id", 'status' => $value, 'badge' => badge($value)]);

    case 'provider_status':
        if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
        $p = row('SELECT id,user_id FROM providers WHERE id=?', [$id]);
        if (!$p) fail('Provider not found.', 404);
        q('UPDATE providers SET account_status=? WHERE id=?', [$value, $id]);
        q('UPDATE users SET status=? WHERE id=?', [$value, $p['user_id']]);
        notify(null, $id, 'Account ' . strtolower($value), 'An administrator set your provider account to ' . $value . '.', 'account');
        json_out(['ok' => true, 'message' => 'Provider ' . ($value === 'Active' ? 'activated' : 'deactivated') . ' successfully.', 'key' => "provider_status:$id", 'status' => $value, 'badge' => badge($value)]);

    case 'verification':
        if (!in_array($value, ['Approved', 'Rejected', 'Pending'], true)) fail('Invalid status.');
        $p = row('SELECT id,user_id,verification_status FROM providers WHERE id=?', [$id]);
        if (!$p) fail('Provider not found.', 404);
        $note = $value === 'Rejected' ? mb_substr(post('note'), 0, 500) : null;
        q('UPDATE providers SET verification_status=?,verification_note=? WHERE id=?', [$value, $note ?: null, $id]);
        $extra = [];
        if ($value === 'Approved') {
            q("UPDATE providers SET account_status='Active' WHERE id=?", [$id]);
            q("UPDATE users SET status='Active' WHERE id=?", [$p['user_id']]);
            q("UPDATE provider_documents SET verification_status='Approved' WHERE provider_id=? AND verification_status='Pending'", [$id]);
            $extra[] = ['key' => "provider_status:$id", 'badge' => badge('Active')];
        }
        if ($p['verification_status'] !== $value) notify_verification_update($id, $value, $note); // only when the status really changed
        $pendingVerificationCount = (int) val("SELECT COUNT(*) FROM providers p WHERE p.verification_status='Pending' AND EXISTS (SELECT 1 FROM provider_documents d WHERE d.provider_id=p.id)");
        json_out(['ok' => true, 'message' => $value === 'Approved' ? 'Provider approved and activated.' : ($value === 'Rejected' ? 'Provider rejected.' : 'Provider set to pending.'), 'key' => "provider_verification:$id", 'status' => $value, 'badge' => badge($value), 'extra' => $extra, 'pendingVerificationCount' => $pendingVerificationCount]);

    case 'doc_status':
        if (!in_array($value, ['Approved', 'Rejected'], true)) fail('Invalid status.');
        if (!row('SELECT id FROM provider_documents WHERE id=?', [$id])) fail('Document not found.', 404);
        q('UPDATE provider_documents SET verification_status=? WHERE id=?', [$value, $id]);
        json_out(['ok' => true, 'message' => 'Document ' . strtolower($value) . '.', 'key' => "doc:$id", 'status' => $value, 'badge' => badge($value)]);

    case 'category_status':
        if (!in_array($value, ['Active', 'Inactive'], true)) fail('Invalid status.');
        if (!row('SELECT id FROM categories WHERE id=?', [$id])) fail('Category not found.', 404);
        q('UPDATE categories SET status=? WHERE id=?', [$value, $id]);
        json_out(['ok' => true, 'message' => 'Status updated successfully.', 'key' => "category_status:$id", 'status' => $value, 'badge' => badge($value)]);

    case 'complaint_status':
        if (!in_array($value, ['Pending', 'Under Review', 'Resolved'], true)) fail('Invalid status.');
        $c = row('SELECT id,user_id,provider_id,booking_id,status FROM complaints WHERE id=?', [$id]);
        if (!$c) fail('Complaint not found.', 404);
        q('UPDATE complaints SET status=?,resolved_at=IF(?="Resolved",NOW(),NULL) WHERE id=?', [$value, $value, $id]);
        if ($c['status'] !== $value) notify_complaint_update($c, $value); // only when the status really changed
        json_out(['ok' => true, 'message' => 'Status updated successfully.', 'key' => "complaint_status:$id", 'status' => $value, 'badge' => badge($value), 'pendingComplaintCount' => (int) val("SELECT COUNT(*) FROM complaints WHERE status IN ('Pending','Under Review')")]);

    case 'delete_user':
        $t = row('SELECT id,role FROM users WHERE id=?', [$id]);
        if (!$t) fail('User not found.', 404);
        if ($t['role'] === 'admin' || $id === $admin['id']) fail('Administrator accounts cannot be deleted.');
        $docs = $t['role'] === 'provider' ? rows('SELECT d.document_path,p.photo FROM providers p LEFT JOIN provider_documents d ON d.provider_id=p.id WHERE p.user_id=?', [$id]) : [];
        try {
            q('DELETE FROM users WHERE id=?', [$id]);
        } catch (mysqli_sql_exception $e) {
            fail(delete_error($e));
        }
        foreach ($docs as $d) {
            delete_upload($d['document_path'] ?? null);
            delete_upload($d['photo'] ?? null);
        }
        json_out(['ok' => true, 'removed' => true, 'message' => 'Account deleted successfully.']);

    case 'delete_provider':
        $p = row('SELECT id,user_id,photo FROM providers WHERE id=?', [$id]);
        if (!$p) fail('Provider not found.', 404);
        $docs = rows('SELECT document_path FROM provider_documents WHERE provider_id=?', [$id]);
        try {
            q('DELETE FROM users WHERE id=?', [$p['user_id']]);
        } catch (mysqli_sql_exception $e) {
            fail(delete_error($e));
        }
        foreach ($docs as $d) delete_upload($d['document_path']);
        delete_upload($p['photo']);
        json_out(['ok' => true, 'removed' => true, 'message' => 'Provider deleted successfully.']);

    case 'delete_category':
        $s = row('SELECT id,image FROM categories WHERE id=?', [$id]);
        if (!$s) fail('Category not found.', 404);
        // Explicit check as well as the foreign key, so a category with bookings is never removed even if a constraint is missing.
        if ((int)val('SELECT COUNT(*) FROM bookings WHERE category_id=?', [$id]) > 0) {
            fail('This category is used by bookings and cannot be deleted. Set it to Inactive instead.');
        }
        try {
            q('DELETE FROM categories WHERE id=?', [$id]);
        } catch (mysqli_sql_exception $e) {
            fail('This category is used by bookings and cannot be deleted. Set it to Inactive instead.');
        }
        delete_upload($s['image']);
        json_out(['ok' => true, 'removed' => true, 'message' => 'Category deleted successfully.']);

    case 'delete_category_image':
        $s = row('SELECT id,image FROM categories WHERE id=?', [$id]);
        if (!$s) fail('Category not found.', 404);
        delete_upload($s['image']);
        q('UPDATE categories SET image=NULL WHERE id=?', [$id]);
        json_out(['ok' => true, 'removed' => true, 'redirect' => "admin/categories/$id/edit", 'message' => 'Image removed.']);
}
fail('Unknown action.', 400);
