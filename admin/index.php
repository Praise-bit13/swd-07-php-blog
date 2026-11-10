<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
$counts = [
    'posts' => (int) db()->query('SELECT COUNT(*) FROM posts')->fetchColumn(),
    'published' => (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn(),
    'drafts' => (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'")->fetchColumn(),
    'categories' => (int) db()->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
];
$pageTitle = 'Dashboard';
require __DIR__ . '/header.php';
?>
<div class="grid"><section class="panel"><h2><?= $counts['posts'] ?></h2><p>Total posts</p></section><section class="panel"><h2><?= $counts['published'] ?></h2><p>Published</p></section><section class="panel"><h2><?= $counts['drafts'] ?></h2><p>Drafts</p></section><section class="panel"><h2><?= $counts['categories'] ?></h2><p>Categories</p></section></div>
<section class="panel" style="margin-top:1.5rem"><h2>Get started</h2><p><a class="button" href="<?= e(app_url('admin/posts/create.php')) ?>">Write a post</a> <a class="button secondary" href="<?= e(app_url('admin/categories/create.php')) ?>">Add a category</a></p></section>
<?php require __DIR__ . '/footer.php'; ?>
