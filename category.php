<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
$slug = trim((string) ($_GET['slug'] ?? ''));
$statement = db()->prepare('SELECT id, name FROM categories WHERE slug = :slug LIMIT 1');
$statement->execute(['slug' => $slug]);
$category = $statement->fetch();
if (!$category) {
    http_response_code(404);
    $posts = [];
} else {
    $statement = db()->prepare(
        "SELECT p.title, p.slug, p.excerpt, p.cover_image, p.published_at, a.name AS author_name
         FROM posts p JOIN admins a ON a.id = p.author_id
         WHERE p.category_id = :category_id AND p.status = 'published'
         ORDER BY p.published_at DESC, p.id DESC"
    );
    $statement->execute(['category_id' => $category['id']]);
    $posts = $statement->fetchAll();
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= $category ? e($category['name']) . ' | SWD Blog' : 'Category not found' ?></title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head>
<body><header class="site-header"><a class="brand" href="<?= e(app_url()) ?>">SWD Blog</a><nav class="site-nav"><a href="<?= e(app_url('search.php')) ?>">Search</a></nav></header><main class="container"><h1><?= $category ? e($category['name']) : 'Category not found' ?></h1>
<?php if (!$category): ?><p>This category does not exist.</p><?php elseif (!$posts): ?><p>No published posts in this category yet.</p><?php else: ?><div class="grid"><?php foreach ($posts as $post): ?><article class="card"><?php if ($post['cover_image']): ?><img src="<?= e(app_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt=""><?php endif; ?><p class="meta"><?= e(date('M j, Y', strtotime($post['published_at']))) ?> · By <?= e($post['author_name']) ?></p><h2><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2><p><?= e($post['excerpt']) ?></p></article><?php endforeach; ?></div><?php endif; ?>
</main></body></html>
