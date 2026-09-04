<?php
// ============================================================
// ADMIN — Live Theme Customizer
// ============================================================
require_once dirname(__DIR__) . '/includes/auth.php';
require_once dirname(__DIR__) . '/includes/crud.php';
requireAuth();

$pageTitle = 'Live Theme Customizer';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    header('Content-Type: application/json');

    if ($_POST['action'] === 'save_theme') {
        $themeKeys = ['accent_color','accent_secondary','bg_color','card_color','border_color','text_color','text_secondary'];
        foreach ($themeKeys as $k) {
            if (isset($_POST[$k]) && preg_match('/^#[0-9A-Fa-f]{6}$/', $_POST[$k])) {
                updateSetting($k, $_POST[$k]);
            }
        }
        echo json_encode(['success' => true, 'message' => 'Theme đã được cập nhật!']);
        exit;
    }

    if ($_POST['action'] === 'reset_theme') {
        $defaults = [
            'accent_color' => '#6366F1',
            'accent_secondary' => '#8B5CF6',
            'bg_color' => '#050505',
            'card_color' => '#0D0D0D',
            'border_color' => '#1A1A1A',
            'text_color' => '#FFFFFF',
            'text_secondary' => '#B8B8B8',
        ];
        foreach ($defaults as $k => $v) updateSetting($k, $v);
        echo json_encode(['success' => true, 'message' => 'Đã reset về mặc định!', 'defaults' => $defaults]);
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
<div class="admin-main"><div class="content-area">

<div class="theme-customizer">
  <!-- Settings Panel -->
  <div class="theme-settings">
    <div style="margin-bottom:20px;">
      <h3 style="font-size:18px;font-weight:700;margin-bottom:4px;">Live Theme Customizer</h3>
      <div style="font-size:12px;color:var(--text-3);">Thay đổi giao diện realtime</div>
    </div>
    
    <form id="themeForm">
      <?= csrfField() ?>
      <input type="hidden" name="action" value="save_theme">

      <?php
        $colorFields = [
          ['accent_color', 'Accent Chính', 'Màu chủ đạo (glow, badge)'],
          ['accent_secondary', 'Accent Phụ', 'Màu gradient phụ'],
          ['bg_color', 'Nền Trang', 'Background toàn trang'],
          ['card_color', 'Màu Card', 'Background các card'],
          ['border_color', 'Màu Border', 'Đường viền mặc định'],
          ['text_color', 'Chữ Chính', 'Tiêu đề, tên'],
          ['text_secondary', 'Chữ Phụ', 'Mô tả, thứ cấp'],
        ];
        foreach ($colorFields as [$key, $label, $hint]):
          $val = $s[$key] ?? '#6366F1';
      ?>
      <div class="form-group">
        <label class="form-label"><?=e($label)?> <span class="label-hint"><?=e($hint)?></span></label>
        <div class="color-picker-wrap" id="picker_<?=$key?>">
          <input type="color" name="<?=$key?>" id="color_<?=$key?>" value="<?=e($val)?>" oninput="livePreview('<?=$key?>',this.value)">
          <input type="text" class="color-hex" value="<?=strtoupper($val)?>" maxlength="7" onchange="syncColorPicker('<?=$key?>',this.value)">
        </div>
      </div>
      <?php endforeach; ?>

      <div style="margin-top:20px;margin-bottom:24px;">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-bottom:10px;"><i class="fas fa-save"></i> Lưu Theme</button>
        <button type="button" class="btn btn-secondary" style="width:100%;justify-content:center;" onclick="resetTheme()"><i class="fas fa-undo"></i> Reset Default</button>
      </div>
    </form>

    <div style="border-top:1px solid var(--border);padding-top:20px;">
      <div style="font-size:14px;font-weight:600;margin-bottom:12px;">Presets</div>
      <div style="display:flex;flex-direction:column;gap:8px;">
        <?php
          $presets = [
            ['Cyber Indigo', '#6366F1', '#8B5CF6', '#050505'],
            ['Electric Blue', '#3B82F6', '#06B6D4', '#040812'],
            ['Emerald Green', '#10B981', '#059669', '#030D09'],
            ['Rose Red', '#F43F5E', '#E11D48', '#0D0305'],
            ['Amber Gold', '#F59E0B', '#D97706', '#0D0A03'],
            ['Violet Dream', '#A855F7', '#7C3AED', '#08040D'],
          ];
          foreach ($presets as [$name, $c1, $c2, $bg]):
        ?>
        <button class="btn btn-secondary" onclick="applyPreset('<?=$c1?>','<?=$c2?>','<?=$bg?>')" style="justify-content:flex-start;gap:12px;">
          <span style="display:flex;gap:4px;">
            <span style="width:14px;height:14px;border-radius:50%;background:<?=$c1?>;display:inline-block;"></span>
            <span style="width:14px;height:14px;border-radius:50%;background:<?=$c2?>;display:inline-block;"></span>
          </span>
          <?=e($name)?>
        </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Live Preview -->
  <div class="theme-preview">
    <iframe id="previewFrame" src="<?=BASE_URL?>"></iframe>
  </div>
</div>

</div></div>
<script src="<?=ADMIN_URL?>assets/js/admin.js"></script>
<script>
const frame = document.getElementById('previewFrame');
let frameReady = false;
frame.addEventListener('load', () => { frameReady = true; });

function livePreview(key, value) {
  // Update CSS var map
  const varMap = {
    accent_color: '--accent',
    accent_secondary: '--accent-2',
    bg_color: '--bg',
    card_color: '--card',
    border_color: '--border',
    text_color: '--text',
    text_secondary: '--text-2',
  };
  if (varMap[key] && frameReady) {
    try {
      frame.contentDocument.documentElement.style.setProperty(varMap[key], value);
    } catch(e) {}
  }
  // Also update hex input
  const hexInput = document.querySelector(`#picker_${key} .color-hex`);
  if (hexInput) hexInput.value = value.toUpperCase();
}

function syncColorPicker(key, value) {
  if (/^#[0-9A-Fa-f]{6}$/.test(value)) {
    const colorInput = document.getElementById('color_' + key);
    if (colorInput) { colorInput.value = value; livePreview(key, value); }
  }
}

function applyPreset(c1, c2, bg) {
  const fields = { accent_color: c1, accent_secondary: c2, bg_color: bg };
  Object.entries(fields).forEach(([k, v]) => {
    const colorInput = document.getElementById('color_' + k);
    const hexInput = document.querySelector(`#picker_${k} .color-hex`);
    if (colorInput) colorInput.value = v;
    if (hexInput) hexInput.value = v.toUpperCase();
    livePreview(k, v);
  });
  AdminJS.toast('info', 'Theme đã áp dụng!', 'Nhấn Lưu để áp dụng vĩnh viễn.', 3000);
}

async function resetTheme() {
  if (!confirm('Reset về theme mặc định?')) return;
  const fd = new FormData();
  fd.append('action', 'reset_theme');
  fd.append('_csrf_token', AdminJS.csrf);
  const res = await fetch(location.href, {method:'POST',body:fd,credentials:'same-origin'});
  const data = await res.json();
  if (data.success) {
    AdminJS.toast('success', 'Đã reset!', data.message);
    if (data.defaults) Object.entries(data.defaults).forEach(([k,v]) => { const ci=document.getElementById('color_'+k);const hi=document.querySelector(`#picker_${k} .color-hex`);if(ci)ci.value=v;if(hi)hi.value=v.toUpperCase();livePreview(k,v); });
    setTimeout(()=>location.reload(),1200);
  }
}

document.getElementById('themeForm').addEventListener('submit', function(e) {
  e.preventDefault();
  AdminJS.submitForm('themeForm', location.href, () => {
    AdminJS.toast('success','Theme đã lưu!','Trang frontend sẽ cập nhật ngay.');
    setTimeout(()=>frame.src=frame.src,500);
  });
});
</script>
</body></html>
