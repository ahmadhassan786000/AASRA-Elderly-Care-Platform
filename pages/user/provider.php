<?php
$u = require_role('user');
require_once APP_ROOT . '/includes/provider_load.php';
$d = load_listed_provider((int)get('id')); if (!$d) { show_error_page(404); exit; }
[$p, $svcs, $reviews] = $d; $area = 'user';
$isFav = (bool)row('SELECT id FROM favorites WHERE user_id=? AND provider_id=?', [$u['id'], $p['id']]);
layout('user', ['title' => $p['name'], 'nav' => 'providers']); echo '<div class="container py-4">';
include APP_ROOT . '/includes/partials/provider_profile.php';
echo '</div>'; layout_end();
