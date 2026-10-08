<?php
require_once APP_ROOT . '/includes/provider_load.php';
$d = load_listed_provider((int)get('id')); if (!$d) { show_error_page(404); exit; }
[$p, $svcs, $reviews] = $d; $area = 'public'; $isFav = false;
layout('public', ['title' => $p['name'], 'nav' => 'services']); echo '<div class="container py-4">';
include APP_ROOT . '/includes/partials/provider_profile.php';
echo '</div>'; layout_end();
