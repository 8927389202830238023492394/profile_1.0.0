<?php
// ADMIN — Achievements Management
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'Achievements';
$table = 'achievements';
$fields = ['title','description','year','icon_class','color','sort_order','status'];
AdminCRUD::handle($table, $fields);

$search = trim($_GET['search']??'');
$page = max(1,(int)($_GET['page']??1));
$perPage = 15;
$where = '1=1'; $params = [];
if ($search){$where.=" AND title LIKE ?"; $params=["%$search%"];}
$countStmt=db()->prepare("SELECT COUNT(*) FROM achievements WHERE $where");$countStmt->execute($params);
$total=(int)$countStmt->fetchColumn();$lastPage=max(1,(int)ceil($total/$perPage));
$stmt=db()->prepare("SELECT * FROM achievements WHERE $where ORDER BY sort_order ASC, id ASC LIMIT $perPage OFFSET ".(($page-1)*$perPage));
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
  <a href="skills.php" class="cms-tab"><i class="fas fa-code"></i> Skills & Tech</a>
  <a href="achievements.php" class="cms-tab active"><i class="fas fa-trophy"></i> Achievements</a>
</div>
<div id="itemModal" class="modal-overlay"><div class="modal-box">
  <div class="modal-header"><div class="mh-icon"><i class="fas fa-trophy"></i></div><h4 id="modalTitle">Thêm Achievement</h4><button class="modal-close" data-modal-close><i class="fas fa-times"></i></button></div>
  <form id="itemForm"><?= csrfField() ?><input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Icon FA <span class="label-hint">vd: fas fa-trophy</span></label><input type="text" name="icon_class" class="form-control" placeholder="fas fa-trophy"></div>
      <div class="form-group"><label class="form-label">Tiêu đề <span class="required">*</span></label><input type="text" name="title" class="form-control" required placeholder="500+ Website Hoàn Thành"></div>
      <div class="form-group"><label class="form-label">Mô tả</label><textarea name="content" class="form-control" rows="2" placeholder="Chi tiết thêm..."></textarea></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="0"></div>
        <div class="form-group"><label class="form-label">Trạng thái</label><select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Hủy</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
  </form>
</div></div>
<div class="admin-card">
  <div class="table-toolbar"><div class="toolbar-right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Achievement</button></div></div>
  <div class="modern-table-wrap"><table class="modern-table">
    <thead><tr><th>Icon</th><th>Tiêu đề</th><th>Mô tả</th><th>Thứ tự</th><th>Trạng thái</th><th style="text-align:right;">Hành động</th></tr></thead>
    <tbody>
    <?php if(empty($rows)):?><tr><td colspan="6"><div class="empty-state"><i class="fas fa-trophy"></i><h4>Chưa có achievement</h4></div></td></tr>
    <?php else:foreach($rows as $r):?>
    <tr>
      <td><div style="width:36px;height:36px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="<?=e($r['icon_class'])?>" style="color:var(--accent);"></i></div></td>
      <td class="td-primary"><?=e($r['title'])?></td>
      <td style="font-size:13px;color:var(--text-3);"><?=e(mb_strimwidth($r['content'],0,50,'...'))?></td>
      <td style="color:var(--text-3);"><?=$r['sort_order']?></td>
      <td><label class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" class="status-toggle" data-table="achievements" data-id="<?=$r['id']?>" data-field="status" <?=$r['status']?'checked':''?>><span class="toggle-slider"></span></span></label></td>
      <td><div class="td-actions"><button class="btn btn-xs btn-info btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button><button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('achievements',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button></div></td>
    </tr>
    <?php endforeach;endif;?>
    </tbody>
  </table></div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Achievement';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Achievement';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script></body></html>
