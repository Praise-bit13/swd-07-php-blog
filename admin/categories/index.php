<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$categories = db()->query(
    'SELECT c.id, c.name, c.slug, COUNT(p.id) AS post_count FROM categories c
     LEFT JOIN posts p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name'
)->fetchAll();
$pageTitle = 'Categories';
require dirname(__DIR__) . '/header.php';
?>
<p><a class="button" href="<?= e(app_url('admin/categories/create.php')) ?>">New category</a></p><?php if (isset($_GET['saved'])): ?><p class="notice">Category saved.</p><?php endif; ?><?php if (isset($_GET['deleted'])): ?><p class="notice">Category deleted.</p><?php endif; ?><?php if (isset($_GET['error'])): ?><p class="notice">This category is in use by posts and cannot be deleted.</p><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Name</th><th>Slug</th><th>Posts</th><th>Actions</th></tr></thead><tbody><?php foreach ($categories as $category): ?><tr><td><?= e($category['name']) ?></td><td><?= e($category['slug']) ?></td><td><?= (int) $category['post_count'] ?></td><td><div class="actions"><a href="<?= e(app_url('admin/categories/edit.php?id=' . $category['id'])) ?>">Edit</a><form class="inline-form" method="post" action="<?= e(app_url('admin/categories/delete.php')) ?>" onsubmit="return confirm('Delete this category?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $category['id'] ?>"><button class="danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$categories): ?><tr><td colspan="4">No categories yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__) . '/footer.php'; ?>
