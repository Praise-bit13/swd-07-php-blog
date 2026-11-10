<?php
declare(strict_types=1);

function require_admin(): void
{
    if (empty($_SESSION['admin_id'])) {
        redirect('admin/login.php');
    }
    $now = time();
    $lastActivity = (int) ($_SESSION['last_activity'] ?? 0);
    if ($lastActivity > 0 && $now - $lastActivity > 1800) {
        $_SESSION = [];
        session_regenerate_id(true);
        redirect('admin/login.php?expired=1');
    }
    $_SESSION['last_activity'] = $now;

    if ($now - (int) ($_SESSION['last_regeneration'] ?? 0) > 900) {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = $now;
    }
}

function current_admin(): array
{
    return [
        'id' => (int) ($_SESSION['admin_id'] ?? 0),
        'name' => (string) ($_SESSION['admin_name'] ?? ''),
        'email' => (string) ($_SESSION['admin_email'] ?? ''),
    ];
}
