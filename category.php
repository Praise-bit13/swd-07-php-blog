<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$statement = db()->prepare('SELECT id, name, slug FROM categories WHERE slug = :slug LIMIT 1');
$statement->execute(['slug' => $slug]);
$category = $statement->fetch();
$posts = [];
$total = 0;
$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 6;
$totalPages = 1;

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Category not found — SWD Journal';
    $pageDescription = 'This story category could not be found.';
} else {
    $count = db()->prepare("SELECT COUNT(*) FROM posts WHERE category_id = :category_id AND status = 'published'");
    $count->execute(['category_id' => $category['id']]);
    $total = (int) $count->fetchColumn();
    $totalPages = max(1, (int) ceil($total / $perPage));
    $page = min($page, $totalPages);

    $postsStatement = db()->prepare(
        "SELECT p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
                c.name AS category_name, c.slug AS category_slug, a.name AS author_name
         FROM posts p
         JOIN categories c ON c.id = p.category_id
         JOIN admins a ON a.id = p.author_id
         WHERE p.category_id = :category_id AND p.status = 'published'
         ORDER BY p.published_at DESC, p.id DESC
         LIMIT :limit OFFSET :offset"
    );
    $postsStatement->bindValue('category_id', (int) $category['id'], PDO::PARAM_INT);
    $postsStatement->bindValue('limit', $perPage, PDO::PARAM_INT);
    $postsStatement->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
    $postsStatement->execute();
    $posts = $postsStatement->fetchAll();
    $pageTitle = $category['name'] . ' — SWD Journal';
    $pageDescription = 'Explore the latest stories in ' . $category['name'] . '.';
}
require __DIR__ . '/app/public-header.php';
?>
<?php if (!$category): ?>
    <section class="not-found"><p class="eyebrow">404 · CATEGORY NOT FOUND</p><h1>We couldn't find that collection.</h1><p>Try browsing all of our latest stories instead.</p><a class="button-link" href="<?= e(app_url()) ?>">Explore all stories <span aria-hidden="true">→</span></a></section>
<?php else: ?>
    <section class="category-hero">
        <p class="eyebrow">EXPLORE A TOPIC</p>
        <h1><?= e($category['name']) ?></h1>
        <p><?= number_format($total) ?> <?= $total === 1 ? 'story' : 'stories' ?> selected for curious minds.</p>
    </section>
    <section class="content-section content-section--category">
        <?php if (!$posts): ?>
            <div class="empty-state"><span class="empty-state__icon" aria-hidden="true">✳</span><h2>More stories are on the way.</h2><p>There are no published posts in this category just yet.</p><a class="text-link" href="<?= e(app_url()) ?>">Browse all stories →</a></div>
        <?php else: ?>
            <div class="post-grid">
                <?php foreach ($posts as $post): ?>
                    <?= render_post_card($post) ?>
                <?php endforeach; ?>
            </div>
            <?php if ($totalPages > 1): ?>
                <nav class="pagination" aria-label="Category pages">
                    <?php if ($page > 1): ?><a class="pagination__link" href="<?= e(app_url('category/' . rawurlencode($category['slug']) . '?page=' . ($page - 1))) ?>">← <span>Newer stories</span></a><?php else: ?><span></span><?php endif; ?>
                    <span class="pagination__current">Page <?= $page ?> <span>of <?= $totalPages ?></span></span>
                    <?php if ($page < $totalPages): ?><a class="pagination__link pagination__link--next" href="<?= e(app_url('category/' . rawurlencode($category['slug']) . '?page=' . ($page + 1))) ?>"><span>Older stories</span> →</a><?php else: ?><span></span><?php endif; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </section>
<?php endif; ?>
<?php require __DIR__ . '/app/public-footer.php'; ?>
