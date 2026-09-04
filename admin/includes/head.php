<?php
// ============================================================
// ADMIN HEAD — Common HTML head for all admin pages
// ============================================================
// Variables expected: $pageTitle
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?= HTML_BRANDING_COMMENT ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <meta name="author" content="<?= PROJECT_AUTHOR ?>">
  <meta name="generator" content="<?= PROJECT_GENERATOR ?>">
  <title><?= e($pageTitle ?? 'Admin') ?> — Cyber Admin Panel</title>

  <!-- Fonts & Icons -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Admin CSS -->
  <link rel="stylesheet" href="<?= ADMIN_URL ?>assets/css/admin.css">

  <!-- Dynamic Theme from DB -->
  <?php
    $accent = getSetting('accent_color', '#6366F1');
    $accent2 = getSetting('accent_secondary', '#8B5CF6');
  ?>
  <style>
    :root {
      --accent: <?= e($accent) ?>;
      --accent-2: <?= e($accent2) ?>;
    }
  </style>
</head>
<body>
<div id="admin-toast-container"></div>
