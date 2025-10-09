<?php
function view(string $template, array $data = []): void
{
    if (!isset($data['currentRoute']) && isset($GLOBALS['currentRoute'])) {
        $data['currentRoute'] = $GLOBALS['currentRoute'];
    }

    global $pdo;
    if (isset($pdo)) {
        if (!isset($data['navPages']) && function_exists('get_navigation_pages')) {
            $data['navPages'] = get_navigation_pages($pdo);
        }
        if (!isset($data['navCareArticles']) && function_exists('get_published_care_articles')) {
            $data['navCareArticles'] = get_published_care_articles($pdo);
        }
    }

    extract($data);
    include __DIR__ . '/../public/views/' . $template . '.php';
}

function asset(string $path): string
{
    return BASE_URL . '/assets/' . ltrim($path, '/');
}

function redirect(string $route, array $params = []): void
{
    $query = http_build_query(array_merge(['route' => $route], $params));
    header('Location: ' . BASE_URL . '/index.php?' . $query);
    exit;
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_login(): void
{
    if (!current_user()) {
        redirect('login');
    }
}

function is_authorized(string $capability): bool
{
    $user = current_user();
    if (!$user) {
        return false;
    }
    if ($user['role'] === 'admin') {
        return true;
    }

    return !empty($user[$capability]);
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message === null) {
        if (isset($_SESSION['flash'][$key])) {
            $value = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            return $value;
        }
        return null;
    }

    $_SESSION['flash'][$key] = $message;
    return null;
}

function csrf_token(): string
{
    $now = time();
    if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
        $_SESSION['csrf_tokens'] = [];
    }

    foreach ($_SESSION['csrf_tokens'] as $storedToken => $expiry) {
        if (!is_int($expiry) || $expiry < $now) {
            unset($_SESSION['csrf_tokens'][$storedToken]);
        }
    }

    if (count($_SESSION['csrf_tokens']) > 50) {
        $_SESSION['csrf_tokens'] = array_slice($_SESSION['csrf_tokens'], -50, null, true);
    }

    $token = bin2hex(random_bytes(32));
    $_SESSION['csrf_tokens'][$token] = $now + 1800;

    return $token;
}

function verify_csrf_token(?string $token): bool
{
    if (!$token || !isset($_SESSION['csrf_tokens'][$token])) {
        return false;
    }

    $expiry = $_SESSION['csrf_tokens'][$token];
    unset($_SESSION['csrf_tokens'][$token]);

    return is_int($expiry) && $expiry >= time();
}

function require_csrf_token(string $route, array $params = []): void
{
    $token = $_POST['_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null);
    if (verify_csrf_token($token)) {
        return;
    }

    flash('error', 'Sicherheitsüberprüfung fehlgeschlagen. Bitte Formular erneut absenden.');
    redirect($route, $params);
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '">';
}

function ensure_directory(string $dir): void
{
    if (is_dir($dir)) {
        return;
    }

    if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException(sprintf('Verzeichnis "%s" konnte nicht erstellt werden.', $dir));
    }
}

function handle_upload(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
        return null;
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return null;
    }

    ensure_directory(UPLOAD_PATH);

    $finfo = function_exists('finfo_open') ? finfo_open(FILEINFO_MIME_TYPE) : false;
    $mimeType = $finfo ? finfo_file($finfo, $file['tmp_name']) : null;
    if ($finfo) {
        finfo_close($finfo);
    }
    if ($mimeType && strpos($mimeType, 'image/') !== 0) {
        return null;
    }

    $originalName = $file['name'] ?? 'upload';
    $sanitizedName = preg_replace('/[^a-zA-Z0-9\.\-]/', '_', $originalName);
    $filename = bin2hex(random_bytes(8)) . '-' . $sanitizedName;
    $destination = UPLOAD_PATH . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return 'uploads/' . $filename;
}

function normalize_nullable_id($value): ?int
{
    if ($value === null) {
        return null;
    }

    if (is_string($value)) {
        $value = trim($value);
        if ($value === '' || $value === '0') {
            return null;
        }
    }

    if (!is_numeric($value)) {
        return null;
    }

    $intValue = (int)$value;
    return $intValue > 0 ? $intValue : null;
}

function normalize_flag($value): int
{
    $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    return $normalized ? 1 : 0;
}

function get_setting(PDO $pdo, string $key, string $default = ''): string
{
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = :key');
    $stmt->execute(['key' => $key]);
    $row = $stmt->fetch();
    return $row['value'] ?? $default;
}

function set_setting(PDO $pdo, string $key, string $value): void
{
    $stmt = $pdo->prepare('REPLACE INTO settings(key, value) VALUES (:key, :value)');
    $stmt->execute(['key' => $key, 'value' => $value]);
}

function slugify(string $value): string
{
    $transliterated = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    if ($transliterated !== false) {
        $value = $transliterated;
    }
    $value = strtolower(trim($value));
    $value = preg_replace('/[^a-z0-9]+/i', '-', $value);
    $value = trim($value, '-');
    return $value ?: bin2hex(random_bytes(4));
}

function ensure_unique_slug(PDO $pdo, string $table, string $slug, ?int $ignoreId = null): string
{
    $base = $slug ?: bin2hex(random_bytes(4));
    $candidate = $base;
    $counter = 1;

    while (true) {
        $sql = "SELECT COUNT(*) FROM {$table} WHERE slug = :slug";
        $params = ['slug' => $candidate];
        if ($ignoreId !== null) {
            $sql .= ' AND id != :id';
            $params['id'] = $ignoreId;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        if ($stmt->fetchColumn() == 0) {
            return $candidate;
        }
        $candidate = $base . '-' . (++$counter);
    }
}

function render_rich_text(?string $value): string
{
    if ($value === null || $value === '') {
        return '';
    }

    if (strpos($value, '<') === false) {
        return nl2br(htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    return $value;
}
