<?php
$u = require_role('provider');
$p = current_provider();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    q('UPDATE notifications SET is_read=1 WHERE provider_id=?', [$p['id']]);
    flash('success', 'All notifications marked as read.');
    redirect('provider/notifications');
}
$list = rows('SELECT * FROM notifications WHERE provider_id=? ORDER BY created_at DESC, id DESC LIMIT 100', [$p['id']]);
$links = notification_links($list, 'provider', (int)$p['id']);
layout('provider', ['title' => 'Notifications', 'active' => 'notifications']);
?>
<div class="page-head">
    <div>
        <h1>Notifications</h1>
    </div>
    <form method="post"><?= csrf_field() ?><button class="btn btn-outline-success btn-sm">Mark all as read</button></form>
</div>
<?php render_notification_list($list, $links); ?>
<?php layout_end();
