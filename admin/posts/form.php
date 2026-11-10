<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$post = $post ?? ['id' => 0, 'title' => '', 'body' => '', 'category_id' => '', 'status' => 'draft', 'cover_image' => null];
$isEdit = (int) $post['id'] > 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $post['title'] = posted_string('title');
    $post['body'] = trim((string) ($_POST['body'] ?? ''));
    $post['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $post['status'] = posted_string('status');
    if ($post['title'] === '' || strlen($post['title']) > 240) $errors[] = 'Title is required and must be 240 characters or fewer.';
    if ($post['body'] === '') $errors[] = 'Body is required.';
    if (!in_array($post['status'], ['draft', 'published'], true)) $errors[] = 'Choose a valid status.';
    $categoryCheck = db()->prepare('SELECT id FROM categories WHERE id = :id');
    $categoryCheck->execute(['id' => $post['category_id']]);
    if (!$categoryCheck->fetch()) $errors[] = 'Choose a valid category.';
    if (!$categories) $errors[] = 'Create a category before adding a post.';
    $cover = $post['cover_image'];
    if (!$errors) {
        try {
            $cover = upload_cover($_FILES['cover_image'] ?? [], $cover ?: null);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    if (!$errors) {
        $pdo = db();
        $slug = unique_slug($pdo, 'posts', $post['title'], $isEdit ? (int) $post['id'] : null);
        $publishedAt = $post['status'] === 'published' ? date('Y-m-d H:i:s') : null;
        if ($isEdit) {
            $statement = $pdo->prepare(
                'UPDATE posts SET title = :title, slug = :slug, excerpt = :excerpt, body = :body, category_id = :category_id,
                 status = :status, cover_image = :cover_image, published_at = COALESCE(published_at, :published_at)
                 WHERE id = :id'
            );
            $statement->execute(['title' => $post['title'], 'slug' => $slug, 'excerpt' => excerpt($post['body'], 297), 'body' => $post['body'], 'category_id' => $post['category_id'], 'status' => $post['status'], 'cover_image' => $cover, 'published_at' => $publishedAt, 'id' => $post['id']]);
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO posts (title, slug, excerpt, body, category_id, author_id, status, cover_image, published_at)
                 VALUES (:title, :slug, :excerpt, :body, :category_id, :author_id, :status, :cover_image, :published_at)'
            );
            $statement->execute(['title' => $post['title'], 'slug' => $slug, 'excerpt' => excerpt($post['body'], 297), 'body' => $post['body'], 'category_id' => $post['category_id'], 'author_id' => current_admin()['id'], 'status' => $post['status'], 'cover_image' => $cover, 'published_at' => $publishedAt]);
        }
        redirect('admin/posts/index.php?saved=1');
    }
}
$pageTitle = $isEdit ? 'Edit post' : 'New post';
require dirname(__DIR__) . '/header.php';
?>
<?php foreach ($errors as $error): ?><p class="notice"><?= e($error) ?></p><?php endforeach; ?>
<form class="panel" method="post" enctype="multipart/form-data"><?= csrf_field() ?><div class="form-field"><label for="title">Title</label><input id="title" name="title" maxlength="240" required value="<?= e($post['title']) ?>"></div><div class="form-field"><label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">Choose category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $post['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="body">Body</label><textarea id="body" name="body" required><?= e($post['body']) ?></textarea></div><div class="form-field"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div><div class="form-field"><label for="cover_image">Cover image (JPG, PNG, GIF, WebP; up to 5 MB)</label><input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/gif,image/webp"><?php if ($post['cover_image']): ?><p>Current image: <?= e($post['cover_image']) ?></p><?php endif; ?></div><button type="submit">Save post</button> <a class="button secondary" href="<?= e(app_url('admin/posts/index.php')) ?>">Cancel</a></form>
<?php require dirname(__DIR__) . '/footer.php'; ?>
