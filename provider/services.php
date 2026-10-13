<?php require_once __DIR__ . '/_base.php';
$title = 'Provider Services';
include __DIR__ . '/../includes/header.php'; ?>
<div class="dashboard-shell">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="content-area">
        <?php $pid = $pdo->query('SELECT id FROM providers WHERE user_id=' . (int)$u['id'])->fetchColumn();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $sid = (int)$_POST['category_id'];
            if ($sid) {
                $pdo->prepare('INSERT IGNORE INTO provider_categories(provider_id,category_id) VALUES(?,?)')->execute([$pid, $sid]);
            }
            flash('success', 'Service added.');
            redirect('services.php');
        }
        $sv = $pdo->query("SELECT * FROM categories WHERE status='Active' ORDER BY category_name")->fetchAll();
        $mine = $pdo->prepare('SELECT s.* FROM categories s JOIN provider_categories ps ON ps.category_id=s.id WHERE ps.provider_id=?');
        $mine->execute([$pid]); ?><div class="row g-4">
            <div class="col-lg-5">
                <div class="service-card p-4">
                    <h4>Add Service</h4>
                    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><select name="category_id" class="form-select mb-3"><?php foreach ($sv as $s): ?><option value="<?= $s['id'] ?>"><?= e($s['category_name']) ?></option><?php endforeach; ?></select><button class="btn btn-success">Add</button></form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="service-card p-4">
                    <h4>My Services</h4><?php foreach ($mine->fetchAll() as $s): ?><span class="badge text-bg-light border me-2 mb-2 p-2"><?= e($s['category_name']) ?></span><?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
</div><?php include __DIR__ . '/../includes/footer.php'; ?>