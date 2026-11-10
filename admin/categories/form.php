<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$errors = [];
$category = $category ?? ['id' => 0, 'name' => ''];
$isEdit = (int) $category['id'] > 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $category['name'] = posted_string('name');
    if ($category['name'] === '' || strlen($category['name']) > 120) $errors[] = 'Name is required and must be 120 characters or fewer.';
    if (!$errors) {
        $slug = unique_slug(db(), 'categories', $category['name'], $isEdit ? (int) $category['id'] : null);
        if ($isEdit) {
            $statement = db()->prepare('UPDATE categories SET name = :name, slug = :slug WHERE id = :id');
            $statement->execute(['name' => $category['name'], 'slug' => $slug, 'id' => $category['id']]);
        } else {
            $statement = db()->prepare('INSERT INTO categories (name, slug) VALUES (:name, :slug)');
            $statement->execute(['name' => $category['name'], 'slug' => $slug]);
        }
        redirect('admin/categories/index.php?saved=1');
    }
}
$pageTitle = $isEdit ? 'Edit category' : 'New category';
require dirname(__DIR__) . '/header.php';
?>
<?php foreach ($errors as $error): ?><p class="notice"><?= e($error) ?></p><?php endforeach; ?><form class="panel" method="post"><?= csrf_field() ?><div class="form-field"><label for="name">Name</label><input id="name" name="name" maxlength="120" required value="<?= e($category['name']) ?>"></div><button type="submit">Save category</button> <a class="button secondary" href="<?= e(app_url('admin/categories/index.php')) ?>">Cancel</a></form>
<?php require dirname(__DIR__) . '/footer.php'; ?>
