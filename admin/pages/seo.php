<?php
// ADMIN — SEO Settings
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'SEO Settings';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    if (!empty($_FILES['favicon_file']['name'])) {
        $res = uploadFile($_FILES['favicon_file'], 'general');
        if ($res['success']) $_POST['favicon'] = $res['url'];
    }
    if (!empty($_FILES['og_image_file']['name'])) {
        $res = uploadFile($_FILES['og_image_file'], 'general');
        if ($res['success']) $_POST['og_image'] = $res['url'];
    }

    $fields = ['meta_title','meta_description','meta_keywords','og_image','canonical_url','twitter_card','twitter_site','favicon','robots_txt'];
    foreach ($fields as $f) { if (isset($_POST[$f])) updateSetting($f, trim($_POST[$f])); }
    // Sitemap Settings
    if (!empty($_POST['sitemap_enabled'])) {
        updateSetting('sitemap_enabled', '1');
    } else {
        updateSetting('sitemap_enabled', '0');
    }
    // Update robots.txt
    if (isset($_POST['robots_txt'])) {
        file_put_contents(BASE_PATH . '/robots.txt', trim($_POST['robots_txt']));
    }
    echo json_encode(['success' => true, 'message' => 'SEO settings đã được lưu!']);
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
  <a href="seo.php" class="cms-tab active"><i class="fas fa-search"></i> SEO</a>
  <a href="scripts.php" class="cms-tab"><i class="fas fa-code"></i> Scripts</a>
  <a href="password.php" class="cms-tab"><i class="fas fa-lock"></i> Security</a>
  <a href="backup.php" class="cms-tab"><i class="fas fa-database"></i> Backup</a>
</div>
<?php
$totalUrls = 5; // Homepage, Services, Websites, About, Contact
try {
    $totalUrls += (int)db()->query("SELECT COUNT(*) FROM services WHERE status=1")->fetchColumn();
    $totalUrls += (int)db()->query("SELECT COUNT(*) FROM websites WHERE status=1")->fetchColumn();
} catch (Exception $e) {}
?>
<div class="admin-card" style="margin-bottom: 20px;">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-sitemap"></i></div><h3>SEO Manager</h3></div>
  <div class="admin-card-body">
    <div class="grid-2" style="gap:20px;">
        <div style="background:var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="color:var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Sitemap URL</div>
            <div style="font-weight: bold; font-size: 14px;"><a href="<?=BASE_URL?>sitemap.xml" target="_blank" style="color:var(--accent-color);"><?=BASE_URL?>sitemap.xml</a></div>
        </div>
        <div style="background:var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="color:var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Robots URL</div>
            <div style="font-weight: bold; font-size: 14px;"><a href="<?=BASE_URL?>robots.txt" target="_blank" style="color:var(--accent-color);"><?=BASE_URL?>robots.txt</a></div>
        </div>
        <div style="background:var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="color:var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Tổng số URL đã Index</div>
            <div style="font-weight: bold; font-size: 18px; color: #10B981;"><?= $totalUrls ?> URLs</div>
        </div>
        <div style="background:var(--bg-color); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color);">
            <div style="color:var(--text-secondary); font-size: 13px; margin-bottom: 5px;">Cập nhật Sitemap gần nhất</div>
            <div style="font-weight: bold; font-size: 18px;"><?= date('Y-m-d') ?> (Auto)</div>
        </div>
    </div>
  </div>
</div>

<form id="seoForm"><<?= csrfField() ?>
<div class="grid-2" style="gap:20px;">
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-search"></i></div><h3>Meta Tags</h3></div>
  <div class="admin-card-body">
    <div class="form-group"><label class="form-label">Meta Title <span class="label-hint">(50-60 ký tự)</span></label><input type="text" name="meta_title" class="form-control" value="<?=e($s['meta_title']??'')?>" maxlength="70"></div>
    <div class="form-group"><label class="form-label">Meta Description <span class="label-hint">(150-160 ký tự)</span></label><textarea name="meta_description" class="form-control" rows="3" maxlength="200"><?=e($s['meta_description']??'')?></textarea></div>
    <div class="form-group"><label class="form-label">Meta Keywords <span class="label-hint">(phân cách bằng dấu phẩy)</span></label><input type="text" name="meta_keywords" class="form-control" value="<?=e($s['meta_keywords']??'')?>"></div>
    <div class="form-group"><label class="form-label">Canonical URL</label><input type="url" name="canonical_url" class="form-control" value="<?=e($s['canonical_url']??'')?>"></div>
    <div class="form-group">
      <label class="form-label">Favicon URL</label>
      <div style="display: flex; gap: 10px;">
        <input type="text" name="favicon" class="form-control" value="<?=e($s['favicon']??'')?>" placeholder="Hoặc tải lên..." style="flex:1;">
        <input type="file" name="favicon_file" class="form-control" accept="image/*" style="flex:1;">
      </div>
    </div>
  </div>
</div>
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-share-alt"></i></div><h3>Social & Open Graph</h3></div>
  <div class="admin-card-body">
    <div class="form-group">
      <label class="form-label">OG Image URL</label>
      <div style="display: flex; gap: 10px;">
        <input type="url" name="og_image" class="form-control" value="<?=e($s['og_image']??'')?>" placeholder="Hoặc tải lên..." style="flex:1;">
        <input type="file" name="og_image_file" class="form-control" accept="image/*" style="flex:1;">
      </div>
    </div>
    <div class="form-group"><label class="form-label">Twitter Card</label><select name="twitter_card" class="form-control"><option value="summary_large_image" <?=$s['twitter_card']==='summary_large_image'?'selected':''?>>summary_large_image</option><option value="summary" <?=$s['twitter_card']==='summary'?'selected':''?>>summary</option></select></div>
    <div class="form-group"><label class="form-label">Twitter @handle</label><input type="text" name="twitter_site" class="form-control" value="<?=e($s['twitter_site']??'')?>" placeholder="@username"></div>
    <div class="form-group">
      <div class="toggle-wrap">
        <span class="toggle-switch"><input type="checkbox" name="sitemap_enabled" id="sitemapToggle" value="1" <?=($s['sitemap_enabled']??'1')==='1'?'checked':''?>><span class="toggle-slider"></span></span>
        <label class="toggle-label" for="sitemapToggle">Tự động tạo sitemap.xml</label>
      </div>
    </div>
  </div>
</div>
</div>
<div class="admin-card" style="margin-top:20px;">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-robot"></i></div><h3>Robots.txt</h3></div>
  <div class="admin-card-body">
    <div class="form-group"><textarea name="robots_txt" class="form-control font-mono" rows="6"><?=e($s['robots_txt']??"User-agent: *\nAllow: /\nDisallow: /admin/")?></textarea></div>
  </div>
</div>
<div style="margin-top:16px;display:flex;justify-content:flex-end;gap:10px;">
  <a href="<?=BASE_URL?>sitemap.xml" target="_blank" class="btn btn-secondary"><i class="fas fa-sitemap"></i> Xem Sitemap</a>
  <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu SEO Settings</button>
</div>
</form>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
document.getElementById('seoForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('seoForm',location.href);});
</script></body></html>
