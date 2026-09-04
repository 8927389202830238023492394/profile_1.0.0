<?php
// ============================================================
// INDEX.PHP — Cyber Profile V3 — Personal Tech Ecosystem
// ============================================================
require_once __DIR__ . '/includes/functions.php';
startSecureSession();
trackVisit();

$s        = getAllSettings();
$sections = getPageSections();
$secMap   = array_column($sections, null, 'section_key');

$websites     = db()->query("SELECT * FROM websites WHERE status=1 ORDER BY sort_order ASC")->fetchAll();
$socials      = db()->query("SELECT * FROM socials WHERE status=1 ORDER BY sort_order ASC")->fetchAll();
$skills       = db()->query("SELECT * FROM skills WHERE status=1 ORDER BY sort_order ASC")->fetchAll();
$banks        = db()->query("SELECT * FROM banks WHERE status=1")->fetchAll();
$achievements = db()->query("SELECT * FROM achievements WHERE status=1 ORDER BY sort_order ASC")->fetchAll();
$services     = db()->query("SELECT * FROM services WHERE status=1 ORDER BY sort_order ASC")->fetchAll();
$statistics   = db()->query("SELECT * FROM statistics ORDER BY sort_order ASC")->fetchAll();
$testimonials = db()->query("SELECT * FROM testimonials WHERE approved=1 ORDER BY id DESC")->fetchAll();
$online       = getOnlineCount();
$visitStats   = getVisitStats();

function hexToRgb(string $hex): string {
    $hex = ltrim($hex, '#');
    if (strlen($hex) === 3) $hex = str_repeat($hex[0],2).str_repeat($hex[1],2).str_repeat($hex[2],2);
    return hexdec(substr($hex,0,2)).','.hexdec(substr($hex,2,2)).','.hexdec(substr($hex,4,2));
}
$accentRgb = hexToRgb($s['accent_color'] ?? '#6366F1');

$typewriterTitles = array_values(array_filter([
    $s['title']   ?? '',
    $s['badge_1'] ?? '',
    $s['badge_2'] ?? '',
    $s['badge_3'] ?? '',
    $s['badge_4'] ?? '',
]));
if (empty($typewriterTitles)) $typewriterTitles = ['Fullstack Developer'];

$socialBrand = [
    'Facebook'  => ['icon_color'=>'#1877F2','bg'=>'rgba(24,119,242,0.1)','border'=>'rgba(24,119,242,0.3)','glow'=>'rgba(24,119,242,0.2)'],
    'Telegram'  => ['icon_color'=>'#0088CC','bg'=>'rgba(0,136,204,0.1)','border'=>'rgba(0,136,204,0.3)','glow'=>'rgba(0,136,204,0.2)'],
    'TikTok'    => ['icon_color'=>'#FF0050','bg'=>'rgba(255,0,80,0.1)','border'=>'rgba(255,0,80,0.3)','glow'=>'rgba(255,0,80,0.2)'],
    'Discord'   => ['icon_color'=>'#5865F2','bg'=>'rgba(88,101,242,0.1)','border'=>'rgba(88,101,242,0.3)','glow'=>'rgba(88,101,242,0.2)'],
    'YouTube'   => ['icon_color'=>'#FF0000','bg'=>'rgba(255,0,0,0.1)','border'=>'rgba(255,0,0,0.3)','glow'=>'rgba(255,0,0,0.2)'],
    'GitHub'    => ['icon_color'=>'#E0E0E0','bg'=>'rgba(255,255,255,0.06)','border'=>'rgba(255,255,255,0.15)','glow'=>'rgba(255,255,255,0.08)'],
    'Zalo'      => ['icon_color'=>'#0068FF','bg'=>'rgba(0,104,255,0.1)','border'=>'rgba(0,104,255,0.3)','glow'=>'rgba(0,104,255,0.2)'],
    'Instagram' => ['icon_color'=>'#E1306C','bg'=>'rgba(225,48,108,0.1)','border'=>'rgba(225,48,108,0.3)','glow'=>'rgba(225,48,108,0.2)'],
    'Twitter'   => ['icon_color'=>'#1DA1F2','bg'=>'rgba(29,161,242,0.1)','border'=>'rgba(29,161,242,0.3)','glow'=>'rgba(29,161,242,0.2)'],
    'LinkedIn'  => ['icon_color'=>'#0A66C2','bg'=>'rgba(10,102,194,0.1)','border'=>'rgba(10,102,194,0.3)','glow'=>'rgba(10,102,194,0.2)'],
];

// Avatar
$avatar = $s['avatar'] ?? '';
$hasAvatar = !empty($avatar) && (filter_var($avatar, FILTER_VALIDATE_URL) || file_exists(BASE_PATH.'/'.ltrim($avatar,'/')));
$initials  = strtoupper(substr(preg_replace('/[^A-Za-zÀ-ỹ]/u','', $s['name'] ?? 'A'), 0, 1));

// Career timeline from experience year
$expStr = $s['experience'] ?? '5 Năm';
$expYears = (int)filter_var($expStr, FILTER_SANITIZE_NUMBER_INT) ?: 5;
$startYear = (int)date('Y') - $expYears;

// Socials map
$facebook = $telegram = null;
foreach ($socials as $soc) {
    if ($soc['platform'] === 'Facebook') $facebook = $soc;
    if ($soc['platform'] === 'Telegram') $telegram = $soc;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <?= HTML_BRANDING_COMMENT ?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="<?= e($s['meta_description'] ?? '') ?>">
  <meta name="keywords"    content="<?= e($s['meta_keywords'] ?? '') ?>">
  <meta name="author"      content="<?= PROJECT_AUTHOR ?>">
  <meta name="copyright"   content="<?= PROJECT_COPYRIGHT ?>">
  <meta name="generator"   content="<?= PROJECT_GENERATOR ?>">
  <meta name="robots"      content="index, follow">
  <link rel="canonical"    href="<?= e($s['canonical_url'] ?? '') ?>">

  <meta property="og:site_name"   content="<?= LEVUPHONG_SIGNATURE ?>">

  <meta property="og:title"       content="<?= e($s['meta_title'] ?? '') ?>">
  <meta property="og:description" content="<?= e($s['meta_description'] ?? '') ?>">
  <meta property="og:image"       content="<?= e($s['og_image'] ?? '') ?>">
  <meta property="og:type"        content="website">
  <meta property="og:url"         content="<?= e($s['canonical_url'] ?? '') ?>">
  <meta name="twitter:card"       content="<?= e($s['twitter_card'] ?? 'summary_large_image') ?>">
  <meta name="twitter:title"      content="<?= e($s['meta_title'] ?? '') ?>">

  <!-- JSON-LD SEO Enhancement -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "Organization",
    "name": "DichVuGiaRe",
    "founder": "Le Vu Phong",
    "url": "https://dichvugiare.net"
  }
  </script>

  <?php if (($s['pwa_enabled']??'1')==='1'): ?>
  <link rel="manifest" href="manifest.json">
  <meta name="theme-color" content="<?= e($s['pwa_theme_color']??'#6366F1') ?>">
  <link rel="apple-touch-icon" href="assets/img/icon-192.png">
  <?php endif; ?>

  <title><?= e($s['meta_title'] ?? $s['name'] ?? 'Profile') ?></title>
  <link rel="icon" href="<?= e($s['favicon'] ?? 'assets/img/favicon.ico') ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Three.js + Earth Engine -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>

  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    :root {
      --accent:    <?= e($s['accent_color']     ?? '#6366F1') ?>;
      --accent-2:  <?= e($s['accent_secondary'] ?? '#8B5CF6') ?>;
      --accent-rgb: <?= $accentRgb ?>;
      --accent-glow: rgba(<?= $accentRgb ?>,0.35);
      --accent-glow-sm: rgba(<?= $accentRgb ?>,0.15);
      --bg:        <?= e($s['bg_color']    ?? '#050508') ?>;
      --card-solid:<?= e($s['card_color']  ?? '#0C0C14') ?>;
      --text:      <?= e($s['text_color']  ?? '#F0F0FF') ?>;
      --text-2:    <?= e($s['text_secondary'] ?? '#9898B8') ?>;
    }
  </style>
  <?php if (!empty($s['header_script'])): ?><?= $s['header_script'] ?><?php endif; ?>
</head>
<body<?php if(($s['announcement_enabled']??'0')==='1' && !empty($s['announcement_text'])): ?> class="has-announcement"<?php endif; ?>>

<!-- Earth Engine V4 — Full-page WebGL Canvas -->
<canvas id="earth-canvas" aria-hidden="true"></canvas>

<!-- Background Grid Overlay -->
<div class="bg-canvas">
  <div class="bg-orb bg-orb-1"></div>
  <div class="bg-orb bg-orb-2"></div>
  <div class="bg-orb bg-orb-3"></div>
</div>

<!-- Earth Mode Badge -->
<div class="earth-mode-badge" id="earthModeBadge">
  <span class="emb-dot"></span>
  <span class="emb-text" id="earthModeText">Network Globe</span>
  <span class="emb-icon"><i class="fas fa-globe"></i></span>
</div>

<!-- Earth Drag Hint -->
<div class="earth-drag-hint" id="earthDragHint">
  <i class="fas fa-hand-pointer"></i> Kéo để xoay
</div>

<!-- Announcement -->
<?php if (($s['announcement_enabled']??'0')==='1' && !empty($s['announcement_text'])): ?>
<div class="announcement-bar" style="background:<?= e($s['announcement_color']??'#6366F1') ?>;">
  <i class="fas fa-bell"></i>
  <span><?= e($s['announcement_text']) ?></span>
  <button class="btn-close-ann" aria-label="Đóng"><i class="fas fa-times"></i></button>
</div>
<?php endif; ?>

<div id="toast-container"></div>

<!-- ══════════════════════════════════════════════════════════════
     HERO V3 — Cinematic Earth Background + Profile Overlay
     ══════════════════════════════════════════════════════════════ -->
<section id="hero">
  <div class="hero-v3-wrap">

    <!-- Profile Panel — Overlaid in front of Earth -->
    <div class="hero-panel">

      <!-- Top: Ecosystem Status -->
      <div class="hp-ecosystem-badge">
        <span class="eco-dot"></span>
        <span>Global Tech Ecosystem</span>
        <span class="eco-version">v4.0</span>
      </div>

      <!-- Avatar Block -->
      <div class="hero-avatar-block">
        <div class="avatar-stage">
          <div class="avatar-stage-inner">
            <div class="avatar-glow-ring"></div>
            <?php if ($hasAvatar): ?>
            <img src="<?= e($avatar) ?>" alt="<?= e($s['name']??'') ?>" class="hero-avatar-img" width="120" height="120" loading="eager">
            <?php else: ?>
            <div class="hero-avatar-ph"><?= $initials ?></div>
            <?php endif; ?>
          </div>
          <div class="online-badge">
            <span class="online-dot"></span>
            <span>Đang hoạt động</span>
          </div>
        </div>
        <div class="avatar-side-info">
          <div class="hero-greeting">Xin chào, tôi là</div>
          <h1 class="hero-name"><span class="grad"><?= e($s['name'] ?? 'Your Name') ?></span></h1>
          <div class="hero-title-row">
            <span class="hero-title-prefix">—</span>
            <div class="hero-typewriter">
              <span id="typewriter-text"></span><span class="typewriter-cursor"></span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bio -->
      <p class="hero-bio"><?= e($s['bio'] ?? $s['tagline'] ?? 'Chuyên gia phát triển hệ sinh thái phần mềm, xây dựng sản phẩm SaaS từ ý tưởng đến vận hành.') ?></p>

      <!-- Skill Tags -->
      <div class="hero-tags">
        <?php for ($i=1;$i<=5;$i++): $b=$s["badge_$i"]??''; if(!$b) continue; ?>
        <span class="tag"><?= e($b) ?></span>
        <?php endfor; ?>
      </div>

      <!-- Live Activity Stats (compact) -->
      <div class="hero-live-stats">
        <div class="hls-item">
          <span class="hls-dot"></span>
          <span class="hls-val"><?= $online ?></span>
          <span class="hls-key">Online</span>
        </div>
        <div class="hls-sep"></div>
        <div class="hls-item">
          <i class="fas fa-globe" style="color:var(--accent);font-size:10px;"></i>
          <span class="hls-val"><?= count($websites) ?></span>
          <span class="hls-key">Projects</span>
        </div>
        <div class="hls-sep"></div>
        <div class="hls-item">
          <i class="fas fa-bolt" style="color:#F59E0B;font-size:10px;"></i>
          <span class="hls-val"><?= count($services) ?></span>
          <span class="hls-key">Services</span>
        </div>
        <div class="hls-sep"></div>
        <div class="hls-item">
          <i class="fas fa-satellite" style="color:var(--accent-2);font-size:10px;"></i>
          <span class="hls-val">9</span>
          <span class="hls-key">Countries</span>
        </div>
      </div>

      <!-- CTA Buttons -->
      <div class="hero-cta">
        <a href="#sec-contact" class="btn btn-primary btn-lg">
          <i class="fas fa-briefcase"></i> Thuê Tôi
        </a>
        <a href="#sec-contact" class="btn btn-outline btn-lg">
          <i class="fas fa-paper-plane"></i> Liên Hệ
        </a>
        <?php if ($facebook): ?>
        <a href="<?= e($facebook['link']) ?>" target="_blank" rel="noopener" class="btn btn-ghost" style="border-color:rgba(24,119,242,0.3);">
          <i class="fab fa-facebook-f" style="color:#1877F2;"></i>
        </a>
        <?php endif; ?>
        <?php if ($telegram): ?>
        <a href="<?= e($telegram['link']) ?>" target="_blank" rel="noopener" class="btn btn-ghost" style="border-color:rgba(0,136,204,0.3);">
          <i class="fab fa-telegram-plane" style="color:#0088CC;"></i>
        </a>
        <?php endif; ?>
      </div>

      <!-- Social Mini Icons -->
      <div class="hero-socials">
        <?php foreach ($socials as $soc):
          $brand = $socialBrand[$soc['platform']] ?? [];
          $ic = $brand['icon_color'] ?? '#6366F1';
        ?>
        <a href="<?= e($soc['link']) ?>" target="_blank" rel="noopener"
           class="hero-social-link" title="<?= e($soc['platform']) ?>"
           style="--hover-color:<?= $ic ?>;">
          <i class="<?= e($soc['icon_class']) ?>" style="color:<?= $ic ?>;"></i>
        </a>
        <?php endforeach; ?>
      </div>

    </div><!-- /hero-panel -->

    <!-- Floating Service Cloud (V5) -->
    <div class="hero-service-cloud">
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-web" style="--delay: 0s;">
        <i class="fas fa-code"></i><span>Tạo Website</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-host" style="--delay: 0.5s;">
        <i class="fas fa-server"></i><span>Hosting</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-vps" style="--delay: 1.2s;">
        <i class="fas fa-hdd"></i><span>VPS</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-proxy" style="--delay: 0.8s;">
        <i class="fas fa-network-wired"></i><span>Proxy</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-source" style="--delay: 1.5s;">
        <i class="fas fa-laptop-code"></i><span>Mã Nguồn</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-logo" style="--delay: 0.3s;">
        <i class="fas fa-palette"></i><span>Thiết Kế Logo</span>
      </a>
      <a href="https://dichvugiare.net" target="_blank" class="fsc-node fsc-domain" style="--delay: 1.8s;">
        <i class="fas fa-globe"></i><span>Tên Miền</span>
      </a>
      
      <!-- Central Hub connecting to Earth -->
      <div class="fsc-hub">
        <div class="fsc-hub-core"></div>
        <div class="fsc-hub-ring"></div>
        <div class="fsc-hub-line"></div>
      </div>
    </div>

    <!-- Floating stats panel (right side, desktop only) -->
    <div class="hero-float-stats">
      <div class="hfs-item">
        <div class="hfs-val counter-val" data-target="<?= count($websites) ?>" data-suffix="+">
          <span class="num"><?= count($websites) ?></span><span class="suffix">+</span>
        </div>
        <div class="hfs-key">Products</div>
      </div>
      <div class="hfs-divider"></div>
      <div class="hfs-item">
        <div class="hfs-val counter-val" data-target="9" data-suffix="">
          <span class="num">9</span><span class="suffix"></span>
        </div>
        <div class="hfs-key">Countries</div>
      </div>
      <div class="hfs-divider"></div>
      <div class="hfs-item">
        <div class="hfs-val counter-val" data-target="<?= $expYears ?>" data-suffix="yr">
          <span class="num"><?= $expYears ?></span><span class="suffix">yr</span>
        </div>
        <div class="hfs-key">Experience</div>
      </div>
      <div class="hfs-divider"></div>
      <div class="hfs-item">
        <div class="hfs-val"><?= $visitStats['today'] ?></div>
        <div class="hfs-key">Today</div>
      </div>
    </div>

  </div><!-- /hero-v3-wrap -->

  <!-- Scroll hint -->
  <div class="hero-scroll" onclick="document.querySelector('#sec-websites,section:not(#hero)')?.scrollIntoView({behavior:'smooth'})">
    <div class="scroll-mouse"><div class="scroll-wheel"></div></div>
    <span>Cuộn xuống</span>
  </div>
</section>

<?php foreach ($sections as $sec):
  if (!$sec['visible']) continue;
  $key = $sec['section_key'];
?>

<!-- ══════════════════════════════════════════════════════════════
     WEBSITES — Project Showcase V2
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='websites' && !empty($websites)): ?>
<section id="sec-websites">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-rocket"></i> Project Showcase</div>
      <h2 class="section-title reveal"><?= $secMap['websites']['title'] ?? 'Hệ Sinh Thái <span class="grad">Sản Phẩm</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['websites']['description'] ?? 'Các nền tảng và sản phẩm công nghệ đang vận hành trong hệ sinh thái') ?></p>
    </div>
    <div class="website-grid">
      <?php foreach ($websites as $idx => $web):
        $hasLogo  = !empty($web['logo']) && (filter_var($web['logo'],FILTER_VALIDATE_URL)||file_exists(BASE_PATH.'/'.ltrim($web['logo'],'/')));
        $hasThumb = !empty($web['thumbnail']??'') && (filter_var($web['thumbnail']??'',FILTER_VALIDATE_URL)||file_exists(BASE_PATH.'/'.ltrim($web['thumbnail']??'','/')));
        $mono2    = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/','', $web['name']), 0, 2));
        $techTags = array_filter(array_slice(preg_split('/[\s,;|]+/', $web['tech_stack']??$web['description']??''), 0, 5));
      ?>
      <a href="<?= e($web['link']) ?>" target="_blank" rel="noopener" class="website-card tilt-card reveal reveal-delay-<?= min($idx%3+1,5) ?>">
        <!-- Thumb / Preview -->
        <div class="wc-thumb">
          <?php if ($hasThumb): ?>
          <img src="<?= e($web['thumbnail']) ?>" alt="<?= e($web['name']) ?>" loading="lazy">
          <?php else: ?>
          <div class="wc-thumb-placeholder">
            <div class="wc-thumb-grid"></div>
            <div class="wc-thumb-monogram"><?= $mono2 ?></div>
          </div>
          <?php endif; ?>
          <div class="wc-overlay">
            <span class="wc-open-btn"><i class="fas fa-external-link-alt"></i> Truy Cập</span>
          </div>
          <div class="wc-status"><span class="wc-status-dot"></span>Live</div>
        </div>
        <!-- Card Body -->
        <div class="wc-body">
          <div class="wc-header">
            <div class="wc-logo-wrap">
              <?php if ($hasLogo): ?>
              <img src="<?= e($web['logo']) ?>" alt="<?= e($web['name']) ?>" loading="lazy">
              <?php else: ?>
              <div class="wc-logo-ph"><?= substr($mono2,0,1) ?></div>
              <?php endif; ?>
            </div>
            <div class="wc-info">
              <div class="wc-name"><?= e($web['name']) ?></div>
              <div class="wc-domain"><?= e($web['domain']) ?></div>
            </div>
            <div class="wc-arrow"><i class="fas fa-arrow-up-right-from-square"></i></div>
          </div>
          <div class="wc-desc"><?= e($web['description']) ?></div>
          <?php if (!empty($techTags)): ?>
          <div class="wc-tags">
            <?php foreach ($techTags as $t): if (!trim($t)) continue; ?>
            <span class="wc-tag"><?= e(trim($t)) ?></span>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
          <div class="wc-footer">
            <span class="wc-status-pill"><span class="wc-status-dot"></span> Trực tuyến</span>
            <span class="wc-visit-label">Xem dự án <i class="fas fa-chevron-right"></i></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════════
     GLOBAL PRESENCE MAP
     ══════════════════════════════════════════════════════════════ -->
<section id="sec-global-map">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-satellite-dish"></i> Infrastructure</div>
      <h2 class="section-title reveal"><?= $secMap['statistics']['title'] ?? 'Global <span class="grad">Presence</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['statistics']['description'] ?? 'Hệ thống kết nối và vận hành trên toàn cầu — dữ liệu thực tế từ các node mạng') ?></p>
    </div>
    <div class="global-map-wrap reveal">
      <!-- SVG World Map -->
      <div class="world-map-container">
        <canvas id="worldMapCanvas"></canvas>
        <!-- City overlay nodes -->
        <div class="map-nodes" id="mapNodes">
          <div class="map-node" style="--nx:72%;--ny:38%;" data-city="Việt Nam" data-status="primary">
            <span class="mn-dot"></span>
            <span class="mn-label">Việt Nam</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:72.5%;--ny:47%;" data-city="Singapore" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">Singapore</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:80%;--ny:30%;" data-city="Tokyo" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">Tokyo</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:78%;--ny:27%;" data-city="Seoul" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">Seoul</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:82%;--ny:68%;" data-city="Sydney" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">Sydney</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:49%;--ny:23%;" data-city="Frankfurt" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">Frankfurt</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:46%;--ny:20%;" data-city="London" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">London</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:21%;--ny:28%;" data-city="New York" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">New York</span>
            <span class="mn-ping"></span>
          </div>
          <div class="map-node" style="--nx:10%;--ny:30%;" data-city="California" data-status="active">
            <span class="mn-dot"></span>
            <span class="mn-label">California</span>
            <span class="mn-ping"></span>
          </div>
        </div>
        <!-- Connection lines drawn by JS -->
        <svg class="map-connections" id="mapConnections"></svg>
      </div>

      <!-- Map Stats Row -->
      <div class="map-stats-row">
        <div class="msr-item">
          <div class="msr-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div class="msr-val">9</div>
          <div class="msr-label">Countries</div>
        </div>
        <div class="msr-item">
          <div class="msr-icon"><i class="fas fa-network-wired"></i></div>
          <div class="msr-val">24/7</div>
          <div class="msr-label">Uptime</div>
        </div>
        <div class="msr-item">
          <div class="msr-icon"><i class="fas fa-bolt"></i></div>
          <div class="msr-val">&lt;50ms</div>
          <div class="msr-label">Latency</div>
        </div>
        <div class="msr-item">
          <div class="msr-icon"><i class="fas fa-shield-alt"></i></div>
          <div class="msr-val">SSL</div>
          <div class="msr-label">Secured</div>
        </div>
        <div class="msr-item">
          <div class="msr-icon"><i class="fas fa-satellite"></i></div>
          <div class="msr-val">CDN</div>
          <div class="msr-label">Distributed</div>
        </div>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     ABOUT ME
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='about' && !empty($s['about_me'])): ?>
<section id="sec-about">
  <div class="container">
    <div class="about-grid">
      <!-- Left: Avatar + Career Timeline -->
      <div class="about-left reveal-left">
        <div class="about-avatar-wrap">
          <?php if ($hasAvatar): ?>
          <img src="<?= e($avatar) ?>" alt="<?= e($s['name']??'') ?>" class="about-avatar-img" loading="lazy">
          <?php else: ?>
          <div class="about-avatar-ph"><span><?= $initials ?></span></div>
          <?php endif; ?>
          <div class="about-frame"></div>
        </div>
        <!-- Career Timeline -->
        <div class="career-timeline">
          <?php
          $timelineData = [
            [$startYear . '', 'Bắt Đầu Hành Trình', 'Học lập trình và xây dựng những dự án đầu tiên.'],
            [($startYear+1) . '', 'Freelance Developer', 'Nhận dự án thực tế, tích lũy kinh nghiệm thực chiến.'],
            [($startYear+2) . '', 'Fullstack Developer', 'Phát triển web app, API, hệ thống quản trị chuyên nghiệp.'],
            [($startYear+$expYears-2) . '', 'Lead Developer', 'Dẫn dắt team, thiết kế kiến trúc hệ thống phức tạp.'],
            [date('Y') . '', 'Founder & System Architect', 'Xây dựng hệ sinh thái sản phẩm SaaS hoạt động thực tế.'],
          ];
          foreach ($timelineData as [$yr, $ttl, $dsc]):
          ?>
          <div class="ct-item">
            <div class="ct-dot"></div>
            <div class="ct-content">
              <div class="ct-year"><?= $yr ?></div>
              <div class="ct-title"><?= $ttl ?></div>
              <div class="ct-desc"><?= $dsc ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Right: Bio + Philosophy -->
      <div class="about-right reveal-right">
        <div class="section-eyebrow" style="margin-bottom:16px;"><i class="fas fa-user"></i> Về Tôi</div>
        <h2 class="section-title" style="margin-bottom:12px;"><?= $secMap['about']['title'] ?? 'Tôi Là <span class="grad">Ai?</span>' ?></h2>
        <p style="color:var(--text-2);margin-bottom:24px;font-size:15px;"><?= e($secMap['about']['description'] ?? 'Giới thiệu ngắn gọn về bản thân và định hướng') ?></p>
        <div class="about-text">
          <p><?= nl2br(e($s['about_me'] ?? '')) ?></p>
        </div>

        <!-- Meta info -->
        <div class="about-meta">
          <?php $metaItems = [
            ['fas fa-calendar-alt', $s['experience']??'5+ Năm', 'Kinh nghiệm'],
            ['fas fa-map-marker-alt', $s['address']??'Việt Nam', 'Vị trí'],
            ['fas fa-envelope', $s['email']??'—', 'Email'],
            ['fas fa-phone', $s['phone']??'—', 'Điện thoại'],
          ]; ?>
          <?php foreach ($metaItems as [$icon,$val,$label]): ?>
          <div class="about-meta-item">
            <div class="ami-icon"><i class="<?= $icon ?>"></i></div>
            <div class="ami-text">
              <strong><?= e($val) ?></strong>
              <span><?= e($label) ?></span>
            </div>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Philosophy -->
        <div class="philosophy-grid">
          <div class="phil-item tilt-card">
            <div class="phil-icon">⚡</div>
            <div class="phil-title">Triết Lý Làm Việc</div>
            <div class="phil-text">Tập trung vào chất lượng, không phải số lượng. Mỗi dòng code là một cam kết.</div>
          </div>
          <div class="phil-item tilt-card">
            <div class="phil-icon">🚀</div>
            <div class="phil-title">Công Nghệ Yêu Thích</div>
            <div class="phil-text">PHP, Laravel, MySQL và hệ sinh thái Linux Server. Đơn giản, ổn định, hiệu quả.</div>
          </div>
          <div class="phil-item tilt-card">
            <div class="phil-icon">🎯</div>
            <div class="phil-title">Định Hướng</div>
            <div class="phil-text">Xây dựng sản phẩm thực tế, tạo ra giá trị cho người dùng cuối.</div>
          </div>
          <div class="phil-item tilt-card">
            <div class="phil-icon">🌱</div>
            <div class="phil-title">Học Hỏi</div>
            <div class="phil-text">Luôn cập nhật công nghệ mới, không ngừng cải thiện bản thân mỗi ngày.</div>
          </div>
        </div>

        <?php if (!empty($s['goals'])): ?>
        <div class="about-goals">
          <div class="about-goals-label"><i class="fas fa-rocket" style="margin-right:6px;"></i>Mục Tiêu</div>
          <p><?= e($s['goals']) ?></p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     SOCIAL
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='social' && !empty($socials)): ?>
<section id="sec-social" class="tight">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-share-alt"></i> Mạng Xã Hội</div>
      <h2 class="section-title reveal"><?= $secMap['social']['title'] ?? 'Kết Nối <span class="grad">Với Tôi</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['social']['description'] ?? 'Theo dõi tôi trên các nền tảng để cập nhật dự án mới nhất') ?></p>
    </div>
    <div class="social-grid">
      <?php foreach ($socials as $idx => $soc):
        $brand = $socialBrand[$soc['platform']] ?? ['icon_color'=>'#6366F1','bg'=>'rgba(99,102,241,0.1)','border'=>'rgba(99,102,241,0.3)','glow'=>'rgba(99,102,241,0.2)'];
        $color = !empty($soc['color']) ? $soc['color'] : $brand['icon_color'];
      ?>
      <a href="<?= e($soc['link']) ?>" target="_blank" rel="noopener"
         class="social-card tilt-card reveal reveal-delay-<?= min($idx%5+1,5) ?>"
         style="--sc-color:<?= e($color) ?>;--sc-bg:<?= $brand['bg'] ?>;--sc-border-light:<?= $brand['border'] ?>;--sc-border:<?= $brand['border'] ?>;--sc-glow:<?= $brand['glow'] ?>;">
        <div class="sc-icon"><i class="<?= e($soc['icon_class']) ?>"></i></div>
        <div class="sc-name"><?= e($soc['platform']) ?></div>
        <?php if (!empty($soc['username'])): ?><div class="sc-user"><?= e($soc['username']) ?></div><?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     STATISTICS
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='statistics' && !empty($statistics)): ?>
<section id="sec-statistics" class="tight">
  <div class="container">
    <div class="stats-bar reveal">
      <?php foreach ($statistics as $idx => $stat): if ($idx>=4) break; ?>
      <div class="stat-item">
        <div class="stat-icon-wrap"><i class="<?= e($stat['icon_class']) ?>"></i></div>
        <div class="stat-number counter-val"
             data-target="<?= e(preg_replace('/[^0-9.]/','',$stat['value'])) ?>"
             data-suffix="<?= e($stat['suffix']) ?>">
          <span class="num"><?= e($stat['value']) ?></span><span class="suffix"><?= e($stat['suffix']) ?></span>
        </div>
        <div class="stat-label"><?= e($stat['label']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     TECH STACK
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='skills' && !empty($skills)): ?>
<section id="sec-skills">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-code"></i> Công Nghệ</div>
      <h2 class="section-title reveal"><?= $secMap['skills']['title'] ?? 'Tech <span class="grad">Stack</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['skills']['description'] ?? 'Công nghệ và công cụ tôi sử dụng hàng ngày để xây dựng sản phẩm') ?></p>
    </div>
    <div class="skills-cloud">
      <?php
      $floatDurs = [3.5,4,4.5,3,5,3.8,4.2,5.5,3.2,4.8,3.6,4.4];
      foreach ($skills as $idx => $skill):
        $dur   = $floatDurs[$idx % count($floatDurs)];
        $delay = round(($idx*0.3)%3,1);
        $hasLogo = !empty($skill['logo']) && (filter_var($skill['logo'],FILTER_VALIDATE_URL)||file_exists(BASE_PATH.'/'.ltrim($skill['logo'],'/')));
      ?>
      <div class="skill-tag reveal" style="--float-dur:<?= $dur ?>s;--float-delay:-<?= $delay ?>s;">
        <span class="st-icon">
          <?php if ($hasLogo): ?>
          <img src="<?= e($skill['logo']) ?>" alt="<?= e($skill['name']) ?>" loading="lazy">
          <?php else: ?>
          <span class="st-letter"><?= strtoupper(substr($skill['name'],0,2)) ?></span>
          <?php endif; ?>
        </span>
        <?= e($skill['name']) ?>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="skills-bars">
      <?php foreach ($skills as $skill): ?>
      <div class="skill-bar-item reveal">
        <div class="skill-bar-header">
          <span class="skill-bar-name"><?= e($skill['name']) ?></span>
          <span class="skill-bar-pct"><?= (int)$skill['level'] ?>%</span>
        </div>
        <div class="skill-bar-track">
          <div class="skill-bar-fill" data-level="<?= (int)$skill['level'] ?>"></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     PAYMENT
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='payment' && !empty($banks)): ?>
<section id="sec-payment" class="tight">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-credit-card"></i> Thanh Toán</div>
      <h2 class="section-title reveal"><?= $secMap['payment']['title'] ?? 'Thông Tin <span class="grad">Ngân Hàng</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['payment']['description'] ?? 'Phương thức thanh toán và giao dịch an toàn') ?></p>
    </div>
    <div class="payment-grid">
      <?php foreach ($banks as $idx => $bank):
        $hasLogo = !empty($bank['logo']) && (filter_var($bank['logo'],FILTER_VALIDATE_URL)||file_exists(BASE_PATH.'/'.ltrim($bank['logo'],'/')));
      ?>
      <div class="bank-card reveal reveal-delay-<?= min($idx%3+1,5) ?>" onclick="copyBankNumber('<?= e($bank['account_number']) ?>')">
        <div class="bc-top">
          <?php if ($hasLogo): ?>
          <img src="<?= e($bank['logo']) ?>" alt="<?= e($bank['bank_short']) ?>" class="bc-logo-img">
          <?php else: ?>
          <div class="bc-logo-text"><?= e($bank['bank_short']?:$bank['bank_name']) ?></div>
          <?php endif; ?>
          <div class="bc-chip"></div>
        </div>
        <div class="bc-number" title="Click để sao chép">
          <?= e(trim(chunk_split($bank['account_number'], 4, ' '))) ?>
        </div>
        <div class="bc-bottom">
          <div>
            <div class="bc-holder-label">Chủ tài khoản</div>
            <div class="bc-holder-name"><?= e($bank['account_holder']) ?></div>
          </div>
          <button class="bc-copy" onclick="event.stopPropagation();copyBankNumber('<?= e($bank['account_number']) ?>')">
            <i class="fas fa-copy"></i> Sao chép
          </button>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     ACHIEVEMENTS
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='achievements' && !empty($achievements)): ?>
<section id="sec-achievements" class="tight">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-trophy"></i> Thành Tựu</div>
      <h2 class="section-title reveal">Những Gì <span class="grad">Đã Đạt Được</span></h2>
    </div>
    <div class="achievements-grid">
      <?php foreach ($achievements as $idx => $ach): ?>
      <div class="achievement-card tilt-card reveal reveal-delay-<?= min($idx%3+1,5) ?>">
        <div class="ach-icon-wrap"><i class="<?= e($ach['icon_class']) ?>"></i></div>
        <div class="ach-title"><?= e($ach['title']) ?></div>
        <?php if (!empty($ach['content'])): ?>
        <div class="ach-desc"><?= e($ach['content']) ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     SERVICES
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='services' && !empty($services)): ?>
<section id="sec-services">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-briefcase"></i> Dịch Vụ</div>
      <h2 class="section-title reveal"><?= $secMap['services']['title'] ?? 'Tôi Có Thể <span class="grad">Giúp Gì?</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['services']['description'] ?? 'Các dịch vụ chuyên nghiệp tôi cung cấp để giải quyết vấn đề của bạn') ?></p>
    </div>
    <div class="services-grid">
      <?php foreach ($services as $idx => $srv): ?>
      <a href="<?= e($srv['link']?:'#sec-contact') ?>" class="service-card tilt-card reveal reveal-delay-<?= min($idx%4+1,5) ?> <?= $idx===0?'featured':'' ?>">
        <div class="srv-icon"><i class="<?= e($srv['icon_class']) ?>"></i></div>
        <div class="srv-name"><?= e($srv['name']) ?></div>
        <div class="srv-desc"><?= e($srv['description']) ?></div>
        <?php if (!empty($srv['price'])): ?>
        <div class="srv-price"><i class="fas fa-tag"></i> <?= e($srv['price']) ?></div>
        <?php endif; ?>
        <div class="srv-cta">Tìm hiểu thêm <i class="fas fa-arrow-right" style="font-size:11px;"></i></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ══════════════════════════════════════════════════════════════
     TESTIMONIALS
     ══════════════════════════════════════════════════════════════ -->
<?php if ($key==='reviews' && !empty($testimonials)): ?>
<section id="sec-reviews">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-star"></i> Đánh Giá</div>
      <h2 class="section-title reveal"><?= $secMap['reviews']['title'] ?? 'Khách Hàng <span class="grad">Nói Gì?</span>' ?></h2>
      <p class="section-desc reveal"><?= e($secMap['reviews']['description'] ?? 'Đánh giá chân thực từ đối tác và khách hàng đã làm việc cùng') ?></p>
    </div>
    <div class="reviews-wrapper reveal">
      <div class="reviews-track" id="reviewsTrack">
        <?php foreach ($testimonials as $test): ?>
        <div class="review-slide">
          <div class="review-card">
            <div class="rc-quote">"</div>
            <div class="rc-stars">
              <?php for ($i=0;$i<5;$i++): ?>
              <i class="fas fa-star" style="<?= $i<$test['rating']?'color:#F59E0B':'color:var(--border-bright)' ?>"></i>
              <?php endfor; ?>
            </div>
            <div class="rc-text"><?= e($test['review']) ?></div>
            <div class="rc-footer">
              <div class="rc-avatar">
                <?php $hasAv = !empty($test['avatar']) && (filter_var($test['avatar'],FILTER_VALIDATE_URL)||file_exists(BASE_PATH.'/'.ltrim($test['avatar'],'/'))); ?>
                <?php if ($hasAv): ?><img src="<?= e($test['avatar']) ?>" alt="<?= e($test['name']) ?>" loading="lazy">
                <?php else: ?><div class="rc-avatar-ph"><?= strtoupper(mb_substr($test['name'],0,1)) ?></div>
                <?php endif; ?>
              </div>
              <div><div class="rc-name"><?= e($test['name']) ?></div><div class="rc-pos"><?= e($test['position']) ?></div></div>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <div class="carousel-controls">
        <button class="carousel-btn" id="prevBtn"><i class="fas fa-chevron-left"></i></button>
        <div class="carousel-dots" id="carouselDots"></div>
        <button class="carousel-btn" id="nextBtn"><i class="fas fa-chevron-right"></i></button>
      </div>
    </div>
  </div>
</section>
<?php endif; ?>

<?php endforeach; ?>

<!-- ══════════════════════════════════════════════════════════════
     CONTACT
     ══════════════════════════════════════════════════════════════ -->
<section id="sec-contact">
  <div class="container">
    <div class="section-header-center">
      <div class="section-eyebrow reveal"><i class="fas fa-envelope"></i> Liên Hệ</div>
      <h2 class="section-title reveal">Hãy <span class="grad">Liên Hệ Tôi</span></h2>
      <p class="section-desc reveal">Để lại tin nhắn — Tôi sẽ phản hồi trong vòng 24 giờ</p>
    </div>
    <div class="contact-grid">
      <div class="contact-info reveal-left">
        <?php if (!empty($s['email'])): ?>
        <div class="contact-info-item">
          <div class="cii-icon"><i class="fas fa-envelope"></i></div>
          <div><div class="cii-label">Email</div><div class="cii-value"><a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></div></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($s['phone'])): ?>
        <div class="contact-info-item">
          <div class="cii-icon"><i class="fas fa-phone"></i></div>
          <div><div class="cii-label">Điện Thoại</div><div class="cii-value"><a href="tel:<?= e(preg_replace('/[^0-9+]/','', $s['phone'])) ?>"><?= e($s['phone']) ?></a></div></div>
        </div>
        <?php endif; ?>
        <?php if (!empty($s['address'])): ?>
        <div class="contact-info-item">
          <div class="cii-icon"><i class="fas fa-map-marker-alt"></i></div>
          <div><div class="cii-label">Địa Chỉ</div><div class="cii-value"><?= e($s['address']) ?></div></div>
        </div>
        <?php endif; ?>
        <div class="contact-socials">
          <?php foreach ($socials as $soc): $brand=$socialBrand[$soc['platform']]??[]; $ic=$brand['icon_color']??'#6366F1'; ?>
          <a href="<?= e($soc['link']) ?>" target="_blank" rel="noopener" class="cs-btn" style="--btn-color:<?= $ic ?>;">
            <i class="<?= e($soc['icon_class']) ?>" style="color:<?= $ic ?>;"></i> <?= e($soc['platform']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="reveal-right">
        <div class="contact-form-wrap">
          <form id="contact-form" novalidate>
            <?= csrfField() ?>
            <input type="text" name="website" style="position:absolute;left:-9999px;opacity:0;" tabindex="-1" autocomplete="off">
            <div class="form-row" style="margin-bottom:16px;">
              <div class="form-group">
                <label class="form-label">Họ tên <span class="req">*</span></label>
                <input type="text" name="name" class="form-control" placeholder="Nguyễn Văn A" required>
              </div>
              <div class="form-group">
                <label class="form-label">Email <span class="req">*</span></label>
                <input type="email" name="email" class="form-control" placeholder="you@example.com" required>
              </div>
            </div>
            <div class="form-group" style="margin-bottom:16px;">
              <label class="form-label">Số điện thoại</label>
              <input type="tel" name="phone" class="form-control" placeholder="0901 234 567">
            </div>
            <div class="form-group" style="margin-bottom:20px;">
              <label class="form-label">Nội dung <span class="req">*</span></label>
              <textarea name="message" class="form-control" rows="5" placeholder="Tôi muốn liên hệ về..." required></textarea>
            </div>
            <button type="submit" class="form-submit magnetic" id="contact-submit">
              <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════════════════════
     FOOTER
     ══════════════════════════════════════════════════════════════ -->
<footer>
  <div class="container">
    <div class="footer-inner">
      <div>
        <div class="footer-logo"><div class="logo-mark"><i class="fas fa-bolt"></i></div><?= e($s['name']??'Profile') ?></div>
        <p class="footer-tagline"><?= e($s['tagline']??$s['title']??'') ?></p>
      </div>
      <div class="footer-col">
        <h4>Điều Hướng</h4>
        <div class="footer-links">
          <a href="#hero">Trang Chủ</a>
          <a href="#sec-about">Về Tôi</a>
          <a href="#sec-services">Dịch Vụ</a>
          <a href="#sec-contact">Liên Hệ</a>
        </div>
      </div>
      <div class="footer-col">
        <h4>Mạng Xã Hội</h4>
        <div class="footer-links">
          <?php foreach (array_slice($socials,0,5) as $soc): ?>
          <a href="<?= e($soc['link']) ?>" target="_blank" rel="noopener">
            <i class="<?= e($soc['icon_class']) ?>" style="width:14px;"></i> <?= e($soc['platform']) ?>
          </a>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <div class="footer-copy">&copy; <?= date('Y') ?> <span><?= e($s['name']??'') ?></span>. Bảo lưu mọi quyền.</div>
      <div class="footer-bottom-socials">
        <?php foreach (array_slice($socials,0,5) as $soc): $brand=$socialBrand[$soc['platform']]??[]; $fc=$brand['icon_color']??'#6366F1'; ?>
        <a href="<?= e($soc['link']) ?>" target="_blank" rel="noopener" class="fbs-link" title="<?= e($soc['platform']) ?>">
          <i class="<?= e($soc['icon_class']) ?>" style="color:<?= $fc ?>;"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <!-- BRANDING WATERMARK -->
    <div style="text-align: center; margin-top: 16px; font-size: 13px; color: rgba(255,255,255,0.4);">
      Developed by <a href="<?= PROJECT_WEBSITE ?>" target="_blank" style="color: rgba(255,255,255,0.6); text-decoration: none; font-weight: 500; transition: color 0.3s;" onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='rgba(255,255,255,0.6)'"><?= PROJECT_AUTHOR ?></a>
    </div>
  </div>
</footer>

<!-- PWA Banner -->
<div id="pwa-install-banner">
  <div class="pwa-title"><i class="fas fa-mobile-alt" style="color:var(--accent);margin-right:6px;"></i>Cài Đặt Ứng Dụng</div>
  <div class="pwa-desc">Thêm vào màn hình chính để truy cập nhanh hơn!</div>
  <div class="pwa-actions">
    <button class="btn-pwa-install" id="pwa-install-btn"><i class="fas fa-download"></i> Cài đặt</button>
    <button class="btn-pwa-dismiss" id="pwa-dismiss-btn">Bỏ qua</button>
  </div>
</div>
<button id="back-to-top" aria-label="Lên đầu trang"><i class="fas fa-arrow-up"></i></button>

<script>
const TYPEWRITER_TEXTS = <?= json_encode($typewriterTitles, JSON_UNESCAPED_UNICODE) ?>;
</script>
<script src="assets/js/earth-engine.js"></script>
<script src="assets/js/main.js"></script>
<?php if (!empty($s['footer_script'])): ?><?= $s['footer_script'] ?><?php endif; ?>
<?php if (!empty($s['ga_code'])): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($s['ga_code']) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e($s['ga_code']) ?>');</script>
<?php endif; ?>
</body>
</html>
