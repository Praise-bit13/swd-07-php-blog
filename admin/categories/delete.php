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
    exit('Invalid category.');
}
try {
    $statement = db()->prepare('DELETE FROM categories WHERE id = :id');
    $statement->execute(['id' => $id]);
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        redirect('admin/categories/index.php?error=in-use');
    }
    throw $exception;
}
redirect('admin/categories/index.php?deleted=1');
