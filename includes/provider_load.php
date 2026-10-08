<?php
// Loads a listed provider by id into [$p,$svcs,$reviews] or returns null.
function load_listed_provider(int $id): ?array {
    $list = listed_providers(['ids' => [$id]], 1);
    if (!$list) return null;
    $p = $list[0];
    $svcs = rows("SELECT s.* FROM services s JOIN provider_services ps ON ps.service_id=s.id WHERE ps.provider_id=? AND s.status='Active' ORDER BY s.service_name", [$id]);
    $reviews = rows('SELECT r.*,u.name user_name FROM ratings r JOIN users u ON u.id=r.user_id WHERE r.provider_id=? ORDER BY r.created_at DESC', [$id]);
    return [$p, $svcs, $reviews];
}
