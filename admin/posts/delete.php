<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}
verify_csrf();
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    http_response_code(400);
    exit('Invalid post.');
}
$statement = db()->prepare('SELECT cover_image FROM posts WHERE id = :id');
$statement->execute(['id' => $id]);
$coverImage = $statement->fetchColumn();
$statement = db()->prepare('DELETE FROM posts WHERE id = :id');
$statement->execute(['id' => $id]);
if ($statement->rowCount() === 0) {
    http_response_code(404);
    exit('Post not found.');
}
try {
    delete_cover_image(is_string($coverImage) ? $coverImage : null);
} catch (RuntimeException $exception) {
    error_log($exception->getMessage());
    redirect('admin/posts/index.php?deleted=1&image_cleanup_failed=1');
}
redirect('admin/posts/index.php?deleted=1');
