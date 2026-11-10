<?php
declare(strict_types=1);
require_once dirname(__DIR__, 2) . '/app/bootstrap.php';
require_admin();
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$statement = db()->prepare('SELECT id, name FROM categories WHERE id = :id');
$statement->execute(['id' => $id ?: 0]);
$category = $statement->fetch();
if (!$category) {
    http_response_code(404);
    exit('Category not found.');
}
require __DIR__ . '/form.php';
