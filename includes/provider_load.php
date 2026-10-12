<?php
// A provider's offerings that users may see/book: only under service types the admin has left Active.
function listed_offerings(int $pid): array
{
    return rows("SELECT o.*,s.service_name FROM provider_offerings o JOIN services s ON s.id=o.service_id AND s.status='Active' WHERE o.provider_id=? ORDER BY s.service_name,o.title", [$pid]);
}
// Loads a listed provider by id into [$p,$svcs,$reviews] or returns null.
function load_listed_provider(int $id): ?array
{
    $list = listed_providers(['ids' => [$id]], 1);
    if (!$list) return null;
    $p = $list[0];
    $svcs = rows("SELECT s.* FROM services s JOIN provider_services ps ON ps.service_id=s.id WHERE ps.provider_id=? AND s.status='Active' ORDER BY s.service_name", [$id]);
    $reviews = rows('SELECT r.*,u.name user_name FROM ratings r JOIN users u ON u.id=r.user_id WHERE r.provider_id=? ORDER BY r.created_at DESC', [$id]);
    return [$p, $svcs, $reviews];
}
