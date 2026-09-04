<?php
// ============================================================
// ADMIN — Testimonials (with Approval)
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Testimonials & Reviews';
$table = 'testimonials';
$fields = ['name','position','review','rating','approved'];

AdminCRUD::handle($table, $fields);

// Handle toggle approved via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle') {
    requireCsrf();
    header('Content-Type: application/json');
    $id = (int)($_POST['id'] ?? 0);
    $val = (int)($_POST['approved'] ?? 0);
    db()->prepare("UPDATE testimonials SET approved=? WHERE id=?")->execute([$val, $id]);
    echo json_encode(['success' => true]);
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;

$where = '1=1'; $params = [];
if ($filter === 'pending') { $where .= ' AND approved=0'; }
elseif ($filter === 'approved') { $where .= ' AND approved=1'; }
if ($search) { $where .= ' AND (name LIKE ? OR review LIKE ?)'; $params[] = "%$search%"; $params[] = "%$search%"; }

$countStmt = db()->prepare("SELECT COUNT(*) FROM testimonials WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM testimonials WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage));
$stmt->execute($params);
$rows = $stmt->fetchAll();

$pendingCount = (int)db()->query("SELECT COUNT(*) FROM testimonials WHERE approved=0")->fetchColumn();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<!-- ── V2: Page Header ──────────────────────────── -->
<div style="margin-bottom: 24px;">
  <h1 style="font-size: 24px; font-weight: 700;">Marketing & Engagement</h1>
  <p style="color: var(--text-2); font-size: 14px;">Quản lý đánh giá khách hàng và tin nhắn liên hệ.</p>
</div>

<!-- ── V2: Tabs ──────────────────────────── -->
<div class="cms-tabs">
  <a href="testimonials.php" class="cms-tab active"><i class="fas fa-star"></i> Reviews</a>
  <a href="contacts.php" class="cms-tab"><i class="fas fa-envelope"></i> Tin Nhắn Liên Hệ</a>
</div>
<!-- Add/Edit Modal -->
<div id="itemModal" class="modal-overlay">
<div class="modal-box">
  <div class="modal-header">
    <div class="mh-icon"><i class="fas fa-star"></i></div>
    <h4 id="modalTitle">Thêm Đánh Giá</h4>
    <button class="modal-close" data-modal-close><i class="fas fa-times"></i></button>
  </div>
  <form id="itemForm">
    <?= csrfField() ?>
    <input type="hidden" name="action" id="formAction" value="create">
    <input type="hidden" name="id" id="formId">
    <div class="modal-body">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Họ tên <span class="required">*</span></label>
          <input type="text" name="name" class="form-control" required>
        </div>
        <div class="form-group">
          <label class="form-label">Chức vụ / Công ty</label>
          <input type="text" name="position" class="form-control" placeholder="CEO tại TechCorp">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Nội dung đánh giá <span class="required">*</span></label>
        <textarea name="review" class="form-control" rows="4" required></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Số sao</label>
          <select name="rating" class="form-control">
            <option value="5">★★★★★ (5 sao)</option>
            <option value="4">★★★★☆ (4 sao)</option>
            <option value="3">★★★☆☆ (3 sao)</option>
            <option value="2">★★☆☆☆ (2 sao)</option>
            <option value="1">★☆☆☆☆ (1 sao)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Trạng thái</label>
          <select name="approved" class="form-control">
            <option value="1">Đã duyệt (hiển thị)</option>
            <option value="0">Chờ duyệt (ẩn)</option>
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

<div class="admin-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <form method="GET" style="display:contents;">
        <div class="admin-search"><i class="fas fa-search"></i><input type="text" name="search" placeholder="Tìm tên, nội dung..." value="<?=e($search)?>"></div>
        <select name="filter" class="admin-filter" onchange="this.form.submit()">
          <option value="all" <?=$filter==='all'?'selected':''?>>Tất cả</option>
          <option value="approved" <?=$filter==='approved'?'selected':''?>>Đã duyệt</option>
          <option value="pending" <?=$filter==='pending'?'selected':''?>>Chờ duyệt <?=$pendingCount>0?"($pendingCount)":'';?></option>
        </select>
      </form>
    </div>
    <div class="toolbar-right">
      <?php if ($pendingCount > 0): ?>
      <span class="status-badge pending"><i class="fas fa-clock"></i> <?=$pendingCount?> chờ duyệt</span>
      <?php endif; ?>
      <button class="btn btn-primary" onclick="openAddModal()"><i class="fas fa-plus"></i> Thêm Đánh Giá</button>
    </div>
  </div>

  <div class="modern-table-wrap">
    <table class="modern-table">
      <thead><tr>
        <th>Người dùng</th><th>Đánh giá</th><th>Sao</th><th>Ngày</th><th>Duyệt</th><th style="text-align:right;">Hành động</th>
      </tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
      <tr><td colspan="6"><div class="empty-state"><i class="fas fa-star"></i><h4>Không có đánh giá nào</h4></div></td></tr>
      <?php else: foreach ($rows as $r): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;flex-shrink:0;">
              <?=mb_strtoupper(mb_substr($r['name'],0,1))?>
            </div>
            <div>
              <div class="td-primary"><?=e($r['name'])?></div>
              <div style="font-size:11px;color:var(--text-3);"><?=e($r['position'])?></div>
            </div>
          </div>
        </td>
        <td style="max-width:240px;font-size:13px;color:var(--text-2);"><?=e(mb_strimwidth($r['review'],0,80,'...'))?></td>
        <td>
          <div style="display:flex;gap:2px;">
            <?php for ($s2=1;$s2<=5;$s2++): ?>
            <i class="fas fa-star" style="font-size:11px;color:<?=$s2<=$r['rating']?'#F59E0B':'var(--border-bright)'?>"></i>
            <?php endfor; ?>
          </div>
        </td>
        <td style="font-size:12px;color:var(--text-3);"><?=timeAgo($r['created_at'])?></td>
        <td>
          <label class="toggle-wrap"><span class="toggle-switch">
            <input type="checkbox" class="status-toggle" data-table="testimonials" data-id="<?=$r['id']?>" data-field="approved" <?=$r['approved']?'checked':''?>>
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

  <?php if ($lastPage > 1): ?>
  <div class="pagination-wrap">
    <span class="pagination-info">Trang <?=$page?>/<?=$lastPage?> — <?=$total?> đánh giá</span>
    <div class="pagination-pages">
      <?php for ($i=1;$i<=$lastPage;$i++): ?>
      <a href="?page=<?=$i?>&filter=<?=$filter?><?=$search?'&search='.urlencode($search):''?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
function openAddModal(){document.getElementById('modalTitle').textContent='Thêm Đánh Giá';document.getElementById('formAction').value='create';document.getElementById('formId').value='';document.getElementById('itemForm').reset();AdminJS.openModal('itemModal');}
function openEditModal(data){document.getElementById('modalTitle').textContent='Sửa Đánh Giá';document.getElementById('formAction').value='update';document.getElementById('formId').value=data.id;AdminJS.fillForm('itemForm',data);AdminJS.openModal('itemModal');}
document.getElementById('itemForm').addEventListener('submit',function(e){e.preventDefault();AdminJS.submitForm('itemForm',location.href);});
</script>
</body></html>
