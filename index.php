<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 9;
$total = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$statement = db()->prepare(
    "SELECT p.id, p.title, p.slug, p.excerpt, p.cover_image, p.published_at, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
     WHERE p.status = 'published' ORDER BY p.published_at DESC, p.id DESC LIMIT :limit OFFSET :offset"
);
$statement->bindValue('limit', $perPage, PDO::PARAM_INT);
$statement->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$statement->execute();
$posts = $statement->fetchAll();
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Blog</title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head>
<body><header class="site-header"><a class="brand" href="<?= e(app_url()) ?>">SWD Blog</a><nav class="site-nav"><a href="<?= e(app_url('search.php')) ?>">Search</a><a href="<?= e(app_url('admin/login.php')) ?>">Admin</a></nav></header>
<main class="container"><section class="hero"><h1>Stories and ideas</h1><p>Latest articles from our team.</p><form class="search-box" action="<?= e(app_url('search.php')) ?>" method="get"><input name="q" placeholder="Search articles" aria-label="Search articles"><button>Search</button></form></section>
<?php if (!$posts): ?><p>No published posts yet.</p><?php else: ?><div class="grid">
<?php foreach ($posts as $post): ?><article class="card"><?php if ($post['cover_image']): ?><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><img src="<?= e(app_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""></a><?php endif; ?><p class="meta"><a href="<?= e(app_url('category.php?slug=' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?></p><h2><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2><p><?= e($post['excerpt']) ?></p><p class="meta">By <?= e($post['author_name']) ?></p><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>">Read article →</a></article><?php endforeach; ?>
</div><?php endif; ?>
<?php if ($total > $perPage): ?><nav class="pagination"><?php if ($page > 1): ?><a class="button secondary" href="?page=<?= $page - 1 ?>">Previous</a><?php endif; ?><?php if ($page * $perPage < $total): ?><a class="button" href="?page=<?= $page + 1 ?>">Next</a><?php endif; ?></nav><?php endif; ?>
</main><footer>SWD Blog</footer></body></html>
