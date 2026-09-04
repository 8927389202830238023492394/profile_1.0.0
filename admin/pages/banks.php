<?php
// ADMIN — Banks / Payment Methods
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'Payment Methods';
$table = 'banks';
$fields = ['bank_name','bank_short','account_holder','account_number','branch','description','status'];
AdminCRUD::handle($table, $fields);
$rows = db()->query("SELECT * FROM banks ORDER BY id ASC")->fetchAll();
include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">
<div id="itemModal" class="modal-overlay"><div class="modal-box modal-lg">
  <div class="modal-header"><div class="mh-icon"><i class="fas fa-credit-card"></i></div><h4 id="modalTitle">Thêm Ngân Hàng</h4><button class="modal-close" data-modal-close><i class="fas fa-times"></i></button></div>
  <form id="itemForm"><?= csrfField() ?><input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Tên ngân hàng <span class="required">*</span></label><input type="text" name="bank_name" class="form-control" placeholder="Asia Commercial Bank" required></div>
        <div class="form-group"><label class="form-label">Tên viết tắt</label><input type="text" name="bank_short" class="form-control" placeholder="ACB" maxlength="20"></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Chủ tài khoản <span class="required">*</span></label><input type="text" name="account_holder" class="form-control" placeholder="NGUYEN VAN ADMIN" required></div>
        <div class="form-group"><label class="form-label">Số tài khoản <span class="required">*</span></label><input type="text" name="account_number" class="form-control" placeholder="1234567890" required></div>
      </div>
      <div class="form-group"><label class="form-label">Chi nhánh</label><input type="text" name="branch" class="form-control" placeholder="CN TP.HCM"></div>
      <div class="form-group"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="2" placeholder="Chuyển khoản 24/7..."></textarea></div>
      <div class="form-group"><label class="form-label">Trạng thái</label><select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Hủy</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
  </form>
</div></div>
<div class="admin-card">
  <div class="table-toolbar"><div class="toolbar-right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Ngân Hàng</button></div></div>
  <div class="modern-table-wrap"><table class="modern-table">
    <thead><tr><th>Ngân hàng</th><th>Chủ TK</th><th>Số tài khoản</th><th>Chi nhánh</th><th>Trạng thái</th><th style="text-align:right;">Hành động</th></tr></thead>
    <tbody>
    <?php if(empty($rows)):?><tr><td colspan="6"><div class="empty-state"><i class="fas fa-credit-card"></i><h4>Chưa có ngân hàng nào</h4></div></td></tr>
    <?php else:foreach($rows as $r):?>
    <tr>
      <td>
        <div style="display:flex;align-items:center;gap:10px;">
          <div style="width:44px;height:26px;background:linear-gradient(135deg,var(--accent),var(--accent-2));border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:900;color:#fff;letter-spacing:0.02em;"><?=e($r['bank_short'])?></div>
          <span class="td-primary"><?=e($r['bank_name'])?></span>
        </div>
      </td>
      <td style="font-family:var(--font-mono);font-size:12px;"><?=e($r['account_holder'])?></td>
      <td><span style="font-family:var(--font-mono);font-size:13px;color:var(--accent);font-weight:700;"><?=e($r['account_number'])?></span></td>
      <td style="color:var(--text-3);font-size:12px;"><?=e($r['branch'])?></td>
      <td><label class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" class="status-toggle" data-table="banks" data-id="<?=$r['id']?>" data-field="status" <?=$r['status']?'checked':''?>><span class="toggle-slider"></span></span></label></td>
      <td><div class="td-actions"><button class="btn btn-xs btn-info btn-icon" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i></button><button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('banks',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button></div></td>
    </tr>
    <?php endforeach;endif;?>
    </tbody>
  </table></div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Ngân Hàng';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Ngân Hàng';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script></body></html>
