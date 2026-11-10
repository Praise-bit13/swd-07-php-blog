<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$posts = db()->query(
    'SELECT p.id, p.title, p.slug, p.status, p.updated_at, c.name AS category_name, a.name AS author_name
     FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
     ORDER BY p.updated_at DESC'
)->fetchAll();
$pageTitle = 'Posts';
require dirname(__DIR__) . '/header.php';
?>
<p><a class="button" href="<?= e(app_url('admin/posts/create.php')) ?>">New post</a></p><?php if (isset($_GET['saved'])): ?><p class="notice">Post saved.</p><?php endif; ?><?php if (isset($_GET['deleted'])): ?><p class="notice">Post deleted.</p><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody><?php foreach ($posts as $post): ?><tr><td><?= e($post['title']) ?></td><td><?= e($post['category_name']) ?></td><td class="status"><?= e($post['status']) ?></td><td><?= e(date('M j, Y', strtotime($post['updated_at']))) ?></td><td><div class="actions"><a href="<?= e(app_url('admin/posts/edit.php?id=' . $post['id'])) ?>">Edit</a><?php if ($post['status'] === 'published'): ?><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>">View</a><?php endif; ?><form class="inline-form" method="post" action="<?= e(app_url('admin/posts/delete.php')) ?>" onsubmit="return confirm('Delete this post?')"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $post['id'] ?>"><button class="danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?><?php if (!$posts): ?><tr><td colspan="5">No posts yet.</td></tr><?php endif; ?></tbody></table></div>
<?php require dirname(__DIR__) . '/footer.php'; ?>
