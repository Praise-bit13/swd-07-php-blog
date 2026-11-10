<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function app_url(string $path = ''): string
{
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $adminPosition = strpos($script, '/admin/');
    $base = $adminPosition !== false
        ? substr($script, 0, $adminPosition)
        : rtrim(str_replace('\\', '/', dirname($script)), '/');
    $base = $base === '/' ? '' : rtrim($base, '/');

    return $base . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path), true, 303);
    exit;
}

function excerpt(string $text, int $length = 180): string
{
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)) ?? '');
    if (function_exists('mb_strlen') && mb_strlen($text, 'UTF-8') > $length) {
        return mb_substr($text, 0, $length, 'UTF-8') . '…';
    }
    if (preg_match('/^.{0,' . $length . '}/us', $text, $matches) === 1
        && strlen($matches[0]) < strlen($text)) {
        return $matches[0] . '…';
    }
    return $text;
}

function text_length(string $value): int
{
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }

    $length = preg_match_all('/./us', $value);
    return $length === false ? PHP_INT_MAX : $length;
}

function slugify(string $title): string
{
    $title = strtolower(trim($title));
    $title = preg_replace('/[^a-z0-9]+/', '-', $title) ?? '';
    return trim($title, '-');
}

function unique_slug(PDO $pdo, string $table, string $value, ?int $exceptId = null): string
{
    if (!in_array($table, ['posts', 'categories'], true)) {
        throw new InvalidArgumentException('Unsupported slug table.');
    }
    $base = slugify($value);
    if ($base === '') {
        $base = 'entry';
    }
    $slug = $base;
    $suffix = 2;
    while (true) {
        $sql = "SELECT id FROM {$table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        if ($exceptId !== null) {
            $sql .= ' AND id <> :id';
            $params['id'] = $exceptId;
        }
        $statement = $pdo->prepare($sql . ' LIMIT 1');
        $statement->execute($params);
        if (!$statement->fetch()) {
            return $slug;
        }
        $slug = $base . '-' . $suffix++;
    }
}

function posted_string(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function upload_cover(array $file, ?string $current = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return $current;
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('The cover image could not be uploaded.');
    }
    $temporaryFile = $file['tmp_name'] ?? '';
    if (!is_string($temporaryFile) || !is_uploaded_file($temporaryFile)) {
        throw new RuntimeException('Choose a valid uploaded image.');
    }
    $size = filesize($temporaryFile);
    if ($size === false || $size > 2 * 1024 * 1024) {
        throw new RuntimeException('Cover images must be no larger than 2 MB.');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($temporaryFile);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!is_string($mime) || !isset($extensions[$mime])) {
        throw new RuntimeException('Use a JPG, PNG, or WebP image.');
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The uploads directory could not be created.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($temporaryFile, $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The cover image could not be saved.');
    }
    return $filename;
}

function delete_cover_image(?string $filename): void
{
    if ($filename === null || $filename === '') {
        return;
    }
    if (preg_match('/\A[a-f0-9]{32}\.(?:jpg|png|gif|webp)\z/', $filename) !== 1) {
        throw new RuntimeException('The stored cover image filename is invalid.');
    }

    $path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename;
    if (is_file($path) && !unlink($path)) {
        throw new RuntimeException('The old cover image could not be deleted.');
    }
}
