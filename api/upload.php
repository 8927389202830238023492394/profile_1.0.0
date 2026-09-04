<?php
// API — File Upload Handler (used by admin)
require_once dirname(__DIR__) . '/includes/functions.php';
startSecureSession();

// Only admins can upload
if (empty($_SESSION['admin_id'])) {
    http_response_code(401);
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['success' => false, 'message' => 'Method not allowed']));
}

// CSRF
if (!verifyCsrfToken($_POST[CSRF_TOKEN_NAME] ?? $_POST['_csrf_token'] ?? '')) {
    http_response_code(403);
    die(json_encode(['success' => false, 'message' => 'CSRF token không hợp lệ.']));
}

header('Content-Type: application/json');

$file = $_FILES['file'] ?? $_FILES['files'][0] ?? null;
if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    die(json_encode(['success' => false, 'message' => 'Không có file hoặc file bị lỗi.']));
}

$folder = sanitize($_POST['folder'] ?? 'general');
$result = uploadFile($file, $folder);

if ($result['success']) {
    // Save to media table
    $id = db()->lastInsertId() ?: 0;
    // If uploadFile saves to media table already, skip
    echo json_encode(array_merge($result, ['id' => $id]));
} else {
    echo json_encode($result);
}
