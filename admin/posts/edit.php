<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT id, title, excerpt, body, category_id, status, cover_image FROM posts WHERE id = :id');
$statement->execute(['id' => $id ?: 0]);
$post = $statement->fetch();
if (!$post) {
    http_response_code(404);
    exit('Post not found.');
}
require __DIR__ . '/form.php';
