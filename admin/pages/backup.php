<?php
// ADMIN — Backup Database
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'Database Backup';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'backup') {
        // Generate SQL dump
        $tables = db()->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $sql = "-- Cyber Dashboard Profile — Database Backup\n-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $sql .= "DROP TABLE IF EXISTS `$table`;\n";
            $createStmt = db()->query("SHOW CREATE TABLE `$table`")->fetch();
            $sql .= $createStmt['Create Table'] . ";\n\n";

            $rows = db()->query("SELECT * FROM `$table`")->fetchAll();
            if (!empty($rows)) {
                $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
                foreach ($rows as $row) {
                    $vals = array_map(fn($v) => $v === null ? 'NULL' : db()->quote($v), array_values($row));
                    $sql .= "INSERT INTO `$table` ($cols) VALUES (" . implode(', ', $vals) . ");\n";
                }
                $sql .= "\n";
            }
        }
        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_' . date('Ymd_His') . '.sql';
        $dir = BASE_PATH . '/admin/backups/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);
        file_put_contents($dir . $filename, $sql);

        echo json_encode(['success' => true, 'message' => 'Backup thành công!', 'filename' => $filename]);
        exit;
    }

    if ($_POST['action'] === 'download') {
        $filename = basename($_POST['filename'] ?? '');
        $dir = BASE_PATH . '/admin/backups/';
        // [SEC] Validate filename: only allow alphanumeric/underscore/dash .sql files
        if (!$filename || !preg_match('/^backup_[\d_]+\.sql$/', $filename) || !file_exists($dir . $filename)) {
            echo json_encode(['success' => false, 'message' => 'File không tồn tại hoặc không hợp lệ.']); exit;
        }
        // [SEC] Resolve real path and verify it's inside backups dir
        $realPath = realpath($dir . $filename);
        $realDir  = realpath($dir);
        if (!$realPath || strpos($realPath, $realDir) !== 0) {
            echo json_encode(['success' => false, 'message' => 'Truy cập không hợp lệ.']); exit;
        }
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($realPath));
        readfile($realPath);
        exit;
    }

    if ($_POST['action'] === 'delete_backup') {
        $filename = basename($_POST['filename'] ?? '');
        $dir = BASE_PATH . '/admin/backups/';
        // [SEC] Validate filename: only allow alphanumeric/underscore/dash .sql files
        if (!$filename || !preg_match('/^backup_[\d_]+\.sql$/', $filename)) {
            echo json_encode(['success' => false, 'message' => 'File không hợp lệ.']); exit;
        }
        $path = $dir . $filename;
        $realPath = realpath($path);
        $realDir  = realpath($dir);
        // [SEC] Ensure we only delete inside the backups directory
        if ($realPath && strpos($realPath, $realDir) === 0 && file_exists($realPath)) {
            @unlink($realPath);
        }
        echo json_encode(['success' => true]);
        exit;
    }

    echo json_encode(['success' => false]);
    exit;
}

// List backups
$backupDir = BASE_PATH . '/admin/backups/';
$backups = [];
if (is_dir($backupDir)) {
    $files = glob($backupDir . '*.sql');
    rsort($files);
    foreach ($files as $f) {
        $backups[] = ['filename' => basename($f), 'size' => filesize($f), 'modified' => filemtime($f)];
    }
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
  <a href="password.php" class="cms-tab"><i class="fas fa-lock"></i> Security</a>
  <a href="backup.php" class="cms-tab active"><i class="fas fa-database"></i> Backup</a>
</div>

<div class="grid-2" style="gap:20px;">
<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-database"></i></div><h3>Tạo Backup</h3></div>
  <div class="admin-card-body">
    <div style="text-align:center;padding:20px 0;">
      <i class="fas fa-database" style="font-size:48px;color:var(--accent);margin-bottom:16px;display:block;opacity:0.7;"></i>
      <div style="font-size:16px;font-weight:700;color:var(--text);margin-bottom:8px;">Database: <?=DB_NAME?></div>
      <div style="font-size:13px;color:var(--text-3);margin-bottom:24px;">Sao lưu toàn bộ dữ liệu thành file .sql</div>
      <button class="btn btn-primary" onclick="createBackup()" id="backupBtn" style="padding:14px 32px;">
        <i class="fas fa-download"></i> Tạo Backup Ngay
      </button>
    </div>
    <div style="margin-top:20px;padding:14px;background:rgba(245,158,11,0.08);border:1px solid rgba(245,158,11,0.2);border-radius:8px;font-size:12.5px;color:var(--warning);">
      <i class="fas fa-exclamation-triangle" style="margin-right:6px;"></i>
      Nên backup định kỳ trước khi thực hiện bất kỳ thay đổi lớn nào.
    </div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-history"></i></div><h3>Lịch Sử Backup</h3><span class="ach-sub"><?=count($backups)?> file</span></div>
  <?php if (empty($backups)): ?>
  <div class="empty-state" style="padding:40px;"><i class="fas fa-database"></i><h4>Chưa có backup nào</h4><p>Tạo backup đầu tiên của bạn</p></div>
  <?php else: ?>
  <div style="padding:12px;">
    <?php foreach ($backups as $b): ?>
    <div style="display:flex;align-items:center;gap:10px;padding:12px;background:var(--card-2);border:1px solid var(--border);border-radius:8px;margin-bottom:8px;">
      <i class="fas fa-file-code" style="color:var(--accent);font-size:16px;flex-shrink:0;"></i>
      <div style="flex:1;">
        <div style="font-family:var(--font-mono);font-size:12px;color:var(--text);font-weight:600;"><?=e($b['filename'])?></div>
        <div style="font-size:11px;color:var(--text-3);margin-top:2px;"><?=round($b['size']/1024,1)?> KB — <?=date('d/m/Y H:i',$b['modified'])?></div>
      </div>
      <form method="POST" style="display:inline;">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="download">
        <input type="hidden" name="filename" value="<?=e($b['filename'])?>">
        <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-download"></i></button>
      </form>
      <button class="btn btn-xs btn-danger" onclick="deleteBackup('<?=e($b['filename'])?>')"><i class="fas fa-trash"></i></button>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>
</div>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
async function createBackup() {
  const btn = document.getElementById('backupBtn');
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang tạo backup...';
  btn.disabled = true;
  const fd = new FormData();
  fd.append('action','backup');fd.append('_csrf_token',AdminJS.csrf);
  const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
  const data = await res.json();
  btn.innerHTML = '<i class="fas fa-download"></i> Tạo Backup Ngay';
  btn.disabled = false;
  if(data.success){AdminJS.toast('success','Backup thành công!',data.filename);setTimeout(()=>location.reload(),1500);}
  else AdminJS.toast('error','Lỗi!',data.message);
}
async function deleteBackup(filename) {
  if(!confirm('Xóa file backup này?')) return;
  const fd=new FormData();fd.append('action','delete_backup');fd.append('filename',filename);fd.append('_csrf_token',AdminJS.csrf);
  const res=await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
  const data=await res.json();
  if(data.success){AdminJS.toast('info','Đã xóa!','');setTimeout(()=>location.reload(),800);}
}
</script></body></html>
