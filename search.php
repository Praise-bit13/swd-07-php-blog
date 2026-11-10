<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';
$query = trim((string) ($_GET['q'] ?? ''));
$posts = [];
if ($query !== '') {
    $statement = db()->prepare(
        "SELECT p.title, p.slug, p.body, p.cover_image, p.published_at, c.name AS category_name, a.name AS author_name,
         MATCH(p.title, p.body) AGAINST (:search IN NATURAL LANGUAGE MODE) AS relevance
         FROM posts p JOIN categories c ON c.id = p.category_id JOIN admins a ON a.id = p.author_id
         WHERE p.status = 'published' AND MATCH(p.title, p.body) AGAINST (:filter IN NATURAL LANGUAGE MODE)
         ORDER BY relevance DESC, p.published_at DESC LIMIT 50"
    );
    $statement->execute(['search' => $query, 'filter' => $query]);
    $posts = $statement->fetchAll();
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Search | SWD Blog</title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head>
<body><header class="site-header"><a class="brand" href="<?= e(app_url()) ?>">SWD Blog</a></header><main class="container"><h1>Search articles</h1><form class="search-box" action="<?= e(app_url('search.php')) ?>" method="get"><input name="q" value="<?= e($query) ?>" placeholder="Search articles" aria-label="Search articles"><button>Search</button></form>
<?php if ($query !== ''): ?><h2>Results for “<?= e($query) ?>”</h2><?php if (!$posts): ?><p>No matching published articles found.</p><?php else: ?><div class="grid"><?php foreach ($posts as $post): ?><article class="card"><p class="meta"><?= e($post['category_name']) ?> · <?= e(date('M j, Y', strtotime($post['published_at']))) ?></p><h2><a href="<?= e(app_url('post.php?slug=' . rawurlencode($post['slug']))) ?>"><?= e($post['title']) ?></a></h2><p><?= e(excerpt($post['body'])) ?></p><p class="meta">By <?= e($post['author_name']) ?></p></article><?php endforeach; ?></div><?php endif; ?><?php endif; ?>
</main></body></html>
