<?php
// ============================================================
// ADMIN TOPBAR
// ============================================================
$adminUser = getAdminUser();
?>
<header class="admin-topbar" id="adminTopbar">
  <button class="mobile-toggle" id="mobileToggle" aria-label="Toggle sidebar">
    <i class="fas fa-bars"></i>
  </button>

  <div class="topbar-title">
    <h1 id="pageTitle"><?= $pageTitle ?? 'Dashboard' ?></h1>
    <div class="breadcrumb">
      <a href="<?= ADMIN_URL ?>">Admin</a>
      <i class="fas fa-chevron-right"></i>
      <span><?= $pageTitle ?? 'Dashboard' ?></span>
    </div>
  </div>

  <div class="topbar-actions">
    <a href="<?= BASE_URL ?>" target="_blank" class="topbar-btn" title="Xem Frontend">
      <i class="fas fa-external-link-alt"></i>
    </a>

    <?php
      $unreadCount = (int)db()->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
    ?>
    <a href="<?= ADMIN_URL ?>pages/contacts.php" class="topbar-btn" title="Tin nhắn mới">
      <i class="fas fa-envelope"></i>
      <?php if ($unreadCount > 0): ?>
      <span class="badge-dot"></span>
      <?php endif; ?>
    </a>

    <a href="<?= ADMIN_URL ?>pages/password.php" class="topbar-btn" title="Cài đặt">
      <i class="fas fa-cog"></i>
    </a>

    <a href="<?= ADMIN_URL ?>logout.php" class="topbar-btn" title="Đăng xuất" style="color:var(--danger);" onclick="return confirm('Bạn có chắc muốn đăng xuất?')">
      <i class="fas fa-sign-out-alt"></i>
    </a>
  </div>
</header>
