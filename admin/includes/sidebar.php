<?php
// ============================================================
// ADMIN SIDEBAR
// ============================================================
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function navItem(string $href, string $icon, string $label, string $page, string $current, ?int $badge = null): void {
    $active = ($current === $page) ? ' active' : '';
    $badgeHtml = $badge !== null ? "<span class='nav-badge'>$badge</span>" : '';
    echo "<a href='$href' class='nav-item$active'><i class='$icon'></i> $label $badgeHtml</a>";
}

// Get unread contacts count
try {
    $unreadContacts = (int)db()->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
    $pendingReviews = (int)db()->query("SELECT COUNT(*) FROM testimonials WHERE approved=0")->fetchColumn();
} catch (\Throwable) {
    $unreadContacts = $pendingReviews = 0;
}

$page = (isset($_GET['page']) ? $_GET['page'] : ($currentDir === 'pages' ? $currentPage : 'dashboard'));
?>
<aside class="admin-sidebar" id="adminSidebar">

  <!-- Logo -->
  <a href="<?= ADMIN_URL ?>" class="sidebar-logo">
    <div class="logo-icon"><i class="fas fa-bolt"></i></div>
    <div>
      <div class="logo-text">Cyber Admin</div>
      <div class="logo-sub">Dashboard Panel</div>
    </div>
  </a>

  <!-- Nav -->
  <nav class="sidebar-nav">

    <div class="nav-group">
      <div class="nav-group-label">Core</div>
      <?php navItem(ADMIN_URL, 'fas fa-home', 'Dashboard', 'index', $page); ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">Content</div>
      <?php navItem(ADMIN_URL . 'pages/profile', 'fas fa-user-circle', 'Personal Profile', 'profile', $page); ?>
      <?php navItem(ADMIN_URL . 'pages/services', 'fas fa-layer-group', 'Service Ecosystem', 'services', $page); ?>
      <?php navItem(ADMIN_URL . 'pages/websites', 'fas fa-globe', 'Website Ecosystem', 'websites', $page); ?>
      <?php navItem(ADMIN_URL . 'pages/media', 'fas fa-images', 'Media Library', 'media', $page); ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">Engagement</div>
      <?php navItem(ADMIN_URL . 'pages/marketing', 'fas fa-bullhorn', 'Marketing & Inbox', 'marketing', $page, ($unreadContacts+$pendingReviews) ?: null); ?>
      <?php navItem(ADMIN_URL . 'pages/banks', 'fas fa-credit-card', 'Payments', 'banks', $page); ?>
    </div>

    <div class="nav-group">
      <div class="nav-group-label">System</div>
      <?php navItem(ADMIN_URL . 'pages/theme', 'fas fa-palette', 'Theme Customizer', 'theme', $page); ?>
      <?php navItem(ADMIN_URL . 'pages/system', 'fas fa-cog', 'System Settings', 'system', $page); ?>
      <?php navItem(ADMIN_URL . 'pages/about', 'fas fa-info-circle', 'About System', 'about', $page); ?>
    </div>

  </nav>

  <!-- Footer -->
  <div class="sidebar-footer">
    <div style="text-align: center; margin-bottom: 16px; font-size: 11px; color: var(--text-3);">
      Powered by <a href="<?= PROJECT_WEBSITE ?>" target="_blank" style="color: var(--text-2); font-weight: 600; text-decoration: none;"><?= PROJECT_AUTHOR ?></a><br>
      <i class="fab fa-whatsapp" style="margin-right: 2px;"></i> <?= PROJECT_CONTACT ?>
    </div>

    <a href="<?= BASE_URL ?>" target="_blank" class="sidebar-user" style="margin-bottom:8px;">
      <div class="su-avatar" style="background:rgba(16,185,129,0.2);border:1px solid rgba(16,185,129,0.3);">
        <i class="fas fa-external-link-alt" style="font-size:12px;color:#10B981;"></i>
      </div>
      <div>
        <div class="su-name">Xem Frontend</div>
        <div class="su-role">profile/index.php</div>
      </div>
    </a>
    <?php $adminUser = getAdminUser(); ?>
    <div class="sidebar-user">
      <div class="su-avatar"><?= strtoupper(substr($adminUser['username'] ?? 'A', 0, 1)) ?></div>
      <div>
        <div class="su-name"><?= e($adminUser['username'] ?? 'Admin') ?></div>
        <div class="su-role">Administrator</div>
      </div>
      <a href="<?= ADMIN_URL ?>logout.php" class="su-logout" title="Đăng xuất">
        <i class="fas fa-sign-out-alt"></i>
      </a>
    </div>
  </div>

</aside>

<!-- Mobile Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>
