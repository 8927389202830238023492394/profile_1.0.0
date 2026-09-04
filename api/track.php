<?php
// API — Visit Tracking
require_once dirname(__DIR__) . '/includes/functions.php';
header('Content-Type: application/json');

// Only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

// Ignore bots
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$botPatterns = ['bot', 'crawl', 'spider', 'wget', 'curl', 'python', 'java'];
foreach ($botPatterns as $bot) {
    if (stripos($ua, $bot) !== false) { echo json_encode(['tracked' => false]); exit; }
}

startSecureSession();
trackVisit();
echo json_encode(['tracked' => true, 'online' => getOnlineCount()]);
