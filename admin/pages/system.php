<?php
// ADMIN — System Settings
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'System Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    $fields = ['site_name','site_url','timezone','pwa_enabled','pwa_theme_color','maintenance_mode','maintenance_message','visits_retention_days'];
    $checkboxes = ['pwa_enabled', 'maintenance_mode'];
    foreach ($fields as $f) {
        if (in_array($f, $checkboxes)) {
            $val = isset($_POST[$f]) ? '1' : '0';
            updateSetting($f, $val);
        } else if (isset($_POST[$f])) {
            updateSetting($f, trim($_POST[$f]));
        }
    }

    // Apply timezone
    $tz = trim($_POST['timezone'] ?? 'Asia/Ho_Chi_Minh');
    if (@date_default_timezone_set($tz)) updateSetting('timezone', $tz);

    // Maintenance mode file
    $maintFile = BASE_PATH . '/.maintenance';
    if (($_POST['maintenance_mode'] ?? '0') === '1') {
        file_put_contents($maintFile, date('Y-m-d H:i:s'));
    } else {
        if (file_exists($maintFile)) @unlink($maintFile);
    }

    echo json_encode(['success' => true, 'message' => 'Cài đặt hệ thống đã được lưu!']);
    exit;
}

// System info
$mysqlVer = e(db()->query("SELECT VERSION()")->fetchColumn());
$phpVer = phpversion();
$serverSoftware = e($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); // [SEC-M4] Escape output
$dbSize = db()->query("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size FROM information_schema.TABLES WHERE table_schema = '" . DB_NAME . "'")->fetchColumn();
$diskFree = round(disk_free_space(BASE_PATH) / 1024 / 1024 / 1024, 2);
$uploadSize = disk_total_space(UPLOAD_PATH) ? round(disk_total_space(UPLOAD_PATH) / 1024 / 1024 / 1024, 2) : 0;

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
  <a href="system.php" class="cms-tab active"><i class="fas fa-cog"></i> Core Settings</a>
  <a href="seo.php" class="cms-tab"><i class="fas fa-search"></i> SEO</a>
  <a href="scripts.php" class="cms-tab"><i class="fas fa-code"></i> Scripts</a>
  <a href="password.php" class="cms-tab"><i class="fas fa-lock"></i> Security</a>
  <a href="backup.php" class="cms-tab"><i class="fas fa-database"></i> Backup</a>
</div>

<!-- System Info -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:24px;">
  <?php $sysInfo = [
    ['PHP Version', $phpVer, 'fab fa-php', '#8892BF'],
    ['MySQL Version', $mysqlVer, 'fas fa-database', '#00618A'],
    ['Database Size', $dbSize . ' MB', 'fas fa-hdd', '#6366F1'],
    ['Disk Free', $diskFree . ' GB', 'fas fa-server', '#10B981'],
  ];
  foreach ($sysInfo as [$lbl,$val,$ico,$col]): ?>
  <div class="admin-card" style="padding:16px;text-align:center;">
    <i class="<?=$ico?>" style="font-size:24px;color:<?=$col?>;margin-bottom:8px;display:block;"></i>
    <div style="font-family:var(--font-mono);font-size:14px;font-weight:700;color:var(--text);"><?=e($val)?></div>
    <div style="font-size:11px;color:var(--text-3);margin-top:3px;"><?=e($lbl)?></div>
  </div>
  <?php endforeach; ?>
</div>

<form id="sysForm"><<?= csrfField() ?>
<div class="grid-2" style="gap:20px;">

<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-cog"></i></div><h3>Cài Đặt Chung</h3></div>
  <div class="admin-card-body">
    <div class="form-group"><label class="form-label">Tên website</label><input type="text" name="site_name" class="form-control" value="<?=e($s['site_name']??'Profile')?>"></div>
    <div class="form-group"><label class="form-label">URL website</label><input type="url" name="site_url" class="form-control" value="<?=e($s['site_url']??BASE_URL)?>"></div>
    <div class="form-group">
      <label class="form-label">Múi giờ</label>
      <select name="timezone" class="form-control">
        <?php foreach (['Asia/Ho_Chi_Minh'=>'Ho Chi Minh (+7)','Asia/Bangkok'=>'Bangkok (+7)','Asia/Singapore'=>'Singapore (+8)','UTC'=>'UTC (0)','America/New_York'=>'New York (-5)'] as $tz=>$lbl): ?>
        <option value="<?=$tz?>" <?=($s['timezone']??'Asia/Ho_Chi_Minh')===$tz?'selected':''?>><?=$lbl?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="form-group"><label class="form-label">Giữ dữ liệu thống kê (ngày)</label><input type="number" name="visits_retention_days" class="form-control" value="<?=e($s['visits_retention_days']??'90')?>" min="7" max="365"></div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-tools"></i></div><h3>Chế Độ Bảo Trì</h3></div>
  <div class="admin-card-body">
    <div class="form-group">
      <div class="toggle-wrap">
        <span class="toggle-switch"><input type="checkbox" name="maintenance_mode" value="1" <?=file_exists(BASE_PATH.'/.maintenance')?'checked':''?>><span class="toggle-slider"></span></span>
        <label class="toggle-label" style="font-size:13px;font-weight:600;">Bật chế độ bảo trì</label>
      </div>
      <div style="margin-top:8px;font-size:12px;color:var(--warning);"><i class="fas fa-exclamation-triangle" style="margin-right:4px;"></i>Khi bật, người dùng sẽ thấy trang bảo trì thay vì nội dung.</div>
    </div>
    <div class="form-group"><label class="form-label">Thông báo bảo trì</label><textarea name="maintenance_message" class="form-control" rows="3" placeholder="Trang đang được bảo trì, vui lòng quay lại sau."><?=e($s['maintenance_message']??'')?></textarea></div>
    <div class="admin-card-header" style="margin-top:16px;"><div class="ach-icon"><i class="fas fa-mobile-alt"></i></div><h3>PWA Settings</h3></div>
    <div class="form-group" style="margin-top:12px;">
      <div class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" name="pwa_enabled" value="1" <?=($s['pwa_enabled']??'1')==='1'?'checked':''?>><span class="toggle-slider"></span></span><label class="toggle-label" style="font-size:13px;">Bật PWA (cài đặt ứng dụng)</label></div>
    </div>
    <div class="form-group"><label class="form-label">PWA Theme Color</label><div class="color-picker-wrap"><input type="color" name="pwa_theme_color" value="<?=e($s['pwa_theme_color']??'#6366F1')?>"><input type="text" class="color-hex" value="<?=strtoupper($s['pwa_theme_color']??'#6366F1')?>" maxlength="7"></div></div>
  </div>
</div>

</div>

<div class="admin-card" style="margin-top:20px;">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-broom"></i></div><h3>Cache & Dọn Dẹp</h3></div>
  <div class="admin-card-body">
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <button type="button" class="btn btn-secondary" onclick="clearOldVisits()"><i class="fas fa-chart-bar"></i> Xóa visits cũ</button>
      <button type="button" class="btn btn-secondary" onclick="clearSessions()"><i class="fas fa-user-clock"></i> Xóa sessions cũ</button>
      <button type="button" class="btn btn-warning" onclick="if(confirm('Tạo lại sitemap?')) { AdminJS.toast('info','Đang tạo...',''); }" style="color:#fff;"><i class="fas fa-sitemap"></i> Tái tạo sitemap</button>
    </div>
  </div>
</div>

<div style="margin-top:16px;display:flex;justify-content:flex-end;">
  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu System Settings</button>
</div>
</form>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
document.getElementById('sysForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('sysForm',location.href);});
async function clearOldVisits(){const res=await AdminJS.ajax({url:location.href,data:{action:'clear_visits'}});if(res.success)AdminJS.toast('success','Đã xóa visits cũ!','');}
async function clearSessions(){const res=await AdminJS.ajax({url:location.href,data:{action:'clear_sessions'}});if(res.success)AdminJS.toast('success','Đã xóa sessions!','');}
</script></body></html>
