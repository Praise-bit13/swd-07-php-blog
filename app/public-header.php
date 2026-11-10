<?php
declare(strict_types=1);
$pageTitle = $pageTitle ?? 'SWD Blog';
$pageDescription = $pageDescription ?? 'Thoughtful stories, practical tutorials, and fresh ideas.';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDescription) ?>">
    <meta name="theme-color" content="#172d36">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= e(app_url('assets/site.css')) ?>">
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="<?= e(app_url()) ?>" aria-label="SWD Blog home">
            <span class="brand__mark">S</span><span>SWD<span class="brand__light">Journal</span></span>
        </a>
        <nav class="site-nav" aria-label="Main navigation">
            <a href="<?= e(app_url()) ?>">Stories</a>
            <a href="<?= e(app_url('search.php')) ?>">Search</a>
            <a class="site-nav__admin" href="<?= e(app_url('admin/login.php')) ?>">Admin <span aria-hidden="true">↗</span></a>
        </nav>
    </div>
</header>
<main>
