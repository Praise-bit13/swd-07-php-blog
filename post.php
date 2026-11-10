<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
$slug = trim((string) ($_GET['slug'] ?? ''));
$statement = db()->prepare(
    "SELECT p.title, p.body, p.cover_image, p.published_at, c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
     WHERE p.slug = :slug AND p.status = 'published' LIMIT 1"
);
$statement->execute(['slug' => $slug]);
$post = $statement->fetch();
if (!$post) {
    http_response_code(404);
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $post ? e($post['title']) . ' | SWD Blog' : 'Post not found | SWD Blog' ?></title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head>
<body><header class="site-header"><a class="brand" href="<?= e(app_url()) ?>">SWD Blog</a><nav class="site-nav"><a href="<?= e(app_url('search.php')) ?>">Search</a></nav></header><main class="container">
<?php if (!$post): ?><h1>Post not found</h1><p>This article may have been removed or is not published.</p><a href="<?= e(app_url()) ?>">Return to the blog</a><?php else: ?>
<article class="panel"><p class="meta"><a href="<?= e(app_url('category.php?slug=' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?> · By <?= e($post['author_name']) ?></p><h1><?= e($post['title']) ?></h1><?php if ($post['cover_image']): ?><img class="post-cover" src="<?= e(app_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""><?php endif; ?><div class="article-body"><?= e($post['body']) ?></div></article><?php endif; ?>
</main></body></html>
