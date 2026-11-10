<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
require_admin();
$admin = current_admin();
$pageTitle = $pageTitle ?? 'Admin';
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($pageTitle) ?> | SWD Blog Admin</title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head><body>
<div class="admin-shell"><aside class="sidebar"><h2>SWD Blog</h2><nav><a href="<?= e(app_url('admin/index.php')) ?>">Dashboard</a><a href="<?= e(app_url('admin/posts/index.php')) ?>">Posts</a><a href="<?= e(app_url('admin/categories/index.php')) ?>">Categories</a><a href="<?= e(app_url()) ?>">View site</a><form method="post" action="<?= e(app_url('admin/logout.php')) ?>"><?= csrf_field() ?><button type="submit">Logout</button></form></nav></aside><main class="admin-main"><div class="admin-top"><h1><?= e($pageTitle) ?></h1><span><?= e($admin['name']) ?></span></div>
