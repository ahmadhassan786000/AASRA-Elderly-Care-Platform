<?php
$u = require_role('user');
if ($_SERVER['REQUEST_METHOD'] === 'POST') { check_csrf(); q('UPDATE notifications SET is_read=1 WHERE user_id=?', [$u['id']]); flash('success', 'All notifications marked as read.'); redirect('user/notifications'); }
$list = rows('SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 100', [$u['id']]);
layout('user', ['title' => 'Notifications', 'nav' => '']);
?>
<div class="container py-4"><div class="page-head"><div><h1>Notifications</h1></div><form method="post"><?= csrf_field() ?><button class="btn btn-outline-success btn-sm">Mark all as read</button></form></div>
<div class="card-soft flush"><?php foreach ($list as $n): ?><div class="p-3 border-bottom <?= $n['is_read'] ? '' : 'bg-success-subtle' ?>"><b><?= e($n['title']) ?></b><div class="text-secondary"><?= e($n['message']) ?></div><small class="text-secondary"><?= e(fmt_date($n['created_at'], 'd M Y, g:i A')) ?></small></div><?php endforeach; ?>
<?php if (!$list): ?><div class="empty-state"><i class="bi bi-bell"></i><p class="mb-0 mt-2">Nothing new.</p></div><?php endif; ?></div></div>
<?php layout_end();
