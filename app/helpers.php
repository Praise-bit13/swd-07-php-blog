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

function make_slug(string $value): string
{
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
    return trim($value, '-');
}

function unique_slug(PDO $pdo, string $table, string $value, ?int $exceptId = null): string
{
    if (!in_array($table, ['posts', 'categories'], true)) {
        throw new InvalidArgumentException('Unsupported slug table.');
    }
    $base = make_slug($value);
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
    if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
        throw new RuntimeException('Cover images must be no larger than 5 MB.');
    }
    $imageInfo = @getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new RuntimeException('Choose a valid image file.');
    }
    $mime = $imageInfo['mime'] ?? '';
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('Use a JPG, PNG, GIF, or WebP image.');
    }

    $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads';
    if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
        throw new RuntimeException('The uploads directory could not be created.');
    }
    $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . DIRECTORY_SEPARATOR . $filename)) {
        throw new RuntimeException('The cover image could not be saved.');
    }
    return $filename;
}
