<?php
// ADMIN — Change Password
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'Đổi Mật Khẩu';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (strlen($new) < 8) { echo json_encode(['success'=>false,'message'=>'Mật khẩu mới phải có ít nhất 8 ký tự.']); exit; }
    if ($new !== $confirm) { echo json_encode(['success'=>false,'message'=>'Mật khẩu xác nhận không khớp.']); exit; }
    $user = db()->prepare("SELECT password FROM users WHERE id=?");
    $user->execute([$_SESSION['admin_id']]);
    $userData = $user->fetch();
    if (!$userData || !password_verify($current, $userData['password'])) { echo json_encode(['success'=>false,'message'=>'Mật khẩu hiện tại không đúng.']); exit; }
    $hash = password_hash($new, PASSWORD_BCRYPT, ['cost' => 12]);
    db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $_SESSION['admin_id']]);
    echo json_encode(['success'=>true,'message'=>'Mật khẩu đã được thay đổi thành công!']);
    exit;
}
include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<!-- ── V2: Page Header ──────────────────────────── -->
<div style="margin-bottom: 24px;">
  <h1 style="font-size: 24px; font-weight: 700;">System Settings</h1>
  <p style="color: var(--text-2); font-size: 14px;">Quản lý cấu hình cốt lõi, bảo mật và SEO của hệ thống.</p>
</div>

<!-- ── V2: Tabs ──────────────────────────── -->
<div class="cms-tabs">
  <a href="system.php" class="cms-tab"><i class="fas fa-cog"></i> Core Settings</a>
  <a href="seo.php" class="cms-tab"><i class="fas fa-search"></i> SEO</a>
  <a href="scripts.php" class="cms-tab"><i class="fas fa-code"></i> Scripts</a>
  <a href="password.php" class="cms-tab active"><i class="fas fa-lock"></i> Security</a>
  <a href="backup.php" class="cms-tab"><i class="fas fa-database"></i> Backup</a>
</div>
<div style="max-width:480px;margin:0 auto;">
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-lock"></i></div><h3>Đổi Mật Khẩu</h3></div>
  <div class="admin-card-body">
    <form id="pwForm"><<?= csrfField() ?>
      <div class="form-group"><label class="form-label">Mật khẩu hiện tại <span class="required">*</span></label><input type="password" name="current_password" class="form-control" required autocomplete="current-password"></div>
      <div class="form-group"><label class="form-label">Mật khẩu mới <span class="label-hint">(ít nhất 8 ký tự)</span></label><input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password"></div>
      <div class="form-group"><label class="form-label">Xác nhận mật khẩu mới <span class="required">*</span></label><input type="password" name="confirm_password" class="form-control" required autocomplete="new-password"></div>
      <div style="margin-top:8px;padding:12px;background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15);border-radius:8px;font-size:12.5px;color:var(--text-3);">
        <i class="fas fa-shield-alt" style="color:var(--accent);margin-right:6px;"></i> Sử dụng mật khẩu mạnh, ít nhất 8 ký tự, kết hợp chữ hoa, số và ký tự đặc biệt.
      </div>
      <button type="submit" class="btn btn-primary w-100" style="justify-content:center;margin-top:16px;padding:12px;"><i class="fas fa-key"></i> Đổi Mật Khẩu</button>
    </form>
  </div>
</div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>document.getElementById('pwForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('pwForm',location.href,()=>{document.getElementById('pwForm').reset();});});</script>
</body></html>
