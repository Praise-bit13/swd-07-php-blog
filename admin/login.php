<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/app/bootstrap.php';
if (!empty($_SESSION['admin_id'])) {
    redirect('admin/index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $statement = db()->prepare('SELECT id, name, email, password_hash FROM admins WHERE email = :email LIMIT 1');
    $statement->execute(['email' => $email]);
    $admin = $statement->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        if (password_needs_rehash($admin['password_hash'], PASSWORD_DEFAULT)) {
            $update = db()->prepare('UPDATE admins SET password_hash = :hash WHERE id = :id');
            $update->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $admin['id']]);
        }
        session_regenerate_id(true);
        $_SESSION = [
            'admin_id' => (int) $admin['id'],
            'admin_name' => $admin['name'],
            'admin_email' => $admin['email'],
            'last_activity' => time(),
            'last_regeneration' => time(),
        ];
        redirect('admin/index.php');
    }
    $error = 'Invalid email or password.';
}
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Admin login | SWD Blog</title><link rel="stylesheet" href="<?= e(app_url('assets/style.css')) ?>"></head>
<body><main class="container" style="max-width:480px"><section class="panel"><h1>Admin login</h1><?php if (isset($_GET['expired'])): ?><p class="notice">Your session expired. Please sign in again.</p><?php endif; ?><?php if ($error !== ''): ?><p class="notice"><?= e($error) ?></p><?php endif; ?><form method="post"><?= csrf_field() ?><div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" autocomplete="username" required></div><div class="form-field"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div><button type="submit">Sign in</button></form><p><a href="<?= e(app_url()) ?>">← Back to blog</a></p></section></main></body></html>
