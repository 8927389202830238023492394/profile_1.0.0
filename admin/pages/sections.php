<?php
// ============================================================
// ADMIN — Section Builder (Drag & Drop)
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Section Builder';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'reorder') {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
        foreach ($ids as $order => $id) {
            db()->prepare("UPDATE page_sections SET sort_order=? WHERE id=?")->execute([$order+1, $id]);
        }
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_POST['action'] === 'edit_section') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        db()->prepare("UPDATE page_sections SET title=?, description=? WHERE id=?")->execute([$title, $description, $id]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_POST['action'] === 'toggle_visible') {
        $id = (int)($_POST['id'] ?? 0);
        $val = (int)($_POST['value'] ?? 0);
        db()->prepare("UPDATE page_sections SET visible=? WHERE id=?")->execute([$val, $id]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

$sections = db()->query("SELECT * FROM page_sections ORDER BY sort_order ASC")->fetchAll();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<div class="grid-2" style="gap:24px;">

<div class="admin-card">
  <div class="admin-card-header">
    <div class="ach-icon"><i class="fas fa-layer-group"></i></div>
    <h3>Kéo Thả Sắp Xếp Khối</h3>
    <span class="ach-sub">Thứ tự tự động lưu</span>
  </div>
  <div class="admin-card-body">
    <div class="section-list" data-sortable data-sortable-table="page_sections" data-sortable-url="<?=ADMIN_URL?>pages/sections.php" id="sectionList">
      <?php foreach ($sections as $sec): ?>
      <div class="section-list-item" draggable="true" data-id="<?=$sec['id']?>">
        <div class="sli-handle"><i class="fas fa-grip-vertical"></i></div>
        <div class="sli-icon"><i class="<?=e($sec['icon_class'])?>"></i></div>
        <div class="sli-name"><?=e($sec['section_name'])?></div>
        <div class="sli-order">#<?=$sec['sort_order']?></div>
        <button class="btn btn-xs btn-outline btn-icon" onclick="editSection(<?=htmlspecialchars(json_encode($sec), ENT_QUOTES, 'UTF-8')?>)" style="margin-right:8px;"><i class="fas fa-edit"></i></button>
        <label class="toggle-wrap">
          <span class="toggle-switch">
            <input type="checkbox" onchange="toggleSection(<?=$sec['id']?>,this.checked?1:0)" <?=$sec['visible']?'checked':''?>>
            <span class="toggle-slider"></span>
          </span>
        </label>
      </div>
      <?php endforeach; ?>
    </div>
    <div style="margin-top:16px;padding:12px;background:rgba(99,102,241,0.05);border:1px solid rgba(99,102,241,0.15);border-radius:8px;font-size:12px;color:var(--text-3);">
      <i class="fas fa-info-circle" style="color:var(--accent);margin-right:6px;"></i>
      Kéo thả để thay đổi thứ tự. Toggle để bật/tắt hiển thị. Thay đổi được lưu ngay lập tức.
    </div>
  </div>
</div>

<!-- Preview -->
<div class="admin-card" style="position:sticky;top:calc(var(--topbar-h) + 16px);">
  <div class="admin-card-header">
    <div class="ach-icon"><i class="fas fa-eye"></i></div>
    <h3>Trạng Thái Hiện Tại</h3>
  </div>
  <div class="admin-card-body">
    <?php foreach ($sections as $i => $sec): ?>
    <div id="secStatus_<?=$sec['id']?>" style="display:flex;align-items:center;gap:12px;padding:12px;background:var(--card-2);border:1px solid var(--border);border-radius:8px;margin-bottom:8px;opacity:<?=$sec['visible']?'1':'0.4'?>;">
      <span style="font-size:12px;font-weight:700;color:var(--text-3);min-width:24px;text-align:center;"><?=$i+1?></span>
      <i class="<?=e($sec['icon_class'])?>" style="color:var(--accent);width:16px;text-align:center;"></i>
      <span style="flex:1;font-size:13px;font-weight:600;color:var(--text);"><?=e($sec['section_name'])?></span>
      <span class="status-badge <?=$sec['visible']?'active':'inactive'?>" id="secBadge_<?=$sec['id']?>"><?=$sec['visible']?'Hiện':'Ẩn'?></span>
    </div>
    <?php endforeach; ?>
    <a href="<?=BASE_URL?>" target="_blank" class="btn btn-outline w-100" style="margin-top:12px;justify-content:center;"><i class="fas fa-external-link-alt"></i> Xem Frontend</a>
  </div>
</div>

</div>
</div></div>

<!-- Edit Section Modal -->
<div id="editSectionModal" class="modal-overlay">
<div class="modal-box">
  <div class="modal-header">
    <div class="mh-icon"><i class="fas fa-edit"></i></div>
    <h4>Chỉnh sửa thông tin Block</h4>
    <button class="modal-close" data-modal-close><i class="fas fa-times"></i></button>
  </div>
  <form id="editSectionForm" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" value="edit_section">
    <input type="hidden" name="id" id="editSecId">
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">Tên hiển thị (Admin)</label>
        <input type="text" id="editSecName" class="form-control" readonly style="background:var(--bg);color:var(--text-3);">
      </div>
      <div class="form-group">
        <label class="form-label">Tiêu đề ngoài Trang Chủ (Title)</label>
        <input type="text" name="title" id="editSecTitle" class="form-control" placeholder="VD: Hệ Sinh Thái Sản Phẩm">
        <div class="label-hint">Hỗ trợ HTML (ví dụ: &lt;span class="grad"&gt;Nổi bật&lt;/span&gt;)</div>
      </div>
      <div class="form-group">
        <label class="form-label">Mô tả (Description)</label>
        <textarea name="description" id="editSecDesc" class="form-control" rows="3" placeholder="Các nền tảng..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button type="button" class="btn btn-outline" data-modal-close>Hủy</button>
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Lưu Thay Đổi</button>
    </div>
  </form>
</div>
</div>

<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function editSection(data) {
  document.getElementById('editSecId').value = data.id;
  document.getElementById('editSecName').value = data.section_name;
  document.getElementById('editSecTitle').value = data.title || '';
  document.getElementById('editSecDesc').value = data.description || '';
  AdminJS.openModal('editSectionModal');
}

document.getElementById('editSectionForm').addEventListener('submit', function(e) {
  e.preventDefault();
  AdminJS.submitForm('editSectionForm', location.href, () => {
    location.reload();
  });
});

async function toggleSection(id, val) {
  const el = document.querySelector(`[data-id="${id}"]`).parentElement;
  const status = document.getElementById('secStatus_' + id);
  const badge = document.getElementById('secBadge_' + id);
  const fd = new FormData();
  fd.append('action','toggle_visible');fd.append('id',id);fd.append('value',val);fd.append('_csrf_token',AdminJS.csrf);
  const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
  const data = await res.json();
  if (data.success) {
    if (status) status.style.opacity = val ? '1' : '0.4';
    if (badge) { badge.textContent = val ? 'Hiện' : 'Ẩn'; badge.className = 'status-badge ' + (val ? 'active' : 'inactive'); }
    AdminJS.toast('info', val ? 'Section đã hiện' : 'Section đã ẩn', '', 2000);
  }
}

// Custom sortable for section list with auto-save
document.addEventListener('DOMContentLoaded', () => {
  const list = document.getElementById('sectionList');
  let dragging = null;
  list.querySelectorAll('[draggable="true"]').forEach(item => {
    item.addEventListener('dragstart', e => { dragging=item; item.classList.add('dragging'); e.dataTransfer.effectAllowed='move'; });
    item.addEventListener('dragend', async () => {
      item.classList.remove('dragging');
      list.querySelectorAll('.drag-over').forEach(el=>el.classList.remove('drag-over'));
      // Save order
      const ids = [...list.querySelectorAll('[data-id]')].map(el=>el.dataset.id);
      const fd = new FormData();
      fd.append('action','reorder');fd.append('ids',ids.join(','));fd.append('_csrf_token',AdminJS.csrf);
      const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
      const data = await res.json();
      if(data.success) AdminJS.toast('info','Thứ tự đã lưu!','',1500);
      // Update numbers in preview
      ids.forEach((id,idx) => {
        const s = document.getElementById('secStatus_'+id);
        if(s) s.querySelector('span:first-child').textContent = idx+1;
      });
    });
    item.addEventListener('dragover', e => { e.preventDefault();item.classList.add('drag-over');const rect=item.getBoundingClientRect();if(e.clientY<rect.top+rect.height/2)list.insertBefore(dragging,item);else list.insertBefore(dragging,item.nextSibling); });
    item.addEventListener('dragleave', ()=>item.classList.remove('drag-over'));
  });
});
</script>
</body></html>
