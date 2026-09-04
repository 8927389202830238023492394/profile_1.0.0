<?php
// ============================================================
// ADMIN — Contacts Manager
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();
$pageTitle = 'Contact Manager';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');
    if ($_POST['action'] === 'delete') {
        db()->prepare("DELETE FROM contacts WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_POST['action'] === 'mark_read') {
        db()->prepare("UPDATE contacts SET is_read=1 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['success' => true]);
        exit;
    }
    if ($_POST['action'] === 'bulk_delete') {
        $ids = array_filter(array_map('intval', explode(',', $_POST['ids']??'')));
        if ($ids) { $ph=implode(',',array_fill(0,count($ids),'?')); db()->prepare("DELETE FROM contacts WHERE id IN ($ph)")->execute($ids); }
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

$filter = $_GET['filter'] ?? 'all';
$search = trim($_GET['search'] ?? '');
$page = max(1,(int)($_GET['page']??1));
$perPage = 15;
$where = '1=1'; $params = [];
if ($filter === 'unread') { $where .= ' AND is_read=0'; }
if ($search) { $where .= ' AND (name LIKE ? OR email LIKE ? OR message LIKE ?)'; $params = array_merge($params, ["%$search%","%$search%","%$search%"]); }

$countStmt = db()->prepare("SELECT COUNT(*) FROM contacts WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$lastPage = max(1,(int)ceil($total/$perPage));

$stmt = db()->prepare("SELECT * FROM contacts WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET ".(($page-1)*$perPage));
$stmt->execute($params);
$rows = $stmt->fetchAll();
$unread = (int)db()->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();

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
  <a href="testimonials.php" class="cms-tab"><i class="fas fa-star"></i> Reviews</a>
  <a href="contacts.php" class="cms-tab active"><i class="fas fa-envelope"></i> Tin Nhắn Liên Hệ</a>
</div>
<!-- Detail Modal -->
<div id="detailModal" class="modal-overlay">
<div class="modal-box modal-lg">
  <div class="modal-header">
    <div class="mh-icon"><i class="fas fa-envelope-open"></i></div>
    <h4 id="detailName">Chi tiết tin nhắn</h4>
    <button class="modal-close" data-modal-close><i class="fas fa-times"></i></button>
  </div>
  <div class="modal-body">
    <div id="detailContent" style="color:var(--text-2);font-size:14px;line-height:1.8;"></div>
  </div>
</div>
</div>

<div class="admin-card">
  <div class="table-toolbar">
    <div class="toolbar-left">
      <form method="GET" style="display:contents;">
        <div class="admin-search"><i class="fas fa-search"></i><input type="text" name="search" placeholder="Tìm tên, email..." value="<?=e($search)?>"></div>
        <select name="filter" class="admin-filter" onchange="this.form.submit()">
          <option value="all" <?=$filter==='all'?'selected':''?>>Tất cả (<?=$total?>)</option>
          <option value="unread" <?=$filter==='unread'?'selected':''?>>Chưa đọc (<?=$unread?>)</option>
        </select>
      </form>
      <div id="bulkBar" class="d-none" style="display:none;align-items:center;gap:8px;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.2);border-radius:8px;padding:6px 12px;">
        <span style="font-size:13px;color:var(--danger);"><span id="bulkCount">0</span> đã chọn</span>
        <button onclick="AdminJS.bulkDelete('contacts',location.href)" class="btn btn-xs btn-danger"><i class="fas fa-trash"></i> Xóa</button>
      </div>
    </div>
    <div class="toolbar-right">
      <?php if ($unread > 0): ?>
      <span class="status-badge pending"><i class="fas fa-envelope"></i> <?=$unread?> chưa đọc</span>
      <?php endif; ?>
    </div>
  </div>

  <div class="modern-table-wrap">
    <table class="modern-table">
      <thead><tr>
        <th><input type="checkbox" id="selectAll" style="accent-color:var(--accent);width:15px;height:15px;"></th>
        <th>Người gửi</th><th>Email</th><th>SĐT</th><th>Nội dung</th><th>IP</th><th>Thời gian</th><th style="text-align:right;">Hành động</th>
      </tr></thead>
      <tbody>
      <?php if (empty($rows)): ?>
      <tr><td colspan="8"><div class="empty-state"><i class="fas fa-inbox"></i><h4>Không có tin nhắn nào</h4></div></td></tr>
      <?php else: foreach ($rows as $r): ?>
      <tr style="<?=!$r['is_read']?'background:rgba(99,102,241,0.03);':''?>">
        <td class="td-check"><input type="checkbox" class="row-check" value="<?=$r['id']?>"></td>
        <td>
          <div style="display:flex;align-items:center;gap:8px;">
            <?php if (!$r['is_read']): ?><span style="width:7px;height:7px;background:var(--accent);border-radius:50%;flex-shrink:0;"></span><?php endif; ?>
            <span class="td-primary"><?=e($r['name'])?></span>
          </div>
        </td>
        <td class="td-mono" style="font-size:12px;"><?=e($r['email'])?></td>
        <td style="color:var(--text-3);font-size:12px;"><?=e($r['phone'])?:'-'?></td>
        <td style="font-size:13px;color:var(--text-2);max-width:200px;"><?=e(mb_strimwidth($r['message'],0,60,'...'))?></td>
        <td class="td-mono" style="font-size:11px;color:var(--text-3);"><?=e($r['ip_address'])?></td>
        <td style="font-size:12px;color:var(--text-3);"><?=timeAgo($r['created_at'])?></td>
        <td><div class="td-actions">
          <button class="btn btn-xs btn-info btn-icon" onclick='viewContact(<?=json_encode($r)?>'><i class="fas fa-eye"></i></button>
          <button class="btn btn-xs btn-danger btn-icon" onclick="deleteItem('contacts',<?=$r['id']?>,location.href)"><i class="fas fa-trash"></i></button>
        </div></td>
      </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if ($lastPage > 1): ?>
  <div class="pagination-wrap">
    <span class="pagination-info">Trang <?=$page?>/<?=$lastPage?> — <?=$total?> tin nhắn</span>
    <div class="pagination-pages">
      <?php for($i=1;$i<=$lastPage;$i++): ?><a href="?page=<?=$i?>&filter=<?=$filter?><?=$search?'&search='.urlencode($search):''?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a><?php endfor; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
// [SEC] Sanitize user-input for display in innerHTML
function escHtml(s) {
  return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

async function viewContact(data) {
  document.getElementById('detailName').textContent = 'Tin nhắn từ: ' + data.name;
  // [SEC] Use escHtml on all user-supplied values to prevent Stored XSS
  document.getElementById('detailContent').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
      <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:12px;">
        <div style="font-size:11px;color:var(--text-3);margin-bottom:4px;">HỌ TÊN</div>
        <div style="font-weight:600;">${escHtml(data.name)}</div>
      </div>
      <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:12px;">
        <div style="font-size:11px;color:var(--text-3);margin-bottom:4px;">EMAIL</div>
        <div><a href="mailto:${escHtml(data.email)}" style="color:var(--accent);">${escHtml(data.email)}</a></div>
      </div>
      <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:12px;">
        <div style="font-size:11px;color:var(--text-3);margin-bottom:4px;">SĐT</div>
        <div>${escHtml(data.phone)||'—'}</div>
      </div>
      <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:12px;">
        <div style="font-size:11px;color:var(--text-3);margin-bottom:4px;">IP</div>
        <div style="font-family:var(--font-mono);font-size:12px;">${escHtml(data.ip_address)}</div>
      </div>
    </div>
    <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:16px;">
      <div style="font-size:11px;color:var(--text-3);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.08em;">NỘI DUNG</div>
      <div style="line-height:1.8;">${escHtml(data.message).replace(/\n/g,'<br>')}</div>
    </div>
    <div style="margin-top:12px;font-size:12px;color:var(--text-3);">Gửi lúc: ${escHtml(data.created_at)}</div>
  `;
  AdminJS.openModal('detailModal');
  // Mark as read
  if (!data.is_read) {
    const fd=new FormData();fd.append('action','mark_read');fd.append('id',data.id);fd.append('_csrf_token',AdminJS.csrf);
    await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
  }
}
</script>
</body></html>
