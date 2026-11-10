<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_admin();

$summary = db()->query(
    "SELECT
        COUNT(*) AS total_posts,
        COUNT(CASE WHEN status = 'published' THEN 1 END) AS published_posts,
        COUNT(CASE WHEN status = 'draft' THEN 1 END) AS draft_posts,
        (SELECT COUNT(*) FROM categories) AS total_categories
     FROM posts"
)->fetch();

$categoryStats = db()->query(
    "SELECT c.id, c.name, c.slug, COUNT(p.id) AS total_posts,
        COUNT(CASE WHEN p.status = 'published' THEN 1 END) AS published_posts,
        COUNT(CASE WHEN p.status = 'draft' THEN 1 END) AS draft_posts
     FROM categories c
     LEFT JOIN posts p ON p.category_id = c.id
     GROUP BY c.id, c.name, c.slug
     ORDER BY total_posts DESC, c.name ASC"
)->fetchAll();

$latestPosts = db()->query(
    'SELECT p.id, p.title, p.slug, p.status, p.updated_at, c.name AS category_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     ORDER BY p.updated_at DESC, p.id DESC
     LIMIT 5'
)->fetchAll();

$pageTitle = 'Dashboard';
require __DIR__ . '/header.php';
?>
<section class="grid" aria-label="Blog overview">
    <article class="panel"><p class="meta">All posts</p><h2><?= (int) $summary['total_posts'] ?></h2></article>
    <article class="panel"><p class="meta">Published</p><h2><?= (int) $summary['published_posts'] ?></h2></article>
    <article class="panel"><p class="meta">Drafts</p><h2><?= (int) $summary['draft_posts'] ?></h2></article>
    <article class="panel"><p class="meta">Categories</p><h2><?= (int) $summary['total_categories'] ?></h2></article>
</section>

<section class="panel" style="margin-top:1.5rem">
    <div class="admin-top"><h2>Latest posts</h2><a href="<?= e(app_url('admin/posts/index.php')) ?>">All posts →</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($latestPosts as $post): ?>
                <tr>
                    <td><?= e($post['title']) ?></td>
                    <td><?= e($post['category_name']) ?></td>
                    <td><span class="status-badge <?= e($post['status']) ?>"><?= e($post['status']) ?></span></td>
                    <td><?= e(date('M j, Y', strtotime($post['updated_at']))) ?></td>
                    <td><a href="<?= e(app_url('admin/posts/edit.php?id=' . (int) $post['id'])) ?>">Edit</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$latestPosts): ?><tr><td colspan="5">No posts yet. Create a post to get started.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel" style="margin-top:1.5rem">
    <div class="admin-top"><h2>Posts by category</h2><a href="<?= e(app_url('admin/categories/index.php')) ?>">Manage categories →</a></div>
    <div class="table-wrap">
        <table>
            <thead><tr><th>Category</th><th>Total</th><th>Published</th><th>Drafts</th></tr></thead>
            <tbody>
            <?php foreach ($categoryStats as $category): ?>
                <tr>
                    <td><a href="<?= e(app_url('category/' . rawurlencode($category['slug']))) ?>"><?= e($category['name']) ?></a></td>
                    <td><?= (int) $category['total_posts'] ?></td>
                    <td><?= (int) $category['published_posts'] ?></td>
                    <td><?= (int) $category['draft_posts'] ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$categoryStats): ?><tr><td colspan="4">No categories yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<section class="panel" style="margin-top:1.5rem">
    <h2>Quick actions</h2>
    <p><a class="button" href="<?= e(app_url('admin/posts/create.php')) ?>">Write a post</a> <a class="button secondary" href="<?= e(app_url('admin/categories/create.php')) ?>">Add a category</a></p>
</section>
<?php require __DIR__ . '/footer.php'; ?>
