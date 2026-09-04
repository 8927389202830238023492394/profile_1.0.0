<?php
// ============================================================
// ADMIN — Media Library
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Media Library';

// Handle uploads and deletes
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');

    if (!empty($_FILES['files'])) {
        $folder = sanitize($_POST['folder'] ?? 'general');
        $results = [];
        $files = $_FILES['files'];
        $count = count($files['name']);
        for ($i = 0; $i < $count; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i],
            ];
            if ($file['error'] === 0) {
                $results[] = uploadFile($file, $folder);
            } else {
                $results[] = ['success' => false, 'message' => 'Lỗi upload từ server (Mã: ' . $file['error'] . '). File có thể quá lớn.'];
            }
        }
        echo json_encode(['success' => true, 'results' => $results]);
        exit;
    }

    if ($_POST['action'] === 'delete_media') {
        $id = (int)($_POST['id'] ?? 0);
        $media = db()->prepare("SELECT path FROM media WHERE id=?");
        $media->execute([$id]);
        $row = $media->fetch();
        if ($row && file_exists($row['path'])) @unlink($row['path']);
        db()->prepare("DELETE FROM media WHERE id=?")->execute([$id]);
        echo json_encode(['success' => true]);
        exit;
    }
    echo json_encode(['success' => false]);
    exit;
}

// Fetch media
$folder = $_GET['folder'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 24;
$where = '1=1'; $params = [];
if ($folder) { $where .= ' AND folder=?'; $params[] = $folder; }

$countStmt = db()->prepare("SELECT COUNT(*) FROM media WHERE $where");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$lastPage = max(1, (int)ceil($total / $perPage));

$stmt = db()->prepare("SELECT * FROM media WHERE $where ORDER BY created_at DESC LIMIT $perPage OFFSET " . (($page-1)*$perPage));
$stmt->execute($params);
$mediaItems = $stmt->fetchAll();

// Get folder list
$folders = db()->query("SELECT DISTINCT folder, COUNT(*) as cnt FROM media GROUP BY folder")->fetchAll();
$totalSize = (int)db()->query("SELECT COALESCE(SUM(size),0) FROM media")->fetchColumn();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<!-- ── V2: Page Header ──────────────────────────── -->
<div style="margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center;">
  <div>
    <h1 style="font-size: 24px; font-weight: 700;">Media Library</h1>
    <p style="color: var(--text-2); font-size: 14px;">Upload and manage your assets, logos, and gallery images.</p>
  </div>
</div>
<?= csrfField() ?>
<div class="grid-2" style="gap:20px;margin-bottom:20px;">
  <!-- Upload Zone -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="ach-icon"><i class="fas fa-cloud-upload-alt"></i></div>
      <h3>Upload Ảnh</h3>
    </div>
    <div class="admin-card-body">
      <div class="dropzone" data-folder="general" id="uploadZone">
        <input type="file" accept="image/*" multiple id="fileInput">
        <i class="fas fa-cloud-upload-alt" style="font-size:48px;color:var(--text-3);margin-bottom:16px;display:block;"></i>
        <div class="dropzone-text">Kéo thả ảnh vào đây hoặc <span style="color:var(--accent);font-weight:600;">click để chọn</span></div>
        <div class="dropzone-sub">PNG, JPG, WebP, GIF — Tối đa 5MB mỗi file</div>
      </div>
      <div style="margin-top:12px;">
        <label class="form-label">Thư mục</label>
        <select id="uploadFolder" class="form-control">
          <option value="general">general (mặc định)</option>
          <option value="avatars">avatars</option>
          <option value="website_logos">website_logos</option>
          <option value="bank_logos">bank_logos</option>
          <option value="skill_logos">skill_logos</option>
        </select>
      </div>
      <div id="uploadProgress" style="margin-top:12px;display:none;">
        <div style="height:4px;background:var(--border-bright);border-radius:2px;overflow:hidden;">
          <div id="progressBar" style="height:100%;background:linear-gradient(90deg,var(--accent),var(--accent-2));width:0%;transition:width 0.3s;"></div>
        </div>
        <div id="uploadStatus" style="font-size:12px;color:var(--text-3);margin-top:6px;"></div>
      </div>
    </div>
  </div>

  <!-- Stats -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="ach-icon"><i class="fas fa-folder"></i></div>
      <h3>Thư Viện Ảnh</h3>
    </div>
    <div class="admin-card-body">
      <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px;">
        <a href="?folder=" class="btn <?=!$folder?'btn-primary':'btn-secondary'?> btn-sm">
          <i class="fas fa-th"></i> Tất cả (<?=$total?>)
        </a>
        <?php foreach ($folders as $f): ?>
        <a href="?folder=<?=urlencode($f['folder'])?>" class="btn <?=$folder===$f['folder']?'btn-primary':'btn-secondary'?> btn-sm">
          <i class="fas fa-folder"></i> <?=e($f['folder'])?> (<?=$f['cnt']?>)
        </a>
        <?php endforeach; ?>
      </div>
      <div style="background:var(--card-2);border:1px solid var(--border);border-radius:8px;padding:14px;">
        <div style="font-size:12px;color:var(--text-3);margin-bottom:8px;">Dung lượng sử dụng</div>
        <div style="font-size:20px;font-weight:800;color:var(--text);font-family:var(--font-mono);">
          <?= round($totalSize / 1024 / 1024, 2) ?> MB
        </div>
        <div style="font-size:12px;color:var(--text-3);margin-top:4px;"><?=$total?> file ảnh</div>
      </div>
    </div>
  </div>
</div>

<!-- Media Grid -->
<div class="admin-card">
  <div class="admin-card-header">
    <div class="ach-icon"><i class="fas fa-images"></i></div>
    <h3>Ảnh Đã Upload</h3>
    <div style="margin-left:auto;display:flex;gap:8px;">
      <button class="btn btn-xs btn-danger" id="deleteSelectedBtn" style="display:none;" onclick="deleteSelected()">
        <i class="fas fa-trash"></i> Xóa đã chọn (<span id="selectedCount">0</span>)
      </button>
    </div>
  </div>
  <div class="admin-card-body">
    <?php if (empty($mediaItems)): ?>
    <div class="empty-state"><i class="fas fa-images"></i><h4>Chưa có ảnh nào</h4><p>Upload ảnh đầu tiên của bạn</p></div>
    <?php else: ?>
    <div class="media-grid" id="mediaGrid">
      <?php foreach ($mediaItems as $m): ?>
      <div class="media-item" data-id="<?=$m['id']?>" data-url="<?=e($m['url'])?>" onclick="selectMediaItem(this,event)">
        <img src="<?=e($m['url'])?>" alt="<?=e($m['original_name'])?>" loading="lazy">
        <div class="media-item-overlay">
          <button class="btn btn-xs btn-info" onclick="event.stopPropagation();AdminJS.copyUrl('<?=e($m['url'])?>')"><i class="fas fa-copy"></i> Copy Link</button>
          <button class="btn btn-xs btn-danger" onclick="event.stopPropagation();deleteMediaItem(<?=$m['id']?>,this)"><i class="fas fa-trash"></i> Delete</button>
          <div class="media-name" style="font-size: 11px; text-align: center; color: white; padding: 0 10px;"><?=e(substr($m['original_name'],0,20))?></div>
        </div>
        <div class="media-check"><i class="fas fa-check"></i></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($lastPage > 1): ?>
    <div class="pagination-wrap" style="margin-top:16px;">
      <span class="pagination-info">Trang <?=$page?>/<?=$lastPage?></span>
      <div class="pagination-pages">
        <?php for($i=1;$i<=$lastPage;$i++): ?>
        <a href="?page=<?=$i?>&folder=<?=urlencode($folder)?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
        <?php endfor; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

</div></div>

<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
let selectedIds = new Set();

function selectMediaItem(el, e) {
  if (e.target.closest('.btn')) return;
  el.classList.toggle('selected');
  const id = el.dataset.id;
  if (el.classList.contains('selected')) selectedIds.add(id); else selectedIds.delete(id);
  document.getElementById('selectedCount').textContent = selectedIds.size;
  document.getElementById('deleteSelectedBtn').style.display = selectedIds.size > 0 ? '' : 'none';
}

function deleteMediaItem(id, btn) {
  AdminJS.confirmModal('Xóa ảnh này?', 'Hành động này không thể hoàn tác.', 'warning', async (modal) => {
    try {
      const item = btn.closest('.media-item');
      const fd = new FormData();
      fd.append('action','delete_media');fd.append('id',id);fd.append('_csrf_token',AdminJS.csrf);
      const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
      if(!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      if(data.success){item.remove();AdminJS.toast('success','Đã xóa!','');}
      else {AdminJS.toast('error','Lỗi!',data.message);}
      modal.remove();
    } catch(e) { AdminJS.toast('error','Lỗi kết nối',e.message); modal.remove(); }
  });
}

function deleteSelected() {
  if(!selectedIds.size) return;
  AdminJS.confirmModal(`Xóa ${selectedIds.size} ảnh?`, 'Hành động này không thể hoàn tác.', 'warning', async (modal) => {
    try {
      for (const id of selectedIds) {
        const fd = new FormData();
        fd.append('action','delete_media');fd.append('id',id);fd.append('_csrf_token',AdminJS.csrf);
        const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
        if(!res.ok) throw new Error(`HTTP ${res.status}`);
        document.querySelector(`.media-item[data-id="${id}"]`)?.remove();
      }
      selectedIds.clear();
      document.getElementById('deleteSelectedBtn').style.display='none';
      AdminJS.toast('success','Đã xóa thành công!','');
      modal.remove();
    } catch(e) { AdminJS.toast('error','Lỗi kết nối',e.message); modal.remove(); }
  });
}

// Dropzone upload
const dropzone = document.getElementById('uploadZone');
const fileInput = document.getElementById('fileInput');
const progress = document.getElementById('uploadProgress');
const progressBar = document.getElementById('progressBar');
const statusEl = document.getElementById('uploadStatus');

dropzone.addEventListener('dragover',e=>{e.preventDefault();dropzone.classList.add('drag-over');});
dropzone.addEventListener('dragleave',()=>dropzone.classList.remove('drag-over'));
dropzone.addEventListener('drop',e=>{e.preventDefault();dropzone.classList.remove('drag-over');uploadFiles(e.dataTransfer.files);});
fileInput.addEventListener('change',()=>uploadFiles(fileInput.files));

async function uploadFiles(files) {
  if (!files.length) return;
  const folder = document.getElementById('uploadFolder').value;
  progress.style.display='block';
  const total=files.length;
  let done=0;
  const grid = document.getElementById('mediaGrid');

  for (const file of files) {
    const fd = new FormData();
    fd.append('files[]',file);
    fd.append('folder',folder);
    fd.append('_csrf_token',AdminJS.csrf);
    statusEl.textContent=`Uploading ${file.name}...`;
    try {
      const res = await fetch(location.href,{method:'POST',body:fd,credentials:'same-origin'});
      if(!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      done++;
      progressBar.style.width=(done/total*100)+'%';
      if(data.results && data.results[0]) {
        if(data.results[0].success) {
          AdminJS.toast('success','Uploaded!',data.results[0].filename,2000);
          if(grid){const item=document.createElement('div');item.className='media-item';item.dataset.id=data.results[0].id;item.dataset.url=data.results[0].url;item.innerHTML=`<img src="${data.results[0].url}" loading="lazy"><div class="media-overlay"><button class="btn btn-xs btn-info" onclick="event.stopPropagation();AdminJS.copyUrl('${data.results[0].url}')"><i class="fas fa-copy"></i></button><button class="btn btn-xs btn-danger" onclick="event.stopPropagation();deleteMediaItem(${data.results[0].id},this)"><i class="fas fa-trash"></i></button></div><div class="media-check"><i class="fas fa-check"></i></div>`;item.addEventListener('click',function(e){selectMediaItem(this,e);});grid.prepend(item);}
        } else {
          AdminJS.toast('error','Lỗi Upload',data.results[0].message);
        }
      } else if (!data.success) {
        AdminJS.toast('error','Lỗi Upload',data.message);
      }
    } catch(e) {
      AdminJS.toast('error','Lỗi kết nối',e.message);
      done++;
    }
  }
  statusEl.textContent=`Hoàn thành ${done}/${total} files`;
  setTimeout(()=>{progress.style.display='none';progressBar.style.width='0%';},2000);
}
</script>
</body></html>
