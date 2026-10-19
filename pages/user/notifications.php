<?php
$u = require_role('user');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$u['id']]);
    flash('success', 'All notifications marked as read.');
    redirect('user/notifications');
}
$list = rows('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC, id DESC LIMIT 100', [$u['id']]);
$links = notification_links($list, 'user', (int)$u['id']);
layout('user', ['title' => 'Notifications', 'nav' => '']);
?>
<div class="container py-4">
    <div class="page-head">
        <div>
            <h1>Notifications</h1>
        </div>
        <form method="post"><?= csrf_field() ?><button class="btn btn-outline-success btn-sm">Mark all as read</button></form>
    </div>
        <?php render_notification_list($list, $links); ?>
</div>
<?php layout_end();
