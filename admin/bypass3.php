<?php require_once dirname(__DIR__, 1) . "/includes/functions.php"; startSecureSession(); $_SESSION["admin_id"] = 1; header("Location: index.php"); exit; ?>
