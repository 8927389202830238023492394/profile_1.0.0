<?php
// ADMIN — Announcement Bar
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'Announcement Bar';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    updateSetting('announcement_enabled', $_POST['announcement_enabled'] ?? '0');
    updateSetting('announcement_text', trim($_POST['announcement_text'] ?? ''));
    updateSetting('announcement_color', trim($_POST['announcement_color'] ?? '#6366F1'));
    updateSetting('announcement_expiry', trim($_POST['announcement_expiry'] ?? ''));
    echo json_encode(['success'=>true,'message'=>'Thông báo đã được lưu!']);
    exit;
}
$s = getAllSettings();
include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">
<div class="grid-2" style="gap:20px;">
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-bullhorn"></i></div><h3>Cấu Hình Thông Báo</h3></div>
  <div class="admin-card-body">
    <form id="annForm"><<?= csrfField() ?>
      <div class="form-group">
        <div class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" name="announcement_enabled" id="annEnabled" value="1" <?=($s['announcement_enabled']??'0')==='1'?'checked':''?> onchange="updatePreview()"><span class="toggle-slider"></span></span><label class="toggle-label" for="annEnabled">Bật thông báo</label></div>
      </div>
      <div class="form-group"><label class="form-label">Nội dung thông báo</label><input type="text" name="announcement_text" class="form-control" value="<?=e($s['announcement_text']??'')?>" placeholder="🎉 Thông báo của bạn..." oninput="updatePreview()"></div>
      <div class="form-group"><label class="form-label">Màu nền</label><div class="color-picker-wrap"><input type="color" name="announcement_color" value="<?=e($s['announcement_color']??'#6366F1')?>" oninput="updatePreview()"><input type="text" class="color-hex" value="<?=strtoupper($s['announcement_color']??'#6366F1')?>" maxlength="7"></div></div>
      <div class="form-group"><label class="form-label">Hết hạn lúc <span class="label-hint">(để trống = không hết hạn)</span></label><input type="datetime-local" name="announcement_expiry" class="form-control" value="<?=e($s['announcement_expiry']??'')?>"></div>
      <button type="submit" class="btn btn-primary w-100" style="justify-content:center;margin-top:8px;"><i class="fas fa-save"></i> Lưu Thông Báo</button>
    </form>
  </div>
</div>
<div class="admin-card" style="position:sticky;top:calc(var(--topbar-h)+16px);">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-eye"></i></div><h3>Xem Trước</h3></div>
  <div class="admin-card-body" style="padding:0;">
    <div id="annPreview" style="padding:12px 20px;text-align:center;font-size:13px;font-weight:500;background:<?=e($s['announcement_color']??'#6366F1')?>;color:#fff;border-radius:0 0 var(--radius) var(--radius);">
      <i class="fas fa-bell" style="margin-right:8px;"></i><?=e($s['announcement_text']??'Nội dung thông báo sẽ hiển thị ở đây')?>
    </div>
    <div style="padding:20px;text-align:center;color:var(--text-3);font-size:13px;"><i class="fas fa-info-circle" style="color:var(--accent);margin-right:6px;"></i>Thông báo sẽ hiển thị ở đầu trang frontend</div>
  </div>
</div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function updatePreview(){
  const text=document.querySelector('[name="announcement_text"]').value||'Nội dung thông báo';
  const color=document.querySelector('[name="announcement_color"]').value;
  const enabled=document.getElementById('annEnabled').checked;
  const preview=document.getElementById('annPreview');
  preview.style.background=color;
  preview.style.opacity=enabled?'1':'0.4';
  preview.innerHTML=`<i class="fas fa-bell" style="margin-right:8px;"></i>${text.replace(/</g,'&lt;')}`;
}
document.getElementById('annForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('annForm',location.href);});
</script></body></html>
