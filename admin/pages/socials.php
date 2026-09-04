<?php
// ADMIN — Socials Management
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'Social Network';
$table = 'socials';
$fields = ['platform','icon_class','color','username','link','sort_order','status'];
AdminCRUD::handle($table, $fields);

$search = trim($_GET['search']??'');
$page = max(1,(int)($_GET['page']??1));
$perPage = 15;
$where = '1=1'; $params = [];
if ($search){$where.=" AND (platform LIKE ? OR username LIKE ?)"; $params=["%$search%","%$search%"];}
$countStmt=db()->prepare("SELECT COUNT(*) FROM socials WHERE $where");$countStmt->execute($params);
$total=(int)$countStmt->fetchColumn();$lastPage=max(1,(int)ceil($total/$perPage));
$stmt=db()->prepare("SELECT * FROM socials WHERE $where ORDER BY sort_order ASC, id ASC LIMIT $perPage OFFSET ".(($page-1)*$perPage));
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
  <a href="socials.php" class="cms-tab active"><i class="fas fa-share-alt"></i> Social Network</a>
  <a href="skills.php" class="cms-tab"><i class="fas fa-code"></i> Skills & Tech</a>
  <a href="achievements.php" class="cms-tab"><i class="fas fa-trophy"></i> Achievements</a>
</div>
<div id="itemModal" class="modal-overlay"><div class="modal-box">
  <div class="modal-header"><div class="mh-icon"><i class="fas fa-share-alt"></i></div><h4 id="modalTitle">Thêm Social</h4><button class="modal-close" data-modal-close><i class="fas fa-times"></i></button></div>
  <form id="itemForm"><<?= csrfField() ?><input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Tên nền tảng <span class="required">*</span></label><input type="text" name="platform" class="form-control" placeholder="Facebook, Telegram..." required></div>
        <div class="form-group"><label class="form-label">Username</label><input type="text" name="username" class="form-control" placeholder="@username"></div>
      </div>
      <div class="form-group"><label class="form-label">Font Awesome Icon <span class="label-hint">vd: fab fa-facebook-f</span></label><input type="text" name="icon_class" class="form-control" placeholder="fab fa-facebook-f"></div>
      <div class="form-group"><label class="form-label">Link</label><input type="url" name="link" class="form-control" placeholder="https://..."></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Màu sắc</label><div class="color-picker-wrap"><input type="color" name="color" value="#6366F1"><input type="text" class="color-hex" value="#6366F1" maxlength="7"></div></div>
        <div class="form-group"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="0"></div>
      </div>
      <div class="form-group"><label class="form-label">Trạng thái</label><select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Hủy</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
  </form>
</div></div>

<div class="admin-card">
  <div class="table-toolbar">
    <div class="toolbar-left"><form method="GET" style="display:contents;"><div class="admin-search"><i class="fas fa-search"></i><input type="text" name="search" placeholder="Tìm platform, username..." value="<?=e($search)?>"></div></form></div>
    <div class="toolbar-right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Social</button></div>
  </div>
  <div class="modern-table-wrap"><table class="modern-table">
    <thead><tr><th>Icon</th><th>Platform</th><th>Username</th><th>Link</th><th>Màu</th><th>Trạng thái</th><th style="text-align:right;">Hành động</th></tr></thead>
    <tbody>
    <?php if(empty($rows)):?><tr><td colspan="7"><div class="empty-state"><i class="fas fa-share-alt"></i><h4>Chưa có social nào</h4></div></td></tr>
    <?php else:foreach($rows as $r):?>
    <tr>
      <td><div style="width:36px;height:36px;background:<?=e($r['color'])?>22;border:1px solid <?=e($r['color'])?>44;border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="<?=e($r['icon_class'])?>" style="color:<?=e($r['color'])?>"></i></div></td>
      <td class="td-primary"><?=e($r['platform'])?></td>
      <td class="td-mono"><?=e($r['username'])?></td>
      <td><a href="<?=e($r['link'])?>" target="_blank" style="color:var(--accent);font-size:12px;"><?=e(substr($r['link'],0,28)).'...'?></a></td>
      <td><div style="width:24px;height:24px;border-radius:50%;background:<?=e($r['color'])?>;border:2px solid var(--border-bright);"></div></td>
      <td><label class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" class="status-toggle" data-table="socials" data-id="<?=$r['id']?>" data-field="status" <?=$r['status']?'checked':''?>><span class="toggle-slider"></span></span></label></td>
      <td><div class="td-actions"><button class="btn btn-xs btn-info btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button><button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('socials',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button></div></td>
    </tr>
    <?php endforeach;endif;?>
    </tbody>
  </table></div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Social';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Social';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script></body></html>
