<?php require_once __DIR__ . '/_base.php';
$title = 'Categories';
include __DIR__ . '/../includes/header.php'; ?>
<div class="dashboard-shell">
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>
    <section class="content-area">
        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_csrf();
            $action = post('action');
            if ($action === 'add') {
                $pdo->prepare('INSERT INTO categories(category_name,description,status) VALUES(?,?,"Active")')->execute([post('category_name'), post('description')]);
            } elseif ($action === 'toggle') {
                $pdo->prepare('UPDATE categories SET status=IF(status="Active","Inactive","Active") WHERE id=?')->execute([(int)$_POST['id']]);
            }
            flash('success', 'Category updated.');
            redirect('categories.php');
        }
        $rows = $pdo->query('SELECT * FROM categories ORDER BY category_name')->fetchAll(); ?><div class="row g-4">
            <div class="col-lg-5">
                <div class="service-card p-4">
                    <h4>Add Category</h4>
                    <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input name="category_name" class="form-control mb-2" placeholder="Category Name" required><textarea name="description" class="form-control mb-2" placeholder="Description"></textarea><button class="btn btn-success">Add</button></form>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="service-card p-3">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody><?php foreach ($rows as $s): ?><tr>
                                    <td><?= e($s['category_name']) ?></td>
                                    <td><?= badge($s['status']) ?></td>
                                    <td>
                                        <form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $s['id'] ?>"><button class="btn btn-sm btn-outline-secondary">Toggle</button></form>
                                    </td>
                                </tr><?php endforeach; ?></tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div><?php include __DIR__ . '/../includes/footer.php'; ?>