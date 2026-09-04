<?php
// ============================================================
// ADMIN — Websites Management
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Website Ecosystem';
$table = 'websites';
$fields = ['logo', 'name', 'domain', 'description', 'link', 'sort_order', 'status'];

// Handle file upload seamlessly before CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_FILES['logo_file']['name'])) {
    $result = uploadFile($_FILES['logo_file'], 'website_logos');
    if ($result['success']) {
        $_POST['logo'] = $result['url'];
    } else {
        echo json_encode(['success' => false, 'message' => $result['message']]);
        exit;
    }
}

// Handle AJAX CRUD
AdminCRUD::handle($table, $fields);

// Fetch with search & filter
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = '1=1';
$params = [];
if ($search) { $where .= " AND (name LIKE ? OR domain LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }
if ($status !== '') { $where .= " AND status = ?"; $params[] = (int)$status; }

$countStmt = db()->prepare("SELECT COUNT(*) FROM websites WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM websites WHERE $where ORDER BY sort_order ASC, id DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage));
$stmt->execute($params);
$rows = $stmt->fetchAll();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>

<div class="admin-main">
<div class="content-area">

<!-- Add/Edit Modal -->
<div id="itemModal" class="modal-overlay">
<div class="modal-box">
  <div class="modal-header">
    <div class="mh-icon"><i class="fas fa-globe"></i></div>
    <h4 id="modalTitle">Thêm Website</h4>
    <button class="modal-close" data-modal-close><i class="fas fa-times"></i></button>
  </div>
  <form id="itemForm" novalidate>
    <?= csrfField() ?>
    <input type="hidden" name="action" id="formAction" value="create">
    <input type="hidden" name="id" id="formId" value="">
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Tên Website <span class="required">*</span></label>
          <input type="text" name="name" class="form-control" placeholder="VCORE.VN" required>
        </div>
        <div class="form-group">
          <label class="form-label">Domain</label>
          <input type="text" name="domain" class="form-control" placeholder="vcore.vn">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Logo Website</label>
        <div style="display: flex; gap: 10px; align-items: center;">
          <input type="file" name="logo_file" class="form-control" accept="image/*" style="flex: 1;">
          <input type="hidden" name="logo" id="logoUrl">
        </div>
        <div style="font-size: 11px; color: var(--text-3); margin-top: 4px;">Để trống nếu không muốn đổi ảnh hiện tại.</div>
      </div>
      <div class="form-group">
        <label class="form-label">Mô tả</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Mô tả ngắn về website..."></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Link truy cập</label>
        <input type="url" name="link" class="form-control" placeholder="https://vcore.vn">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Thứ tự hiển thị</label>
          <input type="number" name="sort_order" class="form-control" value="0" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select name="status" class="form-control">
            <option value="1">Hiển thị</option>
            <option value="0">Ẩn</option>
          </select>
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

<!-- Main Card -->
<div class="admin-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <form method="GET" class="search-form" style="display:contents;">
        <div class="admin-search">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Tìm website, domain..." value="<?= e($search) ?>">
        </div>
        <select name="status" class="admin-filter" onchange="this.form.submit()">
          <option value="">Tất cả trạng thái</option>
          <option value="1" <?= $status==='1'?'selected':'' ?>>Hiển thị</option>
          <option value="0" <?= $status==='0'?'selected':'' ?>>Ẩn</option>
        </select>
      </form>
      <!-- Bulk Bar -->
      <div id="bulkBar" class="d-none" style="display:none;align-items:center;gap:8px;background:rgba(99,102,241,0.1);border:1px solid rgba(99,102,241,0.2);border-radius:8px;padding:6px 12px;">
        <span style="font-size:13px;color:var(--accent);"><span id="bulkCount">0</span> đã chọn</span>
        <button onclick="AdminJS.bulkToggle('<?=$table?>',1,location.href)" class="btn btn-xs btn-success"><i class="fas fa-eye"></i> Hiện</button>
        <button onclick="AdminJS.bulkToggle('<?=$table?>',0,location.href)" class="btn btn-xs btn-warning"><i class="fas fa-eye-slash"></i> Ẩn</button>
        <button onclick="AdminJS.bulkDelete('<?=$table?>',location.href)" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Xóa</button>
      </div>
    </div>
    <div class="toolbar-right">
      <span style="font-size:13px;color:var(--text-3);"><?= $total ?> website</span>
      <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Website</button>
    </div>
  </div>

  <div class="card-grid" data-sortable data-sortable-table="<?=$table?>" data-sortable-url="<?= ADMIN_URL ?>pages/websites.php">
    <?php if (empty($rows)): ?>
      <div class="empty-state" style="grid-column: 1/-1; padding:40px;"><i class="fas fa-globe"></i><h4>Chưa có website nào</h4><p>Nhấn "Thêm Website" để bắt đầu</p></div>
    <?php else: foreach ($rows as $i => $r): ?>
      <div class="cms-card" draggable="true" data-id="<?= $r['id'] ?>">
        <div class="cms-card-thumb" style="height: 140px; background: linear-gradient(135deg,var(--accent),var(--accent-2));">
          <?php if (!empty($r['logo'])): ?>
            <img src="<?=e($r['logo'])?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
            <div style="font-size:40px; font-weight:800; color:#fff;"><?= mb_strtoupper(mb_substr($r['name'],0,1)) ?></div>
          <?php endif; ?>
          <div class="cms-card-status">
            <label class="toggle-wrap">
              <span class="toggle-switch">
                <input type="checkbox" class="status-toggle" data-table="<?=$table?>" data-id="<?=$r['id']?>" data-field="status" <?= $r['status']?'checked':'' ?>>
                <span class="toggle-slider"></span>
              </span>
            </label>
          </div>
        </div>
        <div class="cms-card-body">
          <div class="cms-card-title"><?= e($r['name']) ?></div>
          <div class="cms-card-meta"><i class="fas fa-link"></i> <a href="<?=e($r['link'])?>" target="_blank" style="color:var(--text-2);"><?= e($r['domain']) ?></a></div>
          <div class="cms-card-actions">
            <button class="btn btn-outline" onclick="openEditModal(<?= htmlspecialchars(json_encode($r), ENT_QUOTES, 'UTF-8') ?>)" title="Sửa"><i class="fas fa-edit"></i> Edit</button>
            <a href="<?=e($r['link'])?>" target="_blank" class="btn btn-outline"><i class="fas fa-external-link-alt"></i> Visit</a>
            <button class="btn btn-outline" style="color:var(--danger);border-color:rgba(239,68,68,0.2);" onclick="deleteItem('<?=$table?>',<?=$r['id']?>,location.href)" title="Xóa"><i class="fas fa-trash"></i></button>
          </div>
        </div>
      </div>
    <?php endforeach; endif; ?>
  </div>

  <!-- Pagination -->
  <?php if ($lastPage > 1): ?>
  <div class="pagination-wrap">
    <span class="pagination-info">Trang <?=$page?> / <?=$lastPage?> — <?=$total?> kết quả</span>
    <div class="pagination-pages">
      <?php for ($i = 1; $i <= $lastPage; $i++):
        $url = '?page=' . $i . ($search ? '&search='.urlencode($search) : '') . ($status !== '' ? '&status='.$status : '');
      ?>
      <a href="<?= $url ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

</div><!-- /content-area -->
</div><!-- /admin-main -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="<?= ADMIN_URL ?>assets/js/admin.js"></script>
<script>
function openAddModal() {
  document.getElementById('modalTitle').textContent = 'Thêm Website';
  document.getElementById('formAction').value = 'create';
  document.getElementById('formId').value = '';
  document.getElementById('itemForm').reset();
  AdminJS.openModal('itemModal');
}
function openEditModal(data) {
  document.getElementById('modalTitle').textContent = 'Sửa Website';
  document.getElementById('formAction').value = 'update';
  document.getElementById('formId').value = data.id;
  AdminJS.fillForm('itemForm', data);
  AdminJS.openModal('itemModal');
}
document.getElementById('itemForm').addEventListener('submit', function(e) {
  e.preventDefault();
  AdminJS.submitForm('itemForm', location.href);
});
</script>
</body></html>
