<?php
$limit = max(1, min(24, (int)get('limit', '12')));
$list = listed_providers(['q' => get('q'), 'service' => (int)get('service')], $limit);
json_out(['ok' => true, 'providers' => array_map(fn($p) => [
    'id' => (int)$p['id'], 'name' => $p['name'], 'initial' => initials($p['name']), 'photo' => $p['photo'] ? upload_url($p['photo']) : '',
    'rating' => round((float)$p['rating'], 1), 'reviews' => (int)$p['reviews'], 'location' => $p['location'] ?: 'Lahore',
    'bio' => mb_strimwidth((string)$p['bio'], 0, 140, '…'), 'charges' => (float)$p['charges'], 'services' => $p['services'],
], $list)]);
