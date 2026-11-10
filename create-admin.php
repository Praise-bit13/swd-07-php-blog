<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}
require_once __DIR__ . '/app/db.php';

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php create-admin.php \"Your Name\" you@example.com\n");
    exit(1);
}
$name = trim($argv[1]);
$email = filter_var(trim($argv[2]), FILTER_VALIDATE_EMAIL);
if ($name === '' || strlen($name) > 120 || $email === false) {
    fwrite(STDERR, "Provide a name (up to 120 characters) and a valid email address.\n");
    exit(1);
}
fwrite(STDOUT, "Password (input is visible in this terminal): ");
$password = trim((string) fgets(STDIN));
fwrite(STDOUT, "Confirm password: ");
$confirmation = trim((string) fgets(STDIN));
if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Passwords must match and be at least 12 characters long.\n");
    exit(1);
}

try {
    $pdo = db();
    $lockAcquired = (int) $pdo->query("SELECT GET_LOCK('swd_blog_create_first_admin', 10)")->fetchColumn() === 1;
    if (!$lockAcquired) {
        throw new RuntimeException('Could not acquire the admin setup lock. Try again.');
    }
    $exists = (int) $pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
    if ($exists > 0) {
        throw new RuntimeException('An admin already exists; the bootstrap command only creates the first admin.');
    }
    $statement = $pdo->prepare(
        'INSERT INTO admins (name, email, password_hash) VALUES (:name, :email, :password_hash)'
    );
    $statement->execute([
        'name' => $name,
        'email' => $email,
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
    ]);
    $pdo->query("SELECT RELEASE_LOCK('swd_blog_create_first_admin')");
    $lockAcquired = false;
    fwrite(STDOUT, "Admin created successfully.\n");
} catch (Throwable $exception) {
    if (($lockAcquired ?? false) && isset($pdo) && $pdo instanceof PDO) {
        $pdo->query("SELECT RELEASE_LOCK('swd_blog_create_first_admin')");
    }
    fwrite(STDERR, "Could not create admin: " . $exception->getMessage() . "\n");
    exit(1);
}
