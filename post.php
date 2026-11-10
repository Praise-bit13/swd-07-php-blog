<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$slug = trim((string) ($_GET['slug'] ?? ''));
$statement = db()->prepare(
    "SELECT p.id, p.category_id, p.title, p.slug, p.excerpt, p.body, p.cover_image, p.published_at,
            c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.slug = :slug AND p.status = 'published'
     LIMIT 1"
);
$statement->execute(['slug' => $slug]);
$post = $statement->fetch();
$relatedPosts = [];

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Story not found — SWD Journal';
    $pageDescription = 'This story could not be found.';
} else {
    $related = db()->prepare(
        "SELECT p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
                c.name AS category_name, c.slug AS category_slug, a.name AS author_name
         FROM posts p
         JOIN categories c ON c.id = p.category_id
         JOIN admins a ON a.id = p.author_id
         WHERE p.status = 'published' AND p.category_id = :category_id AND p.id <> :post_id
         ORDER BY p.published_at DESC, p.id DESC
         LIMIT 3"
    );
    $related->execute(['category_id' => $post['category_id'], 'post_id' => $post['id']]);
    $relatedPosts = $related->fetchAll();
    $pageTitle = $post['title'] . ' — SWD Journal';
    $pageDescription = $post['excerpt'];
}
require __DIR__ . '/app/public-header.php';
?>
<?php if (!$post): ?>
    <section class="not-found">
        <p class="eyebrow">404 · STORY NOT FOUND</p>
        <h1>This page has wandered off.</h1>
        <p>The story may have moved, or it may not be published yet.</p>
        <a class="button-link" href="<?= e(app_url()) ?>">Back to all stories <span aria-hidden="true">→</span></a>
    </section>
<?php else: ?>
    <div class="article-shell">
        <nav class="breadcrumbs" aria-label="Breadcrumb"><a href="<?= e(app_url()) ?>">Stories</a><span aria-hidden="true">/</span><a href="<?= e(app_url('category/' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a></nav>
        <article class="article">
            <header class="article__header">
                <a class="eyebrow article__category" href="<?= e(app_url('category/' . rawurlencode($post['category_slug']))) ?>"><?= e($post['category_name']) ?></a>
                <h1><?= e($post['title']) ?></h1>
                <?php if ($post['excerpt'] !== ''): ?><p class="article__dek"><?= e($post['excerpt']) ?></p><?php endif; ?>
                <div class="article__byline">
                    <span class="author-avatar" aria-hidden="true"><?= e(strtoupper(substr($post['author_name'], 0, 1))) ?></span>
                    <span class="article__author">Written by <strong><?= e($post['author_name']) ?></strong></span>
                    <span class="byline-separator" aria-hidden="true">·</span>
                    <time datetime="<?= e(date('c', strtotime((string) $post['published_at']))) ?>"><?= e(date('F j, Y', strtotime((string) $post['published_at']))) ?></time>
                    <span class="byline-separator" aria-hidden="true">·</span>
                    <span><?= reading_time($post['body']) ?> min read</span>
                </div>
            </header>
            <?php if ($post['cover_image']): ?>
                <figure class="article__cover"><img src="<?= e(app_url('uploads/' . rawurlencode($post['cover_image']))) ?>" alt="Cover image for <?= e($post['title']) ?>"></figure>
            <?php endif; ?>
            <div class="article__body"><?= render_paragraphs($post['body']) ?></div>
            <div class="article__endmark" aria-hidden="true">✳</div>
        </article>
        <div class="article-back"><a href="<?= e(app_url()) ?>">← Back to all stories</a></div>
    </div>

    <?php if ($relatedPosts): ?>
        <section class="related-section">
            <div class="section-heading"><div><p class="eyebrow">KEEP EXPLORING</p><h2>More in <?= e($post['category_name']) ?></h2></div><a class="text-link" href="<?= e(app_url('category/' . rawurlencode($post['category_slug']))) ?>">View category →</a></div>
            <div class="post-grid">
                <?php foreach ($relatedPosts as $relatedPost): ?>
                    <?= render_post_card($relatedPost) ?>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>
<?php require __DIR__ . '/app/public-footer.php'; ?>
