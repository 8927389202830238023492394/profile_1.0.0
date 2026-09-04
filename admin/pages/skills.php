<?php
// ============================================================
// ADMIN — Skills Management
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Skills & Tech';
$table = 'skills';
$fields = ['name','icon_class','color','percentage','sort_order','status'];

AdminCRUD::handle($table, $fields);

$search = trim($_GET['search']??'');
$page = max(1,(int)($_GET['page']??1));
$perPage = 15;
$where = '1=1'; $params = [];
if ($search){$where.=" AND name LIKE ?"; $params=["%$search%"];}
$countStmt=db()->prepare("SELECT COUNT(*) FROM skills WHERE $where");$countStmt->execute($params);
$total=(int)$countStmt->fetchColumn();$lastPage=max(1,(int)ceil($total/$perPage));
$stmt=db()->prepare("SELECT * FROM skills WHERE $where ORDER BY sort_order ASC, id ASC LIMIT $perPage OFFSET ".(($page-1)*$perPage));
$stmt->execute($params);$rows=$stmt->fetchAll();

include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<!-- ── V2: Page Header ──────────────────────────── -->
<div style="margin-bottom: 24px;">
  <h1 style="font-size: 24px; font-weight: 700;">Personal Profile</h1>
  <p style="color: var(--text-2); font-size: 14px;">Quản lý thông tin cá nhân, mạng xã hội, kỹ năng và thành tựu.</p>
</div>

<!-- ── V2: Tabs ──────────────────────────── -->
<div class="cms-tabs">
  <a href="profile.php" class="cms-tab"><i class="fas fa-user"></i> Basic Info</a>
  <a href="socials.php" class="cms-tab"><i class="fas fa-share-alt"></i> Social Network</a>
  <a href="skills.php" class="cms-tab active"><i class="fas fa-code"></i> Skills & Tech</a>
  <a href="achievements.php" class="cms-tab"><i class="fas fa-trophy"></i> Achievements</a>
</div>

<!-- Modal -->
<div id="itemModal" class="modal-overlay">
<div class="modal-box">
  <div class="modal-header">
    <div class="mh-icon"><i class="fas fa-code"></i></div>
    <h4 id="modalTitle">Thêm Skill</h4>
    <button class="modal-close" data-modal-close><i class="fas fa-times"></i></button>
  </div>
  <form id="itemForm">
    <?= csrfField() ?>
    <input type="hidden" name="action" id="formAction" value="create">
    <input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Tên công nghệ <span class="required">*</span></label>
        <input type="text" name="name" class="form-control" placeholder="PHP, Laravel, Docker..." required>
      </div>
      <div class="form-group">
        <label class="form-label">Mức độ thành thạo</label>
        <div class="range-wrap">
          <input type="range" name="level" min="0" max="100" value="80" oninput="document.getElementById('levelVal').textContent=this.value+'%'">
          <span class="range-val" id="levelVal">80%</span>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Thứ tự</label>
          <input type="number" name="sort_order" class="form-control" value="0" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" data-modal-close>Hủy</button>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button>
    </div>
  </form>
</div>
</div>

<div class="admin-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <form method="GET" style="display:contents;">
        <div class="admin-search"><i class="fas fa-search"></i><input type="text" name="search" placeholder="Tìm skill..." value="<?=e($search)?>"></div>
      </form>
    </div>
    <div class="toolbar-right">
      <span style="font-size:13px;color:var(--text-3);"><?=$total?> skills</span>
      <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Skill</button>
    </div>
  </div>

  <div class="modern-table-wrap">
    <table class="modern-table">
      <thead><tr><th><input type="checkbox" id="selectAll" style="accent-color:var(--accent);width:15px;height:15px;"></th><th>Tên</th><th>Mức độ</th><th>Thứ tự</th><th>Trạng thái</th><th style="text-align:right;">Hành động</th></tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
      <tr><td colspan="6"><div class="empty-state"><i class="fas fa-code"></i><h4>Chưa có skill nào</h4></div></td></tr>
      <?php else: foreach ($rows as $r): ?>
      <tr>
        <td class="td-check"><input type="checkbox" class="row-check" value="<?=$r['id']?>"></td>
        <td class="td-primary"><?=e($r['name'])?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="flex:1;height:6px;background:var(--border-bright);border-radius:3px;overflow:hidden;max-width:140px;">
              <div style="width:<?=$r['level']?>%;height:100%;background:linear-gradient(90deg,var(--accent),var(--accent-2));border-radius:3px;"></div>
            </div>
            <span style="font-family:var(--font-mono);font-size:12px;color:var(--accent);"><?=$r['level']?>%</span>
          </div>
        </td>
        <td style="color:var(--text-3);"><?=$r['sort_order']?></td>
        <td>
          <label class="toggle-wrap"><span class="toggle-switch">
            <input type="checkbox" class="status-toggle" data-table="<?=$table?>" data-id="<?=$r['id']?>" data-field="status" <?=$r['status']?'checked':''?>>
            <span class="toggle-slider"></span>
          </span></label>
        </td>
        <td><div class="td-actions">
          <button class="btn btn-xs btn-info btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button>
          <button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('<?=$table?>',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button>
        </div></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal() {
  document.getElementById('modalTitle').textContent='Thêm Skill';
  document.getElementById('formAction').value='create';
  document.getElementById('formId').value='';
  document.getElementById('itemForm').reset();
  document.getElementById('levelVal').textContent='80%';
  AdminJS.openModal('itemModal');
}
function openEditModal(data) {
  document.getElementById('modalTitle').textContent='Sửa Skill';
  document.getElementById('formAction').value='update';
  document.getElementById('formId').value=data.id;
  AdminJS.fillForm('itemForm',data);
  document.getElementById('levelVal').textContent=data.level+'%';
  AdminJS.openModal('itemModal');
}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script>
</body></html>
