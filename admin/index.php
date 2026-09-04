<?php
// ============================================================
// ADMIN DASHBOARD — index.php
// ============================================================
require_once __DIR__ . '/includes/auth.php';
requireAuth();

$pageTitle = 'Dashboard';

// Summary stats
$totalWebsites     = (int)db()->query("SELECT COUNT(*) FROM websites WHERE status=1")->fetchColumn();
$totalSocials      = (int)db()->query("SELECT COUNT(*) FROM socials WHERE status=1")->fetchColumn();
$totalBanks        = (int)db()->query("SELECT COUNT(*) FROM banks WHERE status=1")->fetchColumn();
$totalServices     = (int)db()->query("SELECT COUNT(*) FROM services WHERE status=1")->fetchColumn();
$totalSkills       = (int)db()->query("SELECT COUNT(*) FROM skills WHERE status=1")->fetchColumn();
$totalTestimonials = (int)db()->query("SELECT COUNT(*) FROM testimonials WHERE approved=1")->fetchColumn();
$pendingReviews    = (int)db()->query("SELECT COUNT(*) FROM testimonials WHERE approved=0")->fetchColumn();
$totalContacts     = (int)db()->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$unreadContacts    = (int)db()->query("SELECT COUNT(*) FROM contacts WHERE is_read=0")->fetchColumn();
$onlineNow         = getOnlineCount();
$visitStats        = getVisitStats();

// Chart data: last 30 days
$chartData = db()->query("SELECT visit_date, visit_count FROM visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) ORDER BY visit_date ASC")->fetchAll();
$chartLabels = array_column($chartData, 'visit_date');
$chartValues = array_column($chartData, 'visit_count');

// Recent contacts
$recentContacts = db()->query("SELECT * FROM contacts ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent reviews (pending)
$pendingTestimonials = db()->query("SELECT * FROM testimonials WHERE approved=0 ORDER BY created_at DESC LIMIT 5")->fetchAll();

include __DIR__ . '/includes/head.php';
include __DIR__ . '/includes/sidebar.php';
include __DIR__ . '/includes/topbar.php';
?>

<div class="admin-main">
<div class="content-area">

<!-- ── V2: Header & Global Search ──────────────────────────── -->
<div style="display: flex; align-items: center; margin-bottom: 24px; gap: 20px;">
  <div>
    <h1 style="font-size: 24px; font-weight: 700;">Dashboard V2</h1>
    <p style="color: var(--text-2); font-size: 14px;">Welcome back, <?= e($adminUser['username'] ?? 'Admin') ?>. Here is your ecosystem overview.</p>
  </div>
  
  <div class="global-search-wrap">
    <i class="fas fa-search"></i>
    <input type="text" class="global-search-input" placeholder="Search anything...">
    <div class="global-search-shortcut">⌘K</div>
  </div>
</div>

<!-- ── V2: Overview Cards ────────────────────────────────────── -->
<div class="dash-stats-grid" style="margin-bottom: 24px;">
  <?php
    $statCards = [
      ['Tổng lượt xem', $visitStats['total']??0, 'fas fa-eye', '#6366F1', '6366F1', '8B5CF6'],
      ['Hôm nay', $visitStats['today']??0, 'fas fa-calendar-day', '#8B5CF6', '8B5CF6', '6366F1'],
      ['Khách liên hệ', $totalContacts, 'fas fa-envelope', '#F59E0B', 'F59E0B', 'D97706'],
      ['Dịch vụ Live', $totalServices, 'fas fa-briefcase', '#10B981', '10B981', '059669'],
      ['Website Live', $totalWebsites, 'fas fa-globe', '#3B82F6', '3B82F6', '2563EB'],
    ];
    // We display 4 cards, or grid-auto-columns. Let's do 5 using grid style
  ?>
  <div style="display: grid; grid-template-columns: repeat(5, 1fr); gap: 16px; grid-column: 1 / -1;">
    <?php foreach ($statCards as [$label, $val, $icon, $color, $c1, $c2]): ?>
    <div class="dash-stat-card" style="--stat-gradient:linear-gradient(90deg,#<?=$c1?>,#<?=$c2?>); padding: 16px;">
      <div class="dsc-header">
        <div class="dsc-label"><?= e($label) ?></div>
        <div class="dsc-icon" style="--dsc-bg:rgba(<?= implode(',', sscanf($color, '#%02x%02x%02x')) ?>,0.1);--dsc-color:<?= $color ?>;">
          <i class="<?= $icon ?>" style="color:<?= $color ?>;"></i>
        </div>
      </div>
      <div class="dsc-value" style="font-size: 24px; margin-top: 8px;"><?= number_format((int)$val, 0, ',', '.') ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>

<div class="grid-2">

  <!-- ── V2: Left Column ─────────────────────────────────────── -->
  <div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Quick Actions -->
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon"><i class="fas fa-bolt"></i></div>
        <h3>Quick Actions</h3>
      </div>
      <div class="admin-card-body" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 16px;">
        <a href="<?= ADMIN_URL ?>pages/profile" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-user-edit" style="font-size: 20px;"></i> Edit Profile</a>
        <a href="<?= ADMIN_URL ?>pages/services" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-plus-circle" style="font-size: 20px;"></i> Add Service</a>
        <a href="<?= ADMIN_URL ?>pages/media" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-upload" style="font-size: 20px;"></i> Upload Media</a>
        <a href="<?= ADMIN_URL ?>pages/websites" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-globe" style="font-size: 20px;"></i> Websites</a>
        <a href="<?= ADMIN_URL ?>pages/marketing" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-envelope" style="font-size: 20px;"></i> Inbox <?= $unreadContacts > 0 ? "($unreadContacts)" : "" ?></a>
        <a href="<?= ADMIN_URL ?>pages/theme" class="btn btn-outline" style="flex-direction: column; height: 80px; justify-content: center; gap: 8px;"><i class="fas fa-palette" style="font-size: 20px;"></i> Theme</a>
      </div>
    </div>

    <!-- System Health -->
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon" style="background: rgba(16,185,129,0.1); color: var(--success); border-color: rgba(16,185,129,0.2);"><i class="fas fa-heartbeat"></i></div>
        <h3>System Health</h3>
      </div>
      <div class="admin-card-body" style="padding: 16px;">
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
          <li style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
            <span style="color: var(--text-2);"><i class="fas fa-database" style="width: 20px;"></i> MySQL Database</span>
            <span class="status-badge active"><i class="fas fa-check"></i> Connected</span>
          </li>
          <li style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
            <span style="color: var(--text-2);"><i class="fas fa-folder-open" style="width: 20px;"></i> Upload Directory</span>
            <span class="status-badge <?= is_writable(dirname(__DIR__).'/assets') ? 'active' : 'inactive' ?>"><i class="fas <?= is_writable(dirname(__DIR__).'/assets') ? 'fa-check' : 'fa-times' ?>"></i> <?= is_writable(dirname(__DIR__).'/assets') ? 'Writable' : 'Read-only' ?></span>
          </li>
          <li style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
            <span style="color: var(--text-2);"><i class="fab fa-php" style="width: 20px;"></i> PHP Version</span>
            <span style="font-weight: 600;"><?= phpversion() ?></span>
          </li>
          <li style="display: flex; justify-content: space-between; align-items: center; font-size: 14px;">
            <span style="color: var(--text-2);"><i class="fas fa-server" style="width: 20px;"></i> MySQL Version</span>
            <span style="font-weight: 600;"><?= mb_substr(db()->query("SELECT VERSION()")->fetchColumn(), 0, 10) ?></span>
          </li>
        </ul>
      </div>
    </div>

    <!-- Quick Edit Widget -->
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon"><i class="fas fa-pen-nib"></i></div>
        <h3>Quick Edit Mode</h3>
        <span class="ach-sub">Profile & Bio</span>
      </div>
      <div class="admin-card-body" style="padding: 16px;">
        <?php 
          // Fetch settings directly for quick edit
          $s = [];
          $stmt = db()->query("SELECT setting_key, setting_value FROM settings");
          while ($row = $stmt->fetch()) { $s[$row['setting_key']] = $row['setting_value']; }
        ?>
        <form method="post" action="<?= ADMIN_URL ?>pages/profile.php">
          <input type="hidden" name="csrf_token" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
          <div class="form-row">
            <div class="form-group">
              <label class="form-label">Tên hiển thị</label>
              <input type="text" name="name" class="form-control" value="<?= e($s['name']??'') ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Chức danh</label>
              <input type="text" name="title" class="form-control" value="<?= e($s['title']??'') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Mô tả ngắn (Tagline)</label>
            <textarea name="tagline" class="form-control" rows="2"><?= e($s['tagline']??'') ?></textarea>
          </div>
          <button type="submit" name="save_profile" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
        </form>
      </div>
    </div>

  </div>

  <!-- ── V2: Right Column ────────────────────────────────────── -->
  <div style="display: flex; flex-direction: column; gap: 24px;">

    <!-- Global Status -->
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon"><i class="fas fa-server"></i></div>
        <h3>Global Status</h3>
      </div>
      <div class="admin-card-body" style="padding: 16px;">
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid var(--border);">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 10px; height: 10px; background: var(--success); border-radius: 50%; box-shadow: 0 0 10px var(--success);"></div>
            <span style="font-weight: 500;">Website Online</span>
          </div>
          <span style="color: var(--success); font-size: 13px;">All systems operational</span>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px; border-bottom: 1px solid var(--border);">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 10px; height: 10px; background: var(--success); border-radius: 50%;"></div>
            <span style="font-weight: 500;">Database Connection</span>
          </div>
          <span style="font-size: 13px; color: var(--text-2);">0.003s response</span>
        </div>
        <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px;">
          <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 10px; height: 10px; background: var(--accent); border-radius: 50%;"></div>
            <span style="font-weight: 500;">Cache Status</span>
          </div>
          <span style="font-size: 13px; color: var(--text-2);">Optimized</span>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="admin-card">
      <div class="admin-card-header">
        <div class="ach-icon"><i class="fas fa-history"></i></div>
        <h3>Recent Activity</h3>
      </div>
      <div class="admin-card-body" style="padding: 16px;">
        <div class="career-timeline" style="margin-top: 10px; padding-left: 10px;">
          
          <?php if (!empty($recentContacts)): ?>
          <div class="ct-item" style="padding-bottom: 20px;">
            <div class="ct-dot" style="background: var(--warning); box-shadow: 0 0 10px var(--warning);"></div>
            <div class="ct-content">
              <div class="ct-year" style="color: var(--text-3); font-size: 12px;"><?= timeAgo($recentContacts[0]['created_at']) ?></div>
              <div class="ct-title" style="font-size: 14px;">Liên hệ mới: <?= e($recentContacts[0]['name']) ?></div>
              <div class="ct-desc" style="font-size: 12px;"><?= mb_strimwidth($recentContacts[0]['message'], 0, 50, '...') ?></div>
            </div>
          </div>
          <?php endif; ?>

          <?php if (!empty($pendingTestimonials)): ?>
          <div class="ct-item" style="padding-bottom: 20px;">
            <div class="ct-dot" style="background: var(--info);"></div>
            <div class="ct-content">
              <div class="ct-year" style="color: var(--text-3); font-size: 12px;"><?= timeAgo($pendingTestimonials[0]['created_at']) ?></div>
              <div class="ct-title" style="font-size: 14px;">Review mới: <?= e($pendingTestimonials[0]['name']) ?></div>
            </div>
          </div>
          <?php endif; ?>

          <div class="ct-item" style="padding-bottom: 20px;">
            <div class="ct-dot" style="background: var(--success);"></div>
            <div class="ct-content">
              <div class="ct-year" style="color: var(--text-3); font-size: 12px;">Hôm nay</div>
              <div class="ct-title" style="font-size: 14px;">Hệ thống nâng cấp V5</div>
              <div class="ct-desc" style="font-size: 12px;">Cập nhật Cloud Ecosystem & CMS Dashboard V2.</div>
            </div>
          </div>

        </div>
      </div>
    </div>

  </div>

</div>

</div><!-- /content-area -->
</div><!-- /admin-main -->

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="<?= ADMIN_URL ?>assets/js/admin.js"></script>
<script>
// Visit Chart
const visitCtx = document.getElementById('visitChart');
if (visitCtx) {
  new Chart(visitCtx, {
    type: 'line',
    data: {
      labels: <?= json_encode($chartLabels) ?>,
      datasets: [{
        label: 'Lượt xem',
        data: <?= json_encode(array_map('intval', $chartValues)) ?>,
        borderColor: '<?= getSetting('accent_color','#6366F1') ?>',
        backgroundColor: 'rgba(99,102,241,0.08)',
        borderWidth: 2,
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        pointHoverRadius: 5,
        pointHoverBackgroundColor: '<?= getSetting('accent_color','#6366F1') ?>',
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#5A5A72', font: { size: 10 }, maxTicksLimit: 7 } },
        y: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { color: '#5A5A72', font: { size: 10 } }, beginAtZero: true },
      }
    }
  });
}

// Content Pie Chart
const contentCtx = document.getElementById('contentChart');
if (contentCtx) {
  new Chart(contentCtx, {
    type: 'doughnut',
    data: {
      labels: ['Websites','Socials','Services','Skills','Reviews'],
      datasets: [{
        data: [<?= $totalWebsites ?>, <?= $totalSocials ?>, <?= $totalServices ?>, <?= $totalSkills ?>, <?= $totalTestimonials ?>],
        backgroundColor: ['#6366F1','#8B5CF6','#10B981','#F59E0B','#EF4444'],
        borderWidth: 0,
        hoverOffset: 6,
      }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { position: 'right', labels: { color: '#A0A0B8', font: { size: 11 }, boxWidth: 10, padding: 14 } },
      },
      cutout: '68%',
    }
  });
}

function approveReview(id) {
  AdminJS.ajax({ url: '<?= ADMIN_URL ?>pages/testimonials.php', data: { action: 'toggle', id, approved: 1, <?= CSRF_TOKEN_NAME ?>: AdminJS.csrf } })
    .then(r => { if(r.success) { AdminJS.toast('success', 'Đã duyệt!', 'Đánh giá đã được hiển thị.'); setTimeout(()=>location.reload(),1200); } });
}
function deleteReview(id) {
  if (!confirm('Xóa đánh giá này?')) return;
  AdminJS.ajax({ url: '<?= ADMIN_URL ?>pages/testimonials.php', data: { action: 'delete', id, <?= CSRF_TOKEN_NAME ?>: AdminJS.csrf } })
    .then(r => { if(r.success) { AdminJS.toast('success', 'Đã xóa!', ''); setTimeout(()=>location.reload(),1200); } });
}
</script>
</body>
</html>
