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

    $info = @getimagesize($file['tmp_name']);
    if (!$info || empty($info['mime']) || !media_mime_is_allowed($info['mime'])) {
        return null;
    }

    $extension = media_extension_from_mime($info['mime']);
    $timestamp = new DateTimeImmutable('now');
    $subDirectory = $timestamp->format('Y') . '/' . $timestamp->format('m');
    $targetDirectory = rtrim(UPLOAD_PATH, '/') . '/' . $subDirectory;
    ensure_directory($targetDirectory);

    $baseName = media_safe_filename(pathinfo($file['name'] ?? 'upload', PATHINFO_FILENAME));
    $fileName = $baseName . '-' . bin2hex(random_bytes(4)) . '.' . $extension;
    $destination = $targetDirectory . '/' . $fileName;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        return null;
    }

    return 'uploads/' . $subDirectory . '/' . $fileName;
}

function media_mime_is_allowed(string $mime): bool
{
    return in_array(strtolower($mime), MEDIA_ALLOWED_MIME_TYPES, true);
}

function media_extension_from_mime(string $mime): string
{
    return match (strtolower($mime)) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => 'bin',
    };
}

function media_safe_filename(string $name): string
{
    $normalized = trim($name);
    if (function_exists('iconv')) {
        $transliterated = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $normalized);
        if ($transliterated !== false) {
            $normalized = $transliterated;
        }
    }
    $normalized = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $normalized));
    $normalized = trim($normalized, '-');
    return $normalized !== '' ? $normalized : 'media';
}

function media_prepare_storage(string $originalName, string $extension): array
{
    $timestamp = new DateTimeImmutable('now');
    $subDirectory = $timestamp->format('Y') . '/' . $timestamp->format('m');
    $targetDirectory = rtrim(UPLOAD_PATH, '/') . '/' . $subDirectory;
    ensure_directory($targetDirectory);

    $base = media_safe_filename(pathinfo($originalName, PATHINFO_FILENAME));
    $token = bin2hex(random_bytes(6));
    $basename = $base . '-' . $token;

    $build = static function (string $suffix, string $ext) use ($targetDirectory, $subDirectory, $basename): array {
        $filename = $suffix !== '' ? $basename . $suffix . '.' . $ext : $basename . '.' . $ext;
        return [
            'filename' => $filename,
            'absolute' => $targetDirectory . '/' . $filename,
            'relative' => 'uploads/' . $subDirectory . '/' . $filename,
        ];
    };

    return [
        'directory' => $targetDirectory,
        'subdir' => $subDirectory,
        'basename' => $basename,
        'original' => $build('', $extension),
        'thumb' => $build('-thumb', $extension),
        'medium' => $build('-medium', $extension),
        'webp' => $build('', 'webp'),
    ];
}

function media_webp_enabled(): bool
{
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $value = getenv('MEDIA_ENABLE_WEBP');
    if ($value === false) {
        $cached = true;
        return $cached;
    }
    $normalized = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    $cached = $normalized !== null ? $normalized : true;
    return $cached;
}

function media_create_image_resource(string $path, string $mime)
{
    return match (strtolower($mime)) {
        'image/jpeg' => imagecreatefromjpeg($path),
        'image/png' => imagecreatefrompng($path),
        'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : false,
        default => false,
    };
}

function media_preserve_alpha($image, string $mime): void
{
    if (in_array(strtolower($mime), ['image/png', 'image/webp'], true)) {
        imagealphablending($image, false);
        imagesavealpha($image, true);
    }
}

function media_resize_image(string $sourcePath, string $destinationPath, string $mime, int $maxWidth): ?array
{
    $info = @getimagesize($sourcePath);
    if (!$info) {
        return null;
    }
    [$width, $height] = $info;
    if ($width <= 0 || $height <= 0) {
        return null;
    }
    if ($width <= $maxWidth) {
        if (!copy($sourcePath, $destinationPath)) {
            return null;
        }
        return ['width' => $width, 'height' => $height];
    }

    $scale = $maxWidth / $width;
    $targetWidth = max(1, (int)round($width * $scale));
    $targetHeight = max(1, (int)round($height * $scale));

    $source = media_create_image_resource($sourcePath, $info['mime']);
    if (!$source) {
        return null;
    }

    $target = imagecreatetruecolor($targetWidth, $targetHeight);
    media_preserve_alpha($target, $info['mime']);
    if (!imagecopyresampled($target, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height)) {
        imagedestroy($source);
        imagedestroy($target);
        return null;
    }

    $saved = false;
    switch (strtolower($info['mime'])) {
        case 'image/jpeg':
            $saved = imagejpeg($target, $destinationPath, 85);
            break;
        case 'image/png':
            $saved = imagepng($target, $destinationPath, 6);
            break;
        case 'image/webp':
            if (function_exists('imagewebp')) {
                $saved = imagewebp($target, $destinationPath, 80);
            }
            break;
    }

    imagedestroy($source);
    imagedestroy($target);

    if (!$saved) {
        return null;
    }

    return ['width' => $targetWidth, 'height' => $targetHeight];
}

function media_convert_to_webp(string $sourcePath, string $destinationPath, string $sourceMime): bool
{
    if (!media_webp_enabled() || !function_exists('imagewebp')) {
        return false;
    }
    $source = media_create_image_resource($sourcePath, $sourceMime);
    if (!$source) {
        return false;
    }
    $result = imagewebp($source, $destinationPath, 80);
    imagedestroy($source);
    return (bool)$result;
}

function enforce_rate_limit(string $key, int $limit, int $intervalSeconds): void
{
    $now = time();
    if (!isset($_SESSION['rate_limits']) || !is_array($_SESSION['rate_limits'])) {
        $_SESSION['rate_limits'] = [];
    }
    if (!isset($_SESSION['rate_limits'][$key]) || !is_array($_SESSION['rate_limits'][$key])) {
        $_SESSION['rate_limits'][$key] = [];
    }
    $_SESSION['rate_limits'][$key] = array_values(array_filter($_SESSION['rate_limits'][$key], static function ($timestamp) use ($now, $intervalSeconds) {
        return is_int($timestamp) && $timestamp >= ($now - $intervalSeconds);
    }));

    if (count($_SESSION['rate_limits'][$key]) >= $limit) {
        throw new RuntimeException('Rate limit exceeded');
    }

    $_SESSION['rate_limits'][$key][] = $now;
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

function normalize_sex($value): ?string
{
    if ($value === null) {
        return null;
    }

    if (is_string($value)) {
        $normalized = strtolower(trim($value));
    } elseif (is_int($value)) {
        $normalized = (string)$value;
    } else {
        return null;
    }

    $map = [
        'm' => 'male',
        'male' => 'male',
        '1' => 'male',
        'f' => 'female',
        'w' => 'female',
        'female' => 'female',
        '0' => 'female',
        'unknown' => 'unknown',
        'u' => 'unknown',
        'x' => 'unknown',
    ];

    return $map[$normalized] ?? null;
}

function normalize_animal_status($value): ?string
{
    if ($value === null) {
        return null;
    }

    $normalized = strtolower(trim((string)$value));
    $allowed = ['available', 'reserved', 'holdback', 'sold', 'not-for-sale'];
    return in_array($normalized, $allowed, true) ? $normalized : null;
}

function normalize_species_slug(?string $value): ?string
{
    if ($value === null) {
        return null;
    }
    $value = trim($value);
    if ($value === '') {
        return null;
    }
    return slugify($value);
}

function normalize_price_to_cents($value): ?int
{
    if ($value === null) {
        return null;
    }

    $stringValue = trim((string)$value);
    if ($stringValue === '') {
        return null;
    }

    $normalized = str_replace(['€', 'eur', 'EUR'], '', $stringValue);
    if (preg_match('/-?\d+[\d\.,]*/', $normalized, $matches)) {
        $number = $matches[0];
    } else {
        return null;
    }

    $number = str_replace([' ', ','], ['', '.'], $number);
    if (substr_count($number, '.') > 1) {
        $parts = explode('.', $number);
        $decimal = array_pop($parts);
        $number = implode('', $parts) . '.' . $decimal;
    }

    if (!is_numeric($number)) {
        return null;
    }

    return (int)round(((float)$number) * 100);
}

function format_price_from_cents(?int $cents): ?string
{
    if ($cents === null) {
        return null;
    }

    $amount = $cents / 100;
    return number_format($amount, 2, ',', '.') . ' €';
}

function json_response($data, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function parse_partial_date(?string $value): array
{
    if (!$value) {
        return ['year' => '', 'month' => '', 'day' => ''];
    }

    $parts = explode('-', $value);
    $year = $parts[0] ?? '';
    $month = $parts[1] ?? '';
    $day = $parts[2] ?? '';

    return [
        'year' => ctype_digit($year) && strlen($year) === 4 ? $year : '',
        'month' => ctype_digit($month ?? '') && strlen($month) === 2 ? $month : '',
        'day' => ctype_digit($day ?? '') && strlen($day) === 2 ? $day : '',
    ];
}

function normalize_partial_date_input(array $input): array
{
    $year = trim((string)($input['year'] ?? ''));
    $month = trim((string)($input['month'] ?? ''));
    $day = trim((string)($input['day'] ?? ''));

    if ($year === '' && ($month !== '' || $day !== '')) {
        return [null, 'Bitte zunächst ein Jahr auswählen, bevor Monat oder Tag gesetzt werden.'];
    }

    if ($year === '') {
        return [null, null];
    }

    if ($month === '' && $day !== '') {
        return [null, 'Bitte wählen Sie einen Monat, bevor Sie einen Tag festlegen.'];
    }

    $yearInt = (int)$year;
    if ($yearInt < 1900 || $yearInt > (int)date('Y') + 1) {
        return [null, 'Das ausgewählte Jahr liegt außerhalb des zulässigen Bereichs.'];
    }

    $parts = [$year];

    if ($month !== '') {
        $monthInt = (int)$month;
        if ($monthInt < 1 || $monthInt > 12) {
            return [null, 'Der ausgewählte Monat ist ungültig.'];
        }
        $parts[] = str_pad((string)$monthInt, 2, '0', STR_PAD_LEFT);

        if ($day !== '') {
            $dayInt = (int)$day;
            $maxDay = (int)date('t', strtotime(sprintf('%04d-%02d-01', $yearInt, $monthInt)));
            if ($dayInt < 1 || $dayInt > $maxDay) {
                return [null, 'Der ausgewählte Tag passt nicht zum gewählten Monat.'];
            }
            $parts[] = str_pad((string)$dayInt, 2, '0', STR_PAD_LEFT);
        }
    }

    return [implode('-', $parts), null];
}

function format_partial_date(?string $value): ?string
{
    if (!$value) {
        return null;
    }

    $parts = parse_partial_date($value);
    if ($parts['year'] === '') {
        return null;
    }

    $year = $parts['year'];
    if ($parts['month'] === '') {
        return $year;
    }

    $monthNames = [
        1 => 'Januar',
        2 => 'Februar',
        3 => 'März',
        4 => 'April',
        5 => 'Mai',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'August',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Dezember',
    ];
    $monthName = $monthNames[(int)$parts['month']] ?? null;
    if ($parts['day'] === '') {
        return $monthName ? sprintf('%s %s', $monthName, $year) : $year;
    }

    return sprintf('%02d. %s %s', (int)$parts['day'], $monthName ?: '', $year);
}

function build_gene_state_label(array $gene, string $state): ?string
{
    $state = trim($state);
    if ($state === '' || !in_array($state, ['normal', 'heterozygous', 'homozygous'], true)) {
        return null;
    }

    $name = $gene['name'] ?? '';
    if ($state === 'heterozygous') {
        return $gene['heterozygous_label'] ?: ($name ? $name . ' (het)' : null);
    }

    if ($state === 'homozygous') {
        return $gene['homozygous_label'] ?: ($name ? $name . ' (hom)' : null);
    }

    return $gene['normal_label'] ?: ($name ?: null);
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
