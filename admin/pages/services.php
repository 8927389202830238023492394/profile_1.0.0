<?php
// ADMIN — Services Management
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'My Services';
$table = 'services';
$fields = ['icon_class','name','description','price','link','sort_order','status'];
AdminCRUD::handle($table, $fields);
$rows = db()->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC")->fetchAll();
include dirname(__DIR__).'/includes/head.php';
include dirname(__DIR__).'/includes/sidebar.php';
include dirname(__DIR__).'/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">
<div id="itemModal" class="modal-overlay"><div class="modal-box modal-lg">
  <div class="modal-header"><div class="mh-icon"><i class="fas fa-briefcase"></i></div><h4 id="modalTitle">Thêm Dịch Vụ</h4><button class="modal-close" data-modal-close><i class="fas fa-times"></i></button></div>
  <form id="itemForm"><?= csrfField() ?><input type="hidden" name="action" id="formAction" value="create"><input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group"><label class="form-label">Icon FA</label><input type="text" name="icon_class" class="form-control" placeholder="fas fa-code"></div>
        <div class="form-group"><label class="form-label">Tên dịch vụ <span class="required">*</span></label><input type="text" name="name" class="form-control" required></div>
      </div>
      <div class="form-group"><label class="form-label">Mô tả</label><textarea name="description" class="form-control" rows="3"></textarea></div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Giá tham khảo</label><input type="text" name="price" class="form-control" placeholder="Từ 2.000.000đ"></div>
        <div class="form-group"><label class="form-label">Link đặt hàng</label><input type="url" name="link" class="form-control" placeholder="https://..."></div>
      </div>
      <div class="form-row">
        <div class="form-group"><label class="form-label">Thứ tự</label><input type="number" name="sort_order" class="form-control" value="0"></div>
        <div class="form-group"><label class="form-label">Trạng thái</label><select name="status" class="form-control"><option value="1">Hiển thị</option><option value="0">Ẩn</option></select></div>
      </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-secondary" data-modal-close>Hủy</button><button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu</button></div>
  </form>
</div></div>
<div class="admin-card">
  <div class="table-toolbar"><div class="toolbar-right"><button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Dịch Vụ</button></div></div>
  <div class="card-grid">
    <?php if(empty($rows)):?>
      <div class="empty-state" style="grid-column: 1/-1; padding:40px;"><i class="fas fa-briefcase"></i><h4>Chưa có dịch vụ nào</h4></div>
    <?php else:foreach($rows as $r):?>
      <div class="cms-card">
        <div class="cms-card-thumb" style="height: 100px;">
          <i class="<?=e($r['icon_class'])?>" style="color:var(--accent);"></i>
          <div class="cms-card-status">
            <label class="toggle-wrap"><span class="toggle-switch"><input type="checkbox" class="status-toggle" data-table="services" data-id="<?=$r['id']?>" data-field="status" <?=$r['status']?'checked':''?>><span class="toggle-slider"></span></span></label>
          </div>
        </div>
        <div class="cms-card-body">
          <div class="cms-card-title"><?=e($r['name'])?></div>
          <div class="cms-card-meta"><?=e(mb_strimwidth($r['description'],0,80,'...'))?></div>
          <div style="font-size:14px;color:var(--success);font-weight:600;margin-bottom:10px;"><?=e($r['price'])?></div>
          <div class="cms-card-actions">
            <button class="btn btn-outline" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)"><i class="fas fa-edit"></i> Edit</button>
            <button class="btn btn-outline" style="color:var(--danger);border-color:rgba(239,68,68,0.2);" onclick="deleteItem('services',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i> Delete</button>
          </div>
        </div>
      </div>
    <?php endforeach;endif;?>
  </div>
</div>
</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Dịch Vụ';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Dịch Vụ';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script></body></html>
