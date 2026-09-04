<?php
// API — Contact Form Handler
require_once dirname(__DIR__) . '/includes/functions.php';
startSecureSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// CSRF
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? '')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ.']));
}

// Rate limit
$ip = getClientIp();
$stmt = db()->prepare("SELECT COUNT(*) FROM contacts WHERE ip_address = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)");
$stmt->execute([$ip]);
if ((int)$stmt->fetchColumn() >= 5) {
    http_response_code(429);
    die(json_encode(['success' => false, 'message' => 'Bạn đã gửi quá nhiều tin nhắn. Vui lòng thử lại sau.']));
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validate
$errors = [];
if (empty($name) || strlen($name) < 2) $errors[] = 'Vui lòng nhập họ tên (ít nhất 2 ký tự).';
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email không hợp lệ.';
if (empty($message) || strlen($message) < 10) $errors[] = 'Nội dung tin nhắn quá ngắn (ít nhất 10 ký tự).';

if (!empty($errors)) {
    die(json_encode(['success' => false, 'message' => implode(' ', $errors)]));
}

// Honeypot
if (!empty($_POST['website'])) {
    die(json_encode(['success' => true])); // Fake success for bots
}

// Save to DB
$stmt = db()->prepare("INSERT INTO contacts (name, email, phone, message, ip_address) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$name, $email, $phone, $message, $ip]);

// Send email notification (if configured)
$adminEmail = getSetting('email', '');
$siteName = getSetting('site_name', 'Profile');
if ($adminEmail && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
    // [SEC-M2] Sanitize header fields to prevent Email Header Injection
    $safeName    = str_replace(["\r","\n",':',','], '', $name);
    $safeEmail   = filter_var($email, FILTER_SANITIZE_EMAIL);
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $subject = "[" . $siteName . "] Tin nhắn mới từ " . $safeName;
    $body    = "Bạn có tin nhắn mới!\n\nHọ tên: $safeName\nEmail: $safeEmail\nSĐT: $phone\n\nNội dung:\n$safeMessage\n\nIP: $ip\nThời gian: " . date('d/m/Y H:i:s');
    @mail($adminEmail, $subject, $body, "From: noreply@localhost\r\nReply-To: $safeEmail");
}

echo json_encode(['success' => true, 'message' => 'Tin nhắn đã được gửi thành công! Tôi sẽ liên hệ lại sớm nhất.']);
