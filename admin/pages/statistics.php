<?php
// ADMIN — Statistics
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'Statistics';
$table = 'statistics';
$fields = ['label','value','suffix','icon_class','sort_order'];
AdminCRUD::handle($table, $fields);
$rows = db()->query("SELECT * FROM statistics ORDER BY sort_order ASC")->fetchAll();
include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">
<div id="itemModal" class="modal-overlay"><div class="modal-box">
  <div class="modal-header"><div class="mh-icon"><i class="fas fa-chart-bar"></i></div><h4 id="modalTitle">Thêm Thống Kê</h4><button class="modal-close" data-modal-close><i class="fas fa-times"></i></button></div>
  <form id="itemForm"><?= csrfField() ?><input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-group"><label class="form-label">Nhãn <span class="required">*</span></label><input type="text" name="label" class="form-control" placeholder="Khách Hàng" required></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Giá trị <span class="label-hint">(vd: 10532 hoặc 2.5B)</span></label><input type="text" name="value" class="form-control" placeholder="10532" required></div>
        <div class="form-group"><label class="form-label">Hậu tố</label><input type="text" name="suffix" class="form-control" placeholder="+" maxlength="5"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Icon FA</label><input type="text" name="icon_class" class="form-control" placeholder="fas fa-users"></div>
        <div class="form-group"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="0"></div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Hủy</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
  </form>
</div></div>
<div class="admin-card">
  <div class="table-toolbar"><div class="toolbar-right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Thống Kê</button></div></div>
  <div class="modern-table-wrap"><table class="modern-table">
    <thead><tr><th>Icon</th><th>Nhãn</th><th>Giá trị</th><th>Thứ tự</th><th style="text-align:right;">Hành động</th></tr></thead>
    <tbody>
    <?php if(empty($rows)):?><tr><td colspan="5"><div class="empty-state"><i class="fas fa-chart-bar"></i><h4>Chưa có thống kê</h4></div></td></tr>
    <?php else:foreach($rows as $r):?>
    <tr>
      <td><div style="width:36px;height:36px;background:rgba(99,102,241,0.1);border-radius:8px;display:flex;align-items:center;justify-content:center;"><i class="<?=e($r['icon_class'])?>" style="color:var(--accent);"></i></div></td>
      <td class="td-primary"><?=e($r['label'])?></td>
      <td><span style="font-family:var(--font-mono);font-size:16px;font-weight:800;color:var(--accent);"><?=e($r['value'])?><?=e($r['suffix'])?></span></td>
      <td style="color:var(--text-3);"><?=$r['sort_order']?></td>
      <td><div class="td-actions"><button class="btn btn-xs btn-info btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button><button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('statistics',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button></div></td>
    </tr>
    <?php endforeach;endif;?>
    </tbody>
  </table></div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Thống Kê';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Thống Kê';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script></body></html>
