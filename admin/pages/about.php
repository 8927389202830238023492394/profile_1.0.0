<?php
// ============================================================
// ABOUT SYSTEM
// ============================================================
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

$pageTitle = 'About System';

include __DIR__ . '/../includes/head.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/../includes/topbar.php';
?>

<div class="admin-main">
<div class="content-area">

  <div class="admin-header">
    <div class="header-content">
      <h2><i class="fas fa-info-circle"></i> Về Hệ Thống</h2>
      <p>Thông tin bản quyền và tác giả hệ thống</p>
    </div>
  </div>

  <div class="grid-2">
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon" style="background: rgba(99,102,241,0.1); color: var(--accent);"><i class="fas fa-fingerprint"></i></div>
        <h3>Author & Copyright</h3>
      </div>
      <div class="admin-card-body" style="padding: 24px;">
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <span style="color: var(--text-2);">Tác Giả:</span>
            <span style="font-weight: 600; color: var(--text);"><?= PROJECT_AUTHOR ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <span style="color: var(--text-2);">Website:</span>
            <a href="<?= PROJECT_WEBSITE ?>" target="_blank" style="font-weight: 600; color: var(--accent);"><?= PROJECT_WEBSITE ?></a>
          </div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <span style="color: var(--text-2);">Liên Hệ Zalo:</span>
            <span style="font-weight: 600; color: var(--success);"><?= PROJECT_CONTACT ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; padding-bottom: 8px;">
            <span style="color: var(--text-2);">Bản Quyền:</span>
            <span style="font-size: 13px; color: var(--text-3);"><?= PROJECT_COPYRIGHT ?></span>
          </div>
        </div>
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon" style="background: rgba(16,185,129,0.1); color: var(--success);"><i class="fas fa-server"></i></div>
        <h3>System Details</h3>
      </div>
      <div class="admin-card-body" style="padding: 24px;">
        <div style="display: flex; flex-direction: column; gap: 16px;">
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <span style="color: var(--text-2);">Framework / Engine:</span>
            <span style="font-weight: 600; color: var(--text);"><?= PROJECT_GENERATOR ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; border-bottom: 1px solid var(--border); padding-bottom: 8px;">
            <span style="color: var(--text-2);">Namespace:</span>
            <span style="font-family: var(--font-mono); font-size: 13px; color: var(--text-3);"><?= LEVUPHONG_SIGNATURE ?></span>
          </div>
          <div style="display: flex; justify-content: space-between; padding-bottom: 8px;">
            <span style="color: var(--text-2);">PHP Environment:</span>
            <span style="font-weight: 600; color: var(--text);"><?= phpversion() ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="admin-card" style="margin-top: 24px; text-align: center; padding: 40px 20px;">
    <div style="font-size: 48px; color: var(--accent); margin-bottom: 16px;"><i class="fas fa-code"></i></div>
    <h2 style="margin-bottom: 8px;">Cảm ơn bạn đã sử dụng hệ thống!</h2>
    <p style="color: var(--text-2); max-width: 600px; margin: 0 auto; line-height: 1.6;">
      Cyber Profile V3 là sản phẩm tâm huyết được thiết kế với chuẩn mực cao nhất về UI/UX và bảo mật. Mọi thắc mắc hoặc cần hỗ trợ phát triển thêm tính năng, vui lòng liên hệ trực tiếp với tác giả qua Zalo.
    </p>
    <a href="https://zalo.me/<?= PROJECT_CONTACT ?>" target="_blank" class="btn btn-primary" style="margin-top: 24px;"><i class="fas fa-paper-plane"></i> Nhắn tin cho Tác Giả</a>
  </div>

</div>
</div>

<script src="<?= ADMIN_URL ?>assets/js/admin.js"></script>
</body></html>
