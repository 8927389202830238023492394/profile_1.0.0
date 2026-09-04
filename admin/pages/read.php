<?php
// [SEC] This file has been disabled for security reasons.
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
http_response_code(404);
exit;
