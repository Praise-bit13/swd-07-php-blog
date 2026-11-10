<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$categories = db()->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$errors = [];
$post = $post ?? ['id' => 0, 'title' => '', 'excerpt' => '', 'body' => '', 'category_id' => '', 'status' => 'draft', 'cover_image' => null];
$isEdit = (int) $post['id'] > 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $post['title'] = posted_string('title');
    $post['excerpt'] = posted_string('excerpt');
    $post['body'] = trim((string) ($_POST['body'] ?? ''));
    $post['category_id'] = (int) ($_POST['category_id'] ?? 0);
    $post['status'] = posted_string('status');
    if ($post['title'] === '' || text_length($post['title']) > 240) $errors[] = 'Title is required and must be 240 characters or fewer.';
    if ($post['excerpt'] === '' || text_length($post['excerpt']) > 300) $errors[] = 'Excerpt is required and must be 300 characters or fewer.';
    if ($post['body'] === '') $errors[] = 'Body is required.';
    if (!in_array($post['status'], ['draft', 'published'], true)) $errors[] = 'Choose a valid status.';
    $categoryCheck = db()->prepare('SELECT id FROM categories WHERE id = :id');
    $categoryCheck->execute(['id' => $post['category_id']]);
    if (!$categoryCheck->fetch()) $errors[] = 'Choose a valid category.';
    if (!$categories) $errors[] = 'Create a category before adding a post.';
    $oldCover = is_string($post['cover_image']) ? $post['cover_image'] : null;
    $cover = $oldCover;
    if (!$errors) {
        try {
            $cover = upload_cover($_FILES['cover_image'] ?? [], $oldCover);
        } catch (RuntimeException $exception) {
            $errors[] = $exception->getMessage();
        }
    }
    if (!$errors) {
        $pdo = db();
        $publishNow = $post['status'] === 'published' ? 1 : 0;
        try {
            $slug = unique_slug($pdo, 'posts', $post['title'], $isEdit ? (int) $post['id'] : null);
            if ($isEdit) {
                $statement = $pdo->prepare(
                    'UPDATE posts SET title = :title, slug = :slug, excerpt = :excerpt, body = :body, category_id = :category_id,
                     status = :status, cover_image = :cover_image,
                     published_at = CASE WHEN :publish_now = 1 AND published_at IS NULL THEN NOW() ELSE published_at END
                     WHERE id = :id'
                );
                $statement->execute([
                    'title' => $post['title'],
                    'slug' => $slug,
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'category_id' => $post['category_id'],
                    'status' => $post['status'],
                    'cover_image' => $cover,
                    'publish_now' => $publishNow,
                    'id' => $post['id'],
                ]);
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO posts (title, slug, excerpt, body, category_id, author_id, status, cover_image, published_at)
                     VALUES (:title, :slug, :excerpt, :body, :category_id, :author_id, :status, :cover_image,
                     CASE WHEN :publish_now = 1 THEN NOW() ELSE NULL END)'
                );
                $statement->execute([
                    'title' => $post['title'],
                    'slug' => $slug,
                    'excerpt' => $post['excerpt'],
                    'body' => $post['body'],
                    'category_id' => $post['category_id'],
                    'author_id' => current_admin()['id'],
                    'status' => $post['status'],
                    'cover_image' => $cover,
                    'publish_now' => $publishNow,
                ]);
            }
        } catch (Throwable $exception) {
            if ($cover !== $oldCover) {
                try {
                    delete_cover_image($cover);
                } catch (RuntimeException $cleanupException) {
                    error_log($cleanupException->getMessage());
                }
            }
            throw $exception;
        }
        if ($cover !== $oldCover && $oldCover !== null) {
            try {
                delete_cover_image($oldCover);
            } catch (RuntimeException $exception) {
                error_log($exception->getMessage());
                redirect('admin/posts/index.php?saved=1&image_replacement_cleanup_failed=1');
            }
        }
        redirect('admin/posts/index.php?saved=1');
    }
}
$pageTitle = $isEdit ? 'Edit post' : 'New post';
require dirname(__DIR__) . '/header.php';
?>
<?php foreach ($errors as $error): ?><p class="notice"><?= e($error) ?></p><?php endforeach; ?>
<form class="panel" method="post" enctype="multipart/form-data"><?= csrf_field() ?><div class="form-field"><label for="title">Title</label><input id="title" name="title" maxlength="240" required value="<?= e($post['title']) ?>"></div><div class="form-field"><label for="category_id">Category</label><select id="category_id" name="category_id" required><option value="">Choose category</option><?php foreach ($categories as $category): ?><option value="<?= (int) $category['id'] ?>" <?= (int) $post['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select></div><div class="form-field"><label for="excerpt">Excerpt (up to 300 characters)</label><textarea id="excerpt" name="excerpt" maxlength="300" rows="3" required><?= e($post['excerpt']) ?></textarea></div><div class="form-field"><label for="body">Body</label><textarea id="body" name="body" required><?= e($post['body']) ?></textarea></div><div class="form-field"><label for="status">Status</label><select id="status" name="status"><option value="draft" <?= $post['status'] === 'draft' ? 'selected' : '' ?>>Draft</option><option value="published" <?= $post['status'] === 'published' ? 'selected' : '' ?>>Published</option></select></div><div class="form-field"><label for="cover_image">Cover image (JPG, PNG, WebP; up to 2 MB)</label><input id="cover_image" name="cover_image" type="file" accept="image/jpeg,image/png,image/webp"><?php if ($post['cover_image']): ?><p>Current image: <?= e($post['cover_image']) ?></p><?php endif; ?></div><button type="submit">Save post</button> <a class="button secondary" href="<?= e(app_url('admin/posts/index.php')) ?>">Cancel</a></form>
<?php require dirname(__DIR__) . '/footer.php'; ?>
