<?php
// ADMIN — Custom Scripts
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'Custom Scripts';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    foreach (['header_script','footer_script','ga_code','fb_pixel','gtm_code'] as $f) {
        if (isset($_POST[$f])) updateSetting($f, $_POST[$f]);
    }
    echo json_encode(['success'=>true,'message'=>'Script đã được lưu!']);
    exit;
}
$s = getAllSettings();
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
  <a href="scripts.php" class="cms-tab active"><i class="fas fa-code"></i> Scripts</a>
  <a href="password.php" class="cms-tab"><i class="fas fa-lock"></i> Security</a>
  <a href="backup.php" class="cms-tab"><i class="fas fa-database"></i> Backup</a>
</div>
<form id="scriptsForm"><<?= csrfField() ?>
<div class="grid-2" style="gap:20px;">
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-code"></i></div><h3>Custom Scripts</h3><span class="ach-sub">Chèn trực tiếp vào &lt;head&gt; hoặc &lt;/body&gt;</span></div>
  <div class="admin-card-body">
    <div class="form-group"><label class="form-label">Header Script <span class="label-hint">(trong &lt;head&gt;)</span></label><textarea name="header_script" class="form-control font-mono" rows="5" placeholder="<!-- Custom scripts in <head> -->"><?=e($s['header_script']??'')?></textarea></div>
    <div class="form-group"><label class="form-label">Footer Script <span class="label-hint">(trước &lt;/body&gt;)</span></label><textarea name="footer_script" class="form-control font-mono" rows="5" placeholder="<!-- Custom scripts before </body> -->"><?=e($s['footer_script']??'')?></textarea></div>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-chart-line"></i></div><h3>Analytics & Tracking</h3></div>
  <div class="admin-card-body">
    <div class="form-group">
      <label class="form-label"><img src="https://www.google.com/favicon.ico" width="14" height="14" style="margin-right:6px;"> Google Analytics ID</label>
      <input type="text" name="ga_code" class="form-control font-mono" value="<?=e($s['ga_code']??'')?>" placeholder="G-XXXXXXXXXX">
    </div>
    <div class="form-group">
      <label class="form-label"><i class="fab fa-facebook" style="margin-right:6px;color:#1877F2;"></i> Facebook Pixel ID</label>
      <input type="text" name="fb_pixel" class="form-control font-mono" value="<?=e($s['fb_pixel']??'')?>" placeholder="123456789012345">
    </div>
    <div class="form-group">
      <label class="form-label"><i class="fab fa-google" style="margin-right:6px;"></i> GTM Container ID</label>
      <input type="text" name="gtm_code" class="form-control font-mono" value="<?=e($s['gtm_code']??'')?>" placeholder="GTM-XXXXXXX">
    </div>
    <div style="margin-top:12px;padding:12px;background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15);border-radius:8px;font-size:12px;color:var(--text-3);">
      <i class="fas fa-shield-alt" style="color:var(--accent);margin-right:6px;"></i>
      Scripts được chèn trực tiếp vào HTML. Chỉ thêm code tin cậy từ nguồn uy tín.
    </div>
  </div>
</div>
</div>
<div style="margin-top:16px;display:flex;justify-content:flex-end;">
  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Scripts</button>
</div>
</form>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>document.getElementById('scriptsForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('scriptsForm',location.href);});</script>
</body></html>
