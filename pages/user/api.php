<?php
$u = require_role('user'); check_csrf();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') json_out(['ok' => false, 'message' => 'Invalid request.'], 405);
if (post('action') === 'favorite') {
    $pid = (int)post('provider_id');
    if (!val('SELECT COUNT(*) FROM providers WHERE id=?', [$pid])) json_out(['ok' => false, 'message' => 'Provider not found.'], 404);
    $has = row('SELECT id FROM favorites WHERE user_id=? AND provider_id=?', [$u['id'], $pid]);
    if ($has) { q('DELETE FROM favorites WHERE id=?', [$has['id']]); json_out(['ok' => true, 'on' => false, 'message' => 'Removed from favorites.']); }
    q('INSERT INTO favorites(user_id,provider_id) VALUES(?,?)', [$u['id'], $pid]); json_out(['ok' => true, 'on' => true, 'message' => 'Added to favorites.']);
}
json_out(['ok' => false, 'message' => 'Unknown action.'], 400);
