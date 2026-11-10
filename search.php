<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$query = trim((string) ($_GET['q'] ?? ''));
$posts = [];
$usedLikeFallback = $query !== '' && text_length($query) < 3;

if ($query !== '') {
    if ($usedLikeFallback) {
        $like = '%' . strtr($query, ['!' => '!!', '%' => '!%', '_' => '!_']) . '%';
        $statement = db()->prepare(
            "SELECT p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
                    c.name AS category_name, c.slug AS category_slug, a.name AS author_name
             FROM posts p
             JOIN categories c ON c.id = p.category_id
             JOIN admins a ON a.id = p.author_id
             WHERE p.status = 'published'
               AND (p.title LIKE :title_query ESCAPE '!' OR p.body LIKE :body_query ESCAPE '!')
             ORDER BY p.published_at DESC, p.id DESC
             LIMIT 50"
        );
        $statement->execute(['title_query' => $like, 'body_query' => $like]);
    } else {
        $statement = db()->prepare(
            "SELECT p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
                    c.name AS category_name, c.slug AS category_slug, a.name AS author_name,
                    MATCH(p.title, p.body) AGAINST (:search_score IN NATURAL LANGUAGE MODE) AS relevance
             FROM posts p
             JOIN categories c ON c.id = p.category_id
             JOIN admins a ON a.id = p.author_id
             WHERE p.status = 'published'
               AND MATCH(p.title, p.body) AGAINST (:search_filter IN NATURAL LANGUAGE MODE)
             ORDER BY relevance DESC, p.published_at DESC, p.id DESC
             LIMIT 50"
        );
        $statement->execute(['search_score' => $query, 'search_filter' => $query]);
    }
    $posts = $statement->fetchAll();
}

$pageTitle = $query === '' ? 'Search stories — SWD Journal' : 'Search: ' . $query . ' — SWD Journal';
$pageDescription = 'Find a story, guide, or idea in the SWD Journal.';
require __DIR__ . '/app/public-header.php';
?>
<section class="search-page">
    <div class="search-page__intro"><p class="eyebrow">FIND YOUR NEXT READ</p><h1>Search the journal.</h1><p>Follow a question. Find a new perspective.</p></div>
    <form class="search-form search-form--page" action="<?= e(app_url('search.php')) ?>" method="get" role="search">
        <label class="sr-only" for="site-search">Search stories</label>
        <span class="search-form__icon" aria-hidden="true">⌕</span>
        <input id="site-search" type="search" name="q" value="<?= e($query) ?>" placeholder="Try a topic or keyword" autofocus>
        <button type="submit">Search <span aria-hidden="true">→</span></button>
    </form>

    <?php if ($query !== ''): ?>
        <div class="search-results-heading"><h2>Results for “<?= e($query) ?>”</h2><?php if ($usedLikeFallback): ?><span class="search-hint">Showing matches for a short query</span><?php endif; ?></div>
        <?php if (!$posts): ?>
            <div class="empty-state empty-state--compact"><span class="empty-state__icon" aria-hidden="true">⌕</span><h2>No matches this time.</h2><p>Try a different word or a broader topic.</p></div>
        <?php else: ?>
            <div class="post-grid">
                <?php foreach ($posts as $post): ?>
                    <?= render_post_card($post) ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/app/public-footer.php'; ?>
