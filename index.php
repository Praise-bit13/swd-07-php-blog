<?php
declare(strict_types=1);
require_once __DIR__ . '/app/bootstrap.php';

$perPage = 6;
$page = max(1, (int) ($_GET['page'] ?? 1));
$total = (int) db()->query("SELECT COUNT(*) FROM posts WHERE status = 'published'")->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));
$page = min($page, $totalPages);

$statement = db()->prepare(
    "SELECT p.id, p.title, p.slug, p.excerpt, p.cover_image, p.published_at,
            c.name AS category_name, c.slug AS category_slug, a.name AS author_name
     FROM posts p
     JOIN categories c ON c.id = p.category_id
     JOIN admins a ON a.id = p.author_id
     WHERE p.status = 'published'
     ORDER BY p.published_at DESC, p.id DESC
     LIMIT :limit OFFSET :offset"
);
$statement->bindValue('limit', $perPage, PDO::PARAM_INT);
$statement->bindValue('offset', ($page - 1) * $perPage, PDO::PARAM_INT);
$statement->execute();
$posts = $statement->fetchAll();

$pageTitle = 'SWD Journal — stories, ideas & inspiration';
$pageDescription = 'Fresh perspectives, practical guides, and thoughtful stories from the SWD community.';
require __DIR__ . '/app/public-header.php';
?>
<section class="home-hero">
    <div class="home-hero__inner">
        <p class="eyebrow">THE SWD JOURNAL</p>
        <h1>Ideas that move<br><span>you forward.</span></h1>
        <p class="home-hero__intro">Fresh perspectives, practical guides, and thoughtful stories for curious minds.</p>
        <form class="search-form search-form--hero" action="<?= e(app_url('search.php')) ?>" method="get" role="search">
            <label class="sr-only" for="home-search">Search stories</label>
            <span class="search-form__icon" aria-hidden="true">⌕</span>
            <input id="home-search" type="search" name="q" placeholder="What are you curious about?">
            <button type="submit">Explore stories <span aria-hidden="true">→</span></button>
        </form>
        <div class="home-hero__note"><span class="hero-dot"></span> A little inspiration for your next big idea</div>
    </div>
    <div class="home-hero__art" aria-hidden="true"><div class="hero-orbit hero-orbit--one"></div><div class="hero-orbit hero-orbit--two"></div><div class="hero-art__spark">✳</div><div class="hero-art__label">READ<br>WIDELY</div></div>
</section>

<section class="content-section">
    <div class="section-heading">
        <div><p class="eyebrow">THE LATEST</p><h2>Stories to explore</h2></div>
        <span class="section-heading__count"><?= number_format($total) ?> <?= $total === 1 ? 'story' : 'stories' ?></span>
    </div>
    <?php if (!$posts): ?>
        <div class="empty-state"><span class="empty-state__icon" aria-hidden="true">✳</span><h2>The next story starts here.</h2><p>There are no published posts yet. Check back soon for something worth reading.</p></div>
    <?php else: ?>
        <div class="post-grid">
            <?php foreach ($posts as $post): ?>
                <?= render_post_card($post) ?>
            <?php endforeach; ?>
        </div>
        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Story pages">
                <?php if ($page > 1): ?><a class="pagination__link" href="<?= e(app_url('?page=' . ($page - 1))) ?>">← <span>Newer stories</span></a><?php else: ?><span></span><?php endif; ?>
                <span class="pagination__current">Page <?= $page ?> <span>of <?= $totalPages ?></span></span>
                <?php if ($page < $totalPages): ?><a class="pagination__link pagination__link--next" href="<?= e(app_url('?page=' . ($page + 1))) ?>"><span>Older stories</span> →</a><?php else: ?><span></span><?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/app/public-footer.php'; ?>
