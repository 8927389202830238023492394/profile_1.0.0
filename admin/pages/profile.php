<?php
// ============================================================
// ADMIN — Profile Settings
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Thông Tin Cá Nhân';
$msg = '';

// Handle save
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    requireCsrf();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'save_profile') {
        $fields = ['name','title','tagline','bio','about_me','experience','goals','email','phone','address',
                   'badge_1','badge_2','badge_3','badge_4','badge_5'];
        foreach ($fields as $f) {
            if (isset($_POST[$f])) updateSetting($f, trim($_POST[$f]));
        }
        echo json_encode(['success' => true, 'message' => 'Thông tin đã được lưu!']);
        exit;
    }

    if ($_POST['action'] === 'upload_avatar' && !empty($_FILES['avatar']['name'])) {
        $result = uploadFile($_FILES['avatar'], 'avatars');
        if ($result['success']) {
            updateSetting('avatar', $result['url']);
        }
        echo json_encode($result);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Action không hợp lệ.']);
    exit;
}

$s = getAllSettings();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>

<div class="admin-main">
<div class="content-area">

<!-- ── V2: Page Header ──────────────────────────── -->
<div style="margin-bottom: 24px;">
  <h1 style="font-size: 24px; font-weight: 700;">Personal Profile</h1>
  <p style="color: var(--text-2); font-size: 14px;">Quản lý thông tin cá nhân, mạng xã hội, kỹ năng và thành tựu.</p>
</div>

<!-- ── V2: Tabs ──────────────────────────── -->
<div class="cms-tabs">
  <a href="profile.php" class="cms-tab active"><i class="fas fa-user"></i> Basic Info</a>
  <a href="socials.php" class="cms-tab"><i class="fas fa-share-alt"></i> Social Network</a>
  <a href="skills.php" class="cms-tab"><i class="fas fa-code"></i> Skills & Tech</a>
  <a href="achievements.php" class="cms-tab"><i class="fas fa-trophy"></i> Achievements</a>
</div>

<div class="grid-2" style="gap:24px;">

  <!-- Avatar & Basic Info -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="ach-icon"><i class="fas fa-user-circle"></i></div>
      <h3>Ảnh Đại Diện</h3>
    </div>
    <div class="admin-card-body" style="text-align:center;">
      <!-- Avatar Preview -->
      <div style="position:relative;display:inline-block;margin-bottom:20px;">
        <div id="avatarPreview" style="width:120px;height:120px;border-radius:50%;overflow:hidden;border:3px solid var(--border-bright);box-shadow:0 0 0 4px rgba(99,102,241,0.15);margin:0 auto;">
          <?php $av = $s['avatar'] ?? ''; ?>
          <?php if ($av && (str_starts_with($av,'http') || file_exists(BASE_PATH.'/'.$av))): ?>
          <img id="avatarImg" src="<?= e($av) ?>" style="width:100%;height:100%;object-fit:cover;">
          <?php else: ?>
          <div id="avatarImg" style="width:100%;height:100%;background:linear-gradient(135deg,var(--accent),var(--accent-2));display:flex;align-items:center;justify-content:center;font-size:40px;font-weight:800;color:#fff;">
            <?= mb_strtoupper(mb_substr($s['name']??'A',0,1)) ?>
          </div>
          <?php endif; ?>
        </div>
        <label for="avatarInput" style="position:absolute;bottom:2px;right:2px;width:30px;height:30px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(99,102,241,0.4);">
          <i class="fas fa-camera" style="font-size:12px;color:#fff;"></i>
        </label>
        <input type="file" id="avatarInput" accept="image/*" style="display:none;" onchange="uploadAvatar(this)">
      </div>

      <div style="margin-top:12px;font-size:13px;color:var(--text-3);">JPG, PNG, WebP — Tối đa 5MB</div>
      <div id="avatarUploadStatus" style="margin-top:8px;font-size:12px;"></div>
    </div>
  </div>

  <!-- Info Summary -->
  <div class="admin-card">
    <div class="admin-card-header">
      <div class="ach-icon"><i class="fas fa-info-circle"></i></div>
      <h3>Thống Kê Profile</h3>
    </div>
    <div class="admin-card-body">
      <?php
        $stats2 = [
          ['fas fa-globe','Websites', db()->query("SELECT COUNT(*) FROM websites WHERE status=1")->fetchColumn()],
          ['fas fa-share-alt','Socials', db()->query("SELECT COUNT(*) FROM socials WHERE status=1")->fetchColumn()],
          ['fas fa-star','Reviews', db()->query("SELECT COUNT(*) FROM testimonials WHERE approved=1")->fetchColumn()],
          ['fas fa-eye','Tổng lượt xem', getVisitStats()['total']],
        ];
      ?>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <?php foreach ($stats2 as [$ico, $lbl, $val]): ?>
        <div style="background:var(--card-2);border:1px solid var(--border);border-radius:10px;padding:14px;text-align:center;">
          <i class="<?=$ico?>" style="color:var(--accent);font-size:18px;margin-bottom:8px;display:block;"></i>
          <div style="font-size:20px;font-weight:800;color:var(--text);font-family:var(--font-mono);"><?= number_format((int)$val) ?></div>
          <div style="font-size:11px;color:var(--text-3);margin-top:2px;"><?= $lbl ?></div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<div style="margin-top:24px;">
<form id="profileForm" novalidate>
<?= csrfField() ?>
<input type="hidden" name="action" value="save_profile">

<div class="cms-tabs" style="margin-top: 20px;">
  <div class="cms-tab active" onclick="switchFormTab('main')">Thông Tin Cơ Bản</div>
  <div class="cms-tab" onclick="switchFormTab('about')">About Me & Bio</div>
  <div class="cms-tab" onclick="switchFormTab('badges')">Skill Badges</div>
</div>

<div id="tab-main" class="cms-tab-content active">
  <div class="grid-2" style="gap:24px;">
    <!-- Main Info -->
    <div class="admin-card">
      <div class="admin-card-body" style="padding: 24px;">
        <h3 class="form-section-title" style="margin-top:0;">Định Danh Cá Nhân</h3>
        <div class="form-group">
          <label class="form-label">Họ tên đầy đủ <span class="required">*</span></label>
          <input type="text" name="name" class="form-control" value="<?= e($s['name']??'') ?>" required>
        </div>
        <div class="form-row" style="display:flex;gap:15px;">
          <div class="form-group" style="flex:1;">
            <label class="form-label">Chức danh</label>
            <input type="text" name="title" class="form-control" value="<?= e($s['title']??'') ?>" placeholder="Fullstack Developer">
          </div>
          <div class="form-group" style="flex:1;">
            <label class="form-label">Kinh nghiệm</label>
            <input type="text" name="experience" class="form-control" value="<?= e($s['experience']??'') ?>" placeholder="5+ Năm Kinh Nghiệm">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Tagline / Slogan</label>
          <input type="text" name="tagline" class="form-control" value="<?= e($s['tagline']??'') ?>" placeholder="Building the future...">
        </div>
      </div>
    </div>

    <!-- Contact Info -->
    <div class="admin-card">
      <div class="admin-card-body" style="padding: 24px;">
        <h3 class="form-section-title" style="margin-top:0;">Thông Tin Liên Hệ</h3>
        <div class="form-group">
          <label class="form-label">Email liên hệ</label>
          <input type="email" name="email" class="form-control" value="<?= e($s['email']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Số điện thoại</label>
          <input type="tel" name="phone" class="form-control" value="<?= e($s['phone']??'') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Địa chỉ</label>
          <input type="text" name="address" class="form-control" value="<?= e($s['address']??'') ?>">
        </div>
      </div>
    </div>
  </div>
</div>

<div id="tab-about" class="cms-tab-content" style="display:none;">
  <div class="admin-card">
    <div class="admin-card-body" style="padding: 24px;">
      <h3 class="form-section-title" style="margin-top:0;">Giới Thiệu Bản Thân</h3>
      <div class="form-group">
        <label class="form-label">Mô tả ngắn (Bio - Hiển thị ở Hero)</label>
        <textarea name="bio" class="form-control" rows="3"><?= e($s['bio']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Nội dung chi tiết (About Me - Hiển thị ở section Về Tôi) <span class="label-hint">Hỗ trợ HTML</span></label>
        <textarea name="about_me" class="form-control" rows="8"><?= e($s['about_me']??'') ?></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Mục tiêu phát triển (Goals)</label>
        <textarea name="goals" class="form-control" rows="3"><?= e($s['goals']??'') ?></textarea>
      </div>
    </div>
  </div>
</div>

<div id="tab-badges" class="cms-tab-content" style="display:none;">
  <div class="admin-card">
    <div class="admin-card-body" style="padding: 24px;">
      <h3 class="form-section-title" style="margin-top:0;">Skill Badges (Hiển thị ở Hero)</h3>
      <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:16px;">
        <?php for ($i = 1; $i <= 5; $i++): ?>
        <div class="form-group" style="margin-bottom:0;">
          <label class="form-label">Badge <?= $i ?></label>
          <input type="text" name="badge_<?= $i ?>" class="form-control" value="<?= e($s["badge_$i"]??'') ?>" placeholder="Skill <?= $i ?>">
        </div>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</div>

<div class="admin-card" style="margin-top: 24px;">
  <div class="admin-card-body" style="padding: 16px; display: flex; justify-content: flex-end;">
    <button type="submit" class="btn btn-primary" id="btnSaveProfile" style="min-width: 200px;"><i class="fas fa-save"></i> Lưu Tất Cả Thay Đổi</button>
  </div>
</div>

</form>
</div>

</div><!-- /content-area -->
</div><!-- /admin-main -->

<script src="<?= ADMIN_URL ?>assets/js/admin.js"></script>
<script>
function switchFormTab(tabId) {
  document.querySelectorAll('#profileForm .cms-tab').forEach(t => t.classList.remove('active'));
  event.currentTarget.classList.add('active');
  document.querySelectorAll('.cms-tab-content').forEach(c => c.style.display = 'none');
  document.getElementById('tab-' + tabId).style.display = 'block';
}

// Avatar upload
async function uploadAvatar(input) {
  if (!input.files || !input.files[0]) return;
  const file = input.files[0];
  if (!file) return;
  const status = document.getElementById('avatarUploadStatus');
  status.innerHTML = '<span style="color:var(--accent)"><i class="fas fa-spinner fa-spin"></i> Đang upload...</span>';

  const fd = new FormData();
  fd.append('action', 'upload_avatar');
  fd.append('avatar', file);
  fd.append('_csrf_token', AdminJS.csrf);

  const res = await fetch(location.href, { method: 'POST', body: fd });
  const data = await res.json();

  if (data.success) {
    const img = document.getElementById('avatarImg');
    if (img.tagName === 'IMG') { img.src = data.url; }
    else {
      const newImg = document.createElement('img');
      newImg.id = 'avatarImg';
      newImg.src = data.url;
      newImg.style.cssText = 'width:100%;height:100%;object-fit:cover;';
      img.replaceWith(newImg);
    }
    status.innerHTML = '<span style="color:var(--success)"><i class="fas fa-check"></i> Upload thành công!</span>';
    AdminJS.toast('success', 'Đã cập nhật avatar!', '');
  } else {
    // [SEC] Escape server message to prevent Reflected XSS
    const safeMsg = String(data.message||'').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    status.innerHTML = `<span style="color:var(--danger)">${safeMsg}</span>`;
  }
}

document.getElementById('profileForm').addEventListener('submit', function(e) {
  e.preventDefault();
  AdminJS.submitForm('profileForm', location.href);
});
</script>
</body></html>
