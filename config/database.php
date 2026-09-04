<?php
// ============================================================
// DATABASE CONFIGURATION
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'profile_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Base path
define('BASE_PATH', dirname(__DIR__));
// [SEC-H1] Sanitize HTTP_HOST to prevent Host Header Injection
$_allowedHosts = ['localhost', '127.0.0.1'];
$_rawHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$_host = preg_replace('/[^a-zA-Z0-9.\-:]/', '', $_rawHost); // strip dangerous chars
// Auto-detect base directory perfectly
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
$docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$baseDir = str_replace('\\', '/', BASE_PATH);
$basePathUrl = str_replace($docRoot, '', $baseDir);
$basePathUrl = rtrim($basePathUrl, '/') . '/';
// Fallback if running from CLI or unusual environments
if (empty($basePathUrl) || !str_starts_with($basePathUrl, '/')) {
    $basePathUrl = '/';
}

define('BASE_URL', $protocol . $_host . $basePathUrl);
define('ADMIN_URL', BASE_URL . 'admin/');
define('UPLOAD_PATH', BASE_PATH . '/assets/uploads/');
define('UPLOAD_URL', BASE_URL . 'assets/uploads/');

// Security
define('CSRF_TOKEN_NAME', '_csrf_token');
define('SESSION_NAME', 'profile_admin_sess');
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ============================================================
// PDO CONNECTION (Singleton)
// ============================================================
class Database {
    private static ?PDO $instance = null;

    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$instance = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                http_response_code(500);
                die(json_encode(['error' => 'Database connection failed.']));
            }
        }
        return self::$instance;
    }
}

function db(): PDO {
    return Database::getInstance();
}
