<?php
// ============================================================
// ADMIN — Analytics / Visit Stats
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
requireAuth();
$pageTitle = 'Analytics';

$visitStats = getVisitStats();
$onlineNow = getOnlineCount();

// Chart data options
$range = (int)($_GET['range'] ?? 30);
$range = in_array($range, [7,30,90]) ? $range : 30;

$chartData = db()->prepare("SELECT visit_date, visit_count, unique_count FROM visits WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY) ORDER BY visit_date ASC");
$chartData->execute([$range]);
$visits = $chartData->fetchAll();

$labels = array_column($visits, 'visit_date');
$counts = array_map('intval', array_column($visits, 'visit_count'));
$uniques = array_map('intval', array_column($visits, 'unique_count'));

// Monthly data
$monthlyData = db()->query("SELECT DATE_FORMAT(visit_date,'%Y-%m') as month, SUM(visit_count) as total FROM visits GROUP BY DATE_FORMAT(visit_date,'%Y-%m') ORDER BY month DESC LIMIT 12")->fetchAll();

include dirname(__DIR__) . '/includes/head.php';
include dirname(__DIR__) . '/includes/sidebar.php';
include dirname(__DIR__) . '/includes/topbar.php';
?>
<div class="admin-main"><div class="content-area">

<!-- Stats Overview -->
<div class="dash-stats-grid" style="margin-bottom:24px;">
  <?php $statItems = [
    ['Hôm nay', $visitStats['today'], 'fas fa-calendar-day', '#6366F1', '6366F1','8B5CF6'],
    ['Hôm qua', $visitStats['yesterday'], 'fas fa-history', '#8B5CF6', '8B5CF6','6366F1'],
    ['7 ngày', $visitStats['week'], 'fas fa-calendar-week', '#10B981', '10B981','059669'],
    ['30 ngày', $visitStats['month'], 'fas fa-calendar-alt', '#F59E0B', 'F59E0B','D97706'],
    ['Tổng', $visitStats['total'], 'fas fa-chart-line', '#3B82F6', '3B82F6','2563EB'],
    ['Online', $onlineNow, 'fas fa-circle', '#EF4444', 'EF4444','DC2626'],
  ];
  foreach ($statItems as [$lbl,$val,$ico,$col,$c1,$c2]): ?>
  <div class="dash-stat-card" style="--stat-gradient:linear-gradient(90deg,#<?=$c1?>,#<?=$c2?>);">
    <div class="dsc-header">
      <div class="dsc-label"><?=$lbl?></div>
      <div class="dsc-icon" style="--dsc-color:<?=$col?>;"><i class="<?=$ico?>" style="color:<?=$col?>;"></i></div>
    </div>
    <div class="dsc-value"><?=number_format((int)$val)?></div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Main Chart -->
<div class="admin-card" style="margin-bottom:24px;">
  <div class="admin-card-header">
    <div class="ach-icon"><i class="fas fa-chart-area"></i></div>
    <h3>Biểu Đồ Lượt Xem</h3>
    <div style="margin-left:auto;display:flex;gap:6px;">
      <?php foreach([7,30,90] as $r): ?>
      <a href="?range=<?=$r?>" class="btn btn-xs <?=$range===$r?'btn-primary':'btn-secondary'?>"><?=$r?> ngày</a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="admin-card-body">
    <div class="chart-container" style="height:320px;"><canvas id="mainChart"></canvas></div>
  </div>
</div>

<!-- Monthly + Top Days -->
<div class="grid-2">
  <div class="admin-card">
    <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-calendar-alt"></i></div><h3>Thống Kê Theo Tháng</h3></div>
    <div class="admin-card-body"><div class="chart-container"><canvas id="monthlyChart"></canvas></div></div>
  </div>
  <div class="admin-card">
    <div class="admin-card-header"><div class="ach-icon"><i class="fas fa-trophy"></i></div><h3>Top 10 Ngày Cao Nhất</h3></div>
    <div class="modern-table-wrap">
      <table class="modern-table">
        <thead><tr><th>Ngày</th><th>Lượt xem</th><th>Unique</th></tr></thead>
        <tbody>
        <?php
          $top10 = db()->query("SELECT visit_date, visit_count, unique_count FROM visits ORDER BY visit_count DESC LIMIT 10")->fetchAll();
          foreach ($top10 as $t):
        ?>
        <tr>
          <td class="td-mono"><?=$t['visit_date']?></td>
          <td><span style="font-weight:700;color:var(--accent);"><?=number_format($t['visit_count'])?></span></td>
          <td style="color:var(--text-3);"><?=number_format($t['unique_count'])?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div></div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
const accent = getComputedStyle(document.documentElement).getPropertyValue('--accent').trim() || '#6366F1';
const opts = { responsive:true, maintainAspectRatio:false, plugins:{legend:{labels:{color:'#A0A0B8',font:{size:11}}}}, scales:{ x:{grid:{color:'rgba(255,255,255,0.03)'},ticks:{color:'#5A5A72',font:{size:10},maxTicksLimit:10}}, y:{grid:{color:'rgba(255,255,255,0.03)'},ticks:{color:'#5A5A72',font:{size:10}},beginAtZero:true} } };

new Chart(document.getElementById('mainChart'), {
  type:'line',
  data:{
    labels:<?=json_encode($labels)?>,
    datasets:[
      {label:'Lượt xem',data:<?=json_encode($counts)?>,borderColor:accent,backgroundColor:'rgba(99,102,241,0.08)',borderWidth:2,fill:true,tension:0.4,pointRadius:0},
      {label:'Unique',data:<?=json_encode($uniques)?>,borderColor:'#10B981',backgroundColor:'rgba(16,185,129,0.05)',borderWidth:2,fill:true,tension:0.4,pointRadius:0},
    ]
  },
  options:{...opts,plugins:{legend:{display:true,labels:{color:'#A0A0B8'}}}}
});

new Chart(document.getElementById('monthlyChart'), {
  type:'bar',
  data:{
    labels:<?=json_encode(array_column(array_reverse($monthlyData),'month'))?>,
    datasets:[{label:'Lượt xem/tháng',data:<?=json_encode(array_reverse(array_column($monthlyData,'total')))?>,backgroundColor:'rgba(99,102,241,0.6)',borderColor:accent,borderWidth:1,borderRadius:6}]
  },
  options:opts
});
</script>
</body></html>
