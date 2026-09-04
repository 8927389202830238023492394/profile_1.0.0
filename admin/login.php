<?php
// ============================================================
// ADMIN LOGIN PAGE
// ============================================================
require_once dirname(__DIR__) . '/includes/functions.php';
startSecureSession();

// Already logged in?
if (!empty($_SESSION['admin_id'])) {
    header('Location: ' . ADMIN_URL);
    exit;
}

$error   = '';
$success = '';
$ip      = getClientIp();

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
        $error = 'Token bảo mật không hợp lệ. Vui lòng thử lại.';
    } else {
        // Rate limiting
        $stmt = db()->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $attempt = $stmt->fetch();

        if ($attempt && $attempt['attempts'] >= LOGIN_MAX_ATTEMPTS) {
            $lockoutEnd = strtotime($attempt['last_attempt']) + (LOGIN_LOCKOUT_MINUTES * 60);
            if (time() < $lockoutEnd) {
                $remaining = ceil(($lockoutEnd - time()) / 60);
                $error = "Quá nhiều lần đăng nhập sai. Thử lại sau $remaining phút.";
            } else {
                // Reset
                db()->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
                $attempt = null;
            }
        }

        if (!$error) {
            $username = sanitize($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';

            $stmt = db()->prepare("SELECT id, username, password FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Success — clear attempts
                db()->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);

                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_user'] = $user['username'];
                $_SESSION['last_regen'] = time();
                session_regenerate_id(true);

                // Remember me — [SEC-H4] Use dynamic Secure flag based on HTTPS
                if (!empty($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    db()->prepare("UPDATE users SET remember_token = ? WHERE id = ?")->execute([$token, $user['id']]);
                    $isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
                    setcookie('admin_remember', $token, time() + (30 * 86400), '/', '', $isSecure, true);
                }

                header('Location: ' . ADMIN_URL);
                exit;
            } else {
                // Log attempt
                $db = db();
                $db->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1)
                    ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = NOW()")
                    ->execute([$ip]);
                $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
            }
        }
    }
}

// Check remember cookie
if (empty($_SESSION['admin_id']) && !empty($_COOKIE['admin_remember'])) {
    // [SEC] Use hash comparison to prevent timing attacks
    $stmt = db()->prepare("SELECT id, username FROM users WHERE remember_token = ? AND remember_token IS NOT NULL");
    $stmt->execute([$_COOKIE['admin_remember']]);
    $user = $stmt->fetch();
    if ($user) {
        $_SESSION['admin_id']       = $user['id'];
        $_SESSION['admin_user']     = $user['username'];
        $_SESSION['last_activity']  = time();
        $_SESSION['last_regen']     = time();
        session_regenerate_id(true); // [SEC-C2] Regenerate on cookie-based login
        header('Location: ' . ADMIN_URL);
        exit;
    }
}

$pageTitle = 'Đăng Nhập';
$accentColor = getSetting('accent_color', '#6366F1');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login — Cyber Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="<?= ADMIN_URL ?>assets/css/admin.css">
  <style>
    :root { --accent: <?= e($accentColor) ?>; }
  </style>
</head>
<body>

<div class="login-page">
  <div class="login-card">
    <div class="login-logo">
      <div class="logo-circle"><i class="fas fa-bolt"></i></div>
      <h2>Cyber Admin</h2>
      <p>Đăng nhập để quản lý profile của bạn</p>
    </div>

    <?php if ($error): ?>
    <div class="login-alert error"><i class="fas fa-exclamation-circle"></i><?= e($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
    <div class="login-alert success"><i class="fas fa-check-circle"></i><?= e($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>
      <?= csrfField() ?>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-user" style="color:var(--accent);font-size:12px;"></i>
          Tên đăng nhập
        </label>
        <div style="position:relative;">
          <input type="text" name="username" class="form-control" placeholder="admin"
                 value="<?= e($_POST['username'] ?? '') ?>" autocomplete="username" required
                 style="padding-left:42px;" autofocus>
          <i class="fas fa-user" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:13px;pointer-events:none;"></i>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-lock" style="color:var(--accent);font-size:12px;"></i>
          Mật khẩu
        </label>
        <div style="position:relative;">
          <input type="password" name="password" id="passwordInput" class="form-control" placeholder="••••••••"
                 autocomplete="current-password" required
                 style="padding-left:42px;padding-right:42px;">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:var(--text-3);font-size:13px;pointer-events:none;"></i>
          <button type="button" onclick="togglePwd()" style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-3);cursor:pointer;font-size:14px;" id="togglePwdBtn">
            <i class="fas fa-eye"></i>
          </button>
        </div>
      </div>

      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;">
        <label class="toggle-wrap" style="cursor:pointer;">
          <span class="toggle-switch">
            <input type="checkbox" name="remember" id="rememberMe">
            <span class="toggle-slider"></span>
          </span>
          <span class="toggle-label" style="font-size:13px;">Ghi nhớ đăng nhập</span>
        </label>
      </div>

      <button type="submit" class="btn btn-primary w-100" style="justify-content:center;padding:12px;">
        <i class="fas fa-sign-in-alt"></i> Đăng Nhập
      </button>
    </form>

    <div style="margin-top:24px;text-align:center;font-size:12px;color:var(--text-3);">
      <i class="fas fa-shield-alt" style="color:var(--accent);margin-right:4px;"></i>
      Kết nối bảo mật — CSRF Protected — Rate Limited
    </div>
  </div>
</div>

<script>
function togglePwd() {
  const inp = document.getElementById('passwordInput');
  const btn = document.getElementById('togglePwdBtn');
  const show = inp.type === 'password';
  inp.type = show ? 'text' : 'password';
  btn.querySelector('i').className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
}
</script>
</body>
</html>
