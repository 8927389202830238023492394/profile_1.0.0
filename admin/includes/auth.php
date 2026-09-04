<?php
// ============================================================
// ADMIN AUTH MIDDLEWARE
// ============================================================
require_once dirname(__DIR__, 2) . '/includes/functions.php';
startSecureSession();

function requireAuth(): void {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ' . ADMIN_URL . 'login.php');
        exit;
    }
    // [SEC-C3] Session Idle Timeout — 2 hours
    $timeout = 7200; // 2 hours
    if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout) {
        session_unset();
        session_destroy();
        header('Location: ' . ADMIN_URL . 'login.php?timeout=1');
        exit;
    }
    $_SESSION['last_activity'] = time();

    // [SEC-C2] Regenerate session ID periodically to prevent fixation
    if (empty($_SESSION['last_regen']) || time() - $_SESSION['last_regen'] > 300) {
        session_regenerate_id(true);
        $_SESSION['last_regen'] = time();
    }
}

function getAdminUser(): array {
    if (empty($_SESSION['admin_id'])) return [];
    $stmt = db()->prepare("SELECT id, username, email, avatar FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    return $stmt->fetch() ?: [];
}

function requireCsrf(): void {
    $token = $_POST[CSRF_TOKEN_NAME] ?? '';
    if (!verifyCsrfToken($token)) {
        http_response_code(403);
        die(json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ.']));
    }
}
