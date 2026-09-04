<?php
// ============================================================
// GLOBAL HELPER FUNCTIONS
// ============================================================
// [SEC-M1] Suppress errors from browser output in production
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/levuphong_branding.php';
require_once __DIR__ . '/../config/levuphong_license.php';
require_once __DIR__ . '/../config/levuphong_signature.php';

// ── Settings ────────────────────────────────────────────────
function getSetting(string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = db()->prepare("SELECT setting_value FROM settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        $cache[$key] = $row ? (string)$row['setting_value'] : $default;
    }
    return $cache[$key];
}

function getAllSettings(): array {
    $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
    $rows = $stmt->fetchAll();
    $out = [];
    foreach ($rows as $r) $out[$r['setting_key']] = $r['setting_value'];
    return $out;
}

function updateSetting(string $key, string $value): void {
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=?, updated_at=NOW()");
    $stmt->execute([$key, $value, $value]);
}

// ── Security ─────────────────────────────────────────────────
function sanitize(string $str): string {
    return htmlspecialchars(strip_tags(trim($str)), ENT_QUOTES, 'UTF-8');
}

function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

// ── Visit Tracking ────────────────────────────────────────────
function trackVisit(): void {
    $ip = getClientIp();
    $today = date('Y-m-d');
    $sessionKey = 'visited_' . $today;

    // Upsert daily count
    $stmt = db()->prepare("INSERT INTO visits (visit_date, visit_count, unique_count) VALUES (?, 1, 1)
        ON DUPLICATE KEY UPDATE visit_count = visit_count + 1,
        unique_count = unique_count + IF(?, 1, 0)");
    $isUnique = empty($_SESSION[$sessionKey]) ? 1 : 0;
    $stmt->execute([$today, $isUnique]);
    $_SESSION[$sessionKey] = true;

    // Track online session
    $sid = session_id();
    $stmt2 = db()->prepare("INSERT INTO online_sessions (session_id, ip_address, last_seen) VALUES (?,?,NOW())
        ON DUPLICATE KEY UPDATE last_seen=NOW(), ip_address=?");
    $stmt2->execute([$sid, $ip, $ip]);

    // Purge sessions older than 5 minutes
    db()->exec("DELETE FROM online_sessions WHERE last_seen < DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
}

function getOnlineCount(): int {
    $stmt = db()->query("SELECT COUNT(*) FROM online_sessions WHERE last_seen > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
    return (int)$stmt->fetchColumn();
}

function getClientIp(): string {
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

// ── Visit Stats ───────────────────────────────────────────────
function getVisitStats(): array {
    $today = db()->query("SELECT COALESCE(SUM(visit_count),0) FROM visits WHERE visit_date = CURDATE()")->fetchColumn();
    $yesterday = db()->query("SELECT COALESCE(SUM(visit_count),0) FROM visits WHERE visit_date = DATE_SUB(CURDATE(),INTERVAL 1 DAY)")->fetchColumn();
    $week = db()->query("SELECT COALESCE(SUM(visit_count),0) FROM visits WHERE visit_date >= DATE_SUB(CURDATE(),INTERVAL 7 DAY)")->fetchColumn();
    $month = db()->query("SELECT COALESCE(SUM(visit_count),0) FROM visits WHERE visit_date >= DATE_SUB(CURDATE(),INTERVAL 30 DAY)")->fetchColumn();
    $total = db()->query("SELECT COALESCE(SUM(visit_count),0) FROM visits")->fetchColumn();
    return compact('today','yesterday','week','month','total');
}

// ── Page Sections ─────────────────────────────────────────────
function getPageSections(): array {
    return db()->query("SELECT * FROM page_sections ORDER BY sort_order ASC")->fetchAll();
}

// ── File Upload ────────────────────────────────────────────────
function uploadFile(array $file, string $folder = 'general'): array {
    if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Lỗi upload (Mã lỗi: ' . ($file['error'] ?? 'unknown') . ').'];
    }

    // [SEC-H2] Extension blacklist — prevent shell upload
    $blacklistExt = ['php','php3','php4','php5','php7','phtml','phar','shtml','asp','aspx','jsp','exe','sh','bat','cgi','cfm','htaccess'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (in_array($ext, $blacklistExt, true)) {
        return ['success' => false, 'message' => 'Loại file không được phép tải lên.'];
    }

    // [SEC-H2] Verify MIME using finfo (server-side, not client-supplied)
    $allowedMimes = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $realMime = $finfo->file($file['tmp_name']);
    } elseif (function_exists('mime_content_type')) {
        $realMime = mime_content_type($file['tmp_name']);
    } else {
        // Ultimate fallback (less secure but prevents 500 error on weak servers)
        $mimes = ['jpg'=>'image/jpeg','jpeg'=>'image/jpeg','png'=>'image/png','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml'];
        $realMime = $mimes[$ext] ?? 'application/octet-stream';
    }

    if (!in_array($realMime, $allowedMimes, true)) {
        return ['success' => false, 'message' => 'Loại file không được hỗ trợ (MIME không hợp lệ).'];
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File quá lớn (tối đa 5MB).'];
    }

    // [SEC] Generate random filename to prevent path guessing
    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $folder = preg_replace('/[^a-zA-Z0-9_\-]/', '', $folder); // sanitize folder name
    $dir = UPLOAD_PATH . $folder . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $path = $dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $path)) {
        return ['success' => false, 'message' => 'Upload thất bại.'];
    }

    $url = UPLOAD_URL . $folder . '/' . $filename;

    // Save to media table
    $stmt = db()->prepare("INSERT INTO media (filename, original_name, path, url, type, size, folder) VALUES (?,?,?,?,?,?,?)");
    $stmt->execute([$filename, $file['name'], $path, $url, 'image', $file['size'], $folder]);
    $mediaId = db()->lastInsertId();

    return ['success' => true, 'url' => $url, 'filename' => $filename, 'id' => $mediaId];
}

// ── Pagination ─────────────────────────────────────────────────
function paginate(string $table, int $page, int $perPage, string $where = '', array $params = [], string $orderBy = 'id DESC'): array {
    $where = $where ? "WHERE $where" : '';
    $countStmt = db()->prepare("SELECT COUNT(*) FROM `$table` $where");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $perPage;
    $stmt = db()->prepare("SELECT * FROM `$table` $where ORDER BY $orderBy LIMIT $perPage OFFSET $offset");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    return [
        'rows'       => $rows,
        'total'      => $total,
        'page'       => $page,
        'per_page'   => $perPage,
        'last_page'  => (int)ceil($total / $perPage),
    ];
}

// ── JSON Response ─────────────────────────────────────────────
function jsonResponse(array $data, int $code = 200): never {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ── Redirect ───────────────────────────────────────────────────
function redirect(string $url): never {
    header("Location: $url");
    exit;
}

// ── Number Format ─────────────────────────────────────────────
function formatNumber(string $value): string {
    if (is_numeric($value)) {
        return number_format((float)$value, 0, ',', '.');
    }
    return $value;
}

// ── Time Ago ──────────────────────────────────────────────────
function timeAgo(string $datetime): string {
    $now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);
    if ($diff->y > 0) return $diff->y . ' năm trước';
    if ($diff->m > 0) return $diff->m . ' tháng trước';
    if ($diff->d > 0) return $diff->d . ' ngày trước';
    if ($diff->h > 0) return $diff->h . ' giờ trước';
    if ($diff->i > 0) return $diff->i . ' phút trước';
    return 'Vừa xong';
}


// ── Session Start ─────────────────────────────────────────────
function startSecureSession(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'domain'   => '',
            'secure'   => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}
