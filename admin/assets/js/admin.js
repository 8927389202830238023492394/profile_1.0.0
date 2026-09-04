/* ============================================================
   ADMIN PANEL — JavaScript (AJAX CRUD, Toast, Modal, Sort)
   ============================================================ */
'use strict';
// --- BRANDING CONSOLE ---
console.log("%c======================================", "color: #6366F1; font-weight: bold;");
console.log("%c Le Vu Phong Ecosystem ", "color: white; background: #6366F1; font-size: 16px; font-weight: bold; padding: 4px; border-radius: 4px;");
console.log("%c Website: https://dichvugiare.net ", "color: #10B981; font-size: 14px;");
console.log("%c Zalo: 0855550612 ", "color: #3B82F6; font-size: 14px;");
console.log("%c======================================", "color: #6366F1; font-weight: bold;");
// ------------------------

const AdminJS = {
  csrf: '',

  // ── Init ─────────────────────────────────────────────────
  init() {
    // Get CSRF from any form on the page
    const csrfInput = document.querySelector('input[name="_csrf_token"]');
    if (csrfInput) this.csrf = csrfInput.value;

    this.initSidebar();
    this.initModals();
    this.initSearch();
    this.initToggles();
    this.initSortable();
    this.initImagePickers();
    this.initDropzone();
    this.initBulkActions();
  },

  // ── Sidebar Mobile ────────────────────────────────────────
  initSidebar() {
    const toggle  = document.getElementById('mobileToggle');
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('sidebarOverlay');
    if (!toggle || !sidebar) return;

    toggle.addEventListener('click', () => {
      sidebar.classList.toggle('open');
      overlay.classList.toggle('show');
    });
    overlay?.addEventListener('click', () => {
      sidebar.classList.remove('open');
      overlay.classList.remove('show');
    });
  },

  // ── Toast Notifications ────────────────────────────────────
  toast(type, title, message, duration = 4000) {
    const container = document.getElementById('admin-toast-container');
    if (!container) return;

    const configs = {
      success: { icon: 'fa-check-circle', color: '#10B981', bg: 'rgba(16,185,129,0.1)' },
      error:   { icon: 'fa-times-circle', color: '#EF4444', bg: 'rgba(239,68,68,0.1)' },
      warning: { icon: 'fa-exclamation-triangle', color: '#F59E0B', bg: 'rgba(245,158,11,0.1)' },
      info:    { icon: 'fa-info-circle', color: '#6366F1', bg: 'rgba(99,102,241,0.1)' },
    };
    const cfg = configs[type] || configs.info;

    const toast = document.createElement('div');
    toast.className = 'admin-toast';
    toast.style.setProperty('--toast-color', cfg.color);
    toast.style.setProperty('--toast-bg', cfg.bg);
    // [SEC] Build DOM nodes manually to prevent XSS from data.message
    const iconEl = document.createElement('div'); iconEl.className = 'toast-icon'; iconEl.innerHTML = `<i class="fas ${cfg.icon}"></i>`;
    const bodyEl = document.createElement('div'); bodyEl.className = 'toast-body';
    const titleEl = document.createElement('div'); titleEl.className = 'toast-title'; titleEl.textContent = title;
    bodyEl.appendChild(titleEl);
    if (message) { const msgEl = document.createElement('div'); msgEl.className = 'toast-msg'; msgEl.textContent = message; bodyEl.appendChild(msgEl); }
    const closeEl = document.createElement('span'); closeEl.className = 'toast-close'; closeEl.innerHTML = '<i class="fas fa-times"></i>'; closeEl.onclick = () => toast.remove();
    toast.appendChild(iconEl); toast.appendChild(bodyEl); toast.appendChild(closeEl);
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.animation = 'none';
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(30px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  // ── AJAX Helper ──────────────────────────────────────────
  async ajax({ url, data = {}, method = 'POST' }) {
    const fd = new FormData();
    fd.append('_csrf_token', this.csrf);
    Object.entries(data).forEach(([k, v]) => fd.append(k, v));
    try {
      const res = await fetch(url, { method, body: fd, credentials: 'same-origin' });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      return await res.json();
    } catch (e) {
      this.toast('error', 'Lỗi kết nối', e.message);
      throw e;
    }
  },

  // ── Form Helpers (V4 Audit) ──────────────────────────────
  async submitForm(formId, url, callback = null) {
    const form = document.getElementById(formId);
    if (!form) return;

    // Validation
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    const submitBtn = form.querySelector('button[type="submit"]');
    if (submitBtn) {
      if (submitBtn.classList.contains('btn-loading')) return; // prevent double submit
      submitBtn.classList.add('btn-loading');
      // optional: change text to "Đang lưu..." if it's text node, but our CSS handles the spinner over the text.
    }

    const fd = new FormData(form);
    fd.append('_csrf_token', this.csrf);
    
    try {
      const res = await fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' });
      if (!res.ok) throw new Error(`HTTP ${res.status}`);
      const data = await res.json();
      
      if (data.success) {
        this.toast('success', 'Thành công!', data.message || 'Dữ liệu đã được lưu.');
        if (callback) callback(data);
        else setTimeout(() => location.reload(), 1000);
      } else {
        this.toast('error', 'Lỗi!', data.message || 'Không thể lưu dữ liệu.');
      }
    } catch (e) {
      this.toast('error', 'Lỗi kết nối', e.message);
    } finally {
      if (submitBtn) submitBtn.classList.remove('btn-loading');
    }
  },

  fillForm(formId, data) {
    const form = document.getElementById(formId);
    if (!form) return;
    Object.keys(data).forEach(key => {
      const input = form.elements[key];
      if (input) {
        if (input.type === 'checkbox') input.checked = !!data[key];
        else input.value = data[key];
      }
    });
  },

  confirmModal(title, message, type = 'warning', onConfirm) {
    const existing = document.getElementById('adminConfirmModal');
    if (existing) existing.remove();

    const overlay = document.createElement('div');
    overlay.id = 'adminConfirmModal';
    overlay.className = 'modal-overlay show';
    overlay.innerHTML = `
      <div class="modal-box admin-confirm-modal ${type === 'warning' ? 'acm-warning' : ''}" style="max-width: 400px;">
        <div class="acm-icon"><i class="fas ${type === 'warning' ? 'fa-exclamation-triangle' : 'fa-info-circle'}"></i></div>
        <h3>${title}</h3>
        <p>${message}</p>
        <div class="acm-actions">
          <button class="btn btn-secondary" onclick="this.closest('.modal-overlay').remove()">Hủy</button>
          <button class="btn btn-primary" id="confirmBtnAction">${type === 'warning' ? 'Xác nhận' : 'Đồng ý'}</button>
        </div>
      </div>
    `;
    document.body.appendChild(overlay);

    document.getElementById('confirmBtnAction').addEventListener('click', () => {
      const btn = document.getElementById('confirmBtnAction');
      btn.classList.add('btn-loading');
      onConfirm(overlay);
    });
  },

  deleteItem(table, id, url) {
    this.confirmModal('Xác nhận xóa?', 'Hành động này không thể hoàn tác. Dữ liệu sẽ bị xóa vĩnh viễn.', 'warning', async (modal) => {
      try {
        const res = await this.ajax({
          url: url,
          data: { action: 'delete', table, id }
        });
        if (res.success) {
          this.toast('success', 'Đã xóa!', res.message || 'Xóa dữ liệu thành công.');
          setTimeout(() => location.reload(), 800);
        } else {
          this.toast('error', 'Lỗi!', res.message || 'Không thể xóa dữ liệu.');
          modal.remove();
        }
      } catch (e) {
        modal.remove();
      }
    });
  },

  // ── Modal System ──────────────────────────────────────────
  initModals() {
    // Open modal
    document.addEventListener('click', e => {
      const trigger = e.target.closest('[data-modal]');
      if (trigger) {
        const id = trigger.dataset.modal;
        this.openModal(id);
      }
      const close = e.target.closest('[data-modal-close], .modal-close, .modal-overlay');
      if (close && (close.dataset.modalClose !== undefined || close.classList.contains('modal-close') || close.classList.contains('modal-overlay'))) {
        if (close.classList.contains('modal-overlay')) {
          // Only close if clicking backdrop itself (not inner box)
          if (e.target === close) this.closeAllModals();
        } else {
          this.closeAllModals();
        }
      }
    });
    document.addEventListener('keydown', e => {
      if (e.key === 'Escape') this.closeAllModals();
    });
  },

  openModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.add('show');
      document.body.style.overflow = 'hidden';
    }
  },

  closeModal(id) {
    const overlay = document.getElementById(id);
    if (overlay) {
      overlay.classList.remove('show');
      document.body.style.overflow = '';
    }
  },

  closeAllModals() {
    document.querySelectorAll('.modal-overlay.show').forEach(m => {
      m.classList.remove('show');
    });
    document.body.style.overflow = '';
  },

  // ── Search with Debounce ──────────────────────────────────
  initSearch() {
    const searchInputs = document.querySelectorAll('.admin-search input');
    searchInputs.forEach(input => {
      let timer;
      input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
          const form = input.closest('form') || document.querySelector('.search-form');
          if (form) {
            const url = new URL(form.action || location.href);
            url.searchParams.set('search', input.value);
            url.searchParams.set('page', '1');
            location.href = url.toString();
          }
        }, 500);
      });
    });
  },

  // ── Toggle Status ─────────────────────────────────────────
  initToggles() {
    document.addEventListener('change', async e => {
      const toggle = e.target.closest('.status-toggle');
      if (!toggle) return;
      const { table, id, field = 'status' } = toggle.dataset;
      const val = toggle.checked ? 1 : 0;

      try {
        const res = await this.ajax({
          url: location.href,
          data: { action: 'toggle_status', table, id, field, value: val }
        });
        if (res.success) {
          this.toast('success', 'Đã cập nhật!', res.message || '');
        } else {
          toggle.checked = !toggle.checked;
          this.toast('error', 'Lỗi!', res.message || '');
        }
      } catch {
        toggle.checked = !toggle.checked;
      }
    });
  },

  // ── Drag & Drop Sort ──────────────────────────────────────
  initSortable() {
    const lists = document.querySelectorAll('[data-sortable]');
    lists.forEach(list => {
      let dragging = null;

      list.querySelectorAll('[draggable="true"]').forEach(item => {
        item.addEventListener('dragstart', e => {
          dragging = item;
          item.classList.add('dragging');
          e.dataTransfer.effectAllowed = 'move';
        });
        item.addEventListener('dragend', () => {
          item.classList.remove('dragging');
          list.querySelectorAll('.drag-over').forEach(el => el.classList.remove('drag-over'));
          this.saveSortOrder(list);
          dragging = null;
        });
        item.addEventListener('dragover', e => {
          e.preventDefault();
          if (dragging && item !== dragging) {
            item.classList.add('drag-over');
            const rect = item.getBoundingClientRect();
            const midY = rect.top + rect.height / 2;
            if (e.clientY < midY) {
              list.insertBefore(dragging, item);
            } else {
              list.insertBefore(dragging, item.nextSibling);
            }
          }
        });
        item.addEventListener('dragleave', () => item.classList.remove('drag-over'));
      });
    });
  },

  async saveSortOrder(list) {
    const url = list.dataset.sortableUrl || location.href;
    const table = list.dataset.sortableTable;
    const ids = [...list.querySelectorAll('[data-id]')].map(el => el.dataset.id);
    if (!ids.length || !table) return;

    try {
      const res = await this.ajax({ url, data: { action: 'reorder', table, ids: ids.join(',') } });
      if (res.success) {
        this.toast('info', 'Thứ tự đã lưu', '', 2000);
      }
    } catch {}
  },

  // ── Image Pickers ─────────────────────────────────────────
  initImagePickers() {
    document.querySelectorAll('.img-picker-preview').forEach(picker => {
      picker.addEventListener('click', () => {
        const field = picker.dataset.field;
        this.openMediaPicker(url => {
          if (url) {
            const img = picker.querySelector('img') || document.createElement('img');
            img.src = url;
            if (!picker.querySelector('img')) picker.prepend(img);
            picker.classList.add('has-img');
            picker.querySelector('.img-picker-empty')?.remove();
            const input = document.getElementById(field) || document.querySelector(`input[name="${field}"]`);
            if (input) input.value = url;
          }
        });
      });
    });
  },

  // Open media picker modal and resolve with selected URL
  openMediaPicker(callback) {
    const modal = document.getElementById('mediaPickerModal');
    if (!modal) {
      // Fallback: simple URL input
      const url = prompt('Nhập URL ảnh:');
      if (url) callback(url);
      return;
    }
    this.openModal('mediaPickerModal');
    const selectBtn = modal.querySelector('#mediaPickerSelect');
    if (selectBtn) {
      selectBtn.onclick = () => {
        const selected = modal.querySelector('.media-item.selected');
        if (selected) {
          callback(selected.dataset.url);
          this.closeModal('mediaPickerModal');
        } else {
          this.toast('warning', 'Chưa chọn ảnh', 'Vui lòng chọn một ảnh trước.');
        }
      };
    }
  },

  // ── Dropzone Upload ───────────────────────────────────────
  initDropzone() {
    document.querySelectorAll('.dropzone').forEach(zone => {
      zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
      zone.addEventListener('dragleave', () => zone.classList.remove('drag-over'));
      zone.addEventListener('drop', e => {
        e.preventDefault();
        zone.classList.remove('drag-over');
        const files = e.dataTransfer.files;
        if (files.length) this.uploadFiles(files, zone);
      });
      const input = zone.querySelector('input[type="file"]');
      if (input) {
        input.addEventListener('change', () => this.uploadFiles(input.files, zone));
      }
    });
  },

  async uploadFiles(files, zone) {
    const folder = zone.dataset.folder || 'general';
    const grid = document.getElementById('mediaGrid');

    for (const file of files) {
      const fd = new FormData();
      fd.append('_csrf_token', this.csrf);
      fd.append('file', file);
      fd.append('folder', folder);

      try {
        const res = await fetch('../api/upload.php', { method: 'POST', body: fd, credentials: 'same-origin' });
        const data = await res.json();
        if (data.success) {
          this.toast('success', 'Upload thành công!', data.filename);
          if (grid) this.appendMediaItem(grid, data);
        } else {
          this.toast('error', 'Upload thất bại', data.message);
        }
      } catch {
        this.toast('error', 'Lỗi upload', '');
      }
    }
  },

  appendMediaItem(grid, data) {
    const item = document.createElement('div');
    item.className = 'media-item';
    item.dataset.url = data.url;
    item.dataset.id = data.id;
    item.innerHTML = `
      <img src="${data.url}" alt="${data.filename}" loading="lazy">
      <div class="media-overlay">
        <button class="btn btn-xs btn-danger" onclick="AdminJS.deleteMedia(${data.id}, this.closest('.media-item'))">
          <i class="fas fa-trash"></i>
        </button>
        <button class="btn btn-xs btn-info" onclick="AdminJS.copyUrl('${data.url}')">
          <i class="fas fa-copy"></i>
        </button>
        <div class="media-name">${data.filename}</div>
      </div>
      <div class="media-check"><i class="fas fa-check"></i></div>
    `;
    item.addEventListener('click', e => {
      if (e.target.closest('.btn')) return;
      document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
      item.classList.add('selected');
    });
    grid.prepend(item);
  },

  async deleteMedia(id, el) {
    if (!confirm('Xóa ảnh này?')) return;
    const res = await this.ajax({ url: location.href, data: { action: 'delete_media', id } });
    if (res.success) { el?.remove(); this.toast('success', 'Đã xóa!', ''); }
    else this.toast('error', 'Lỗi!', res.message);
  },

  copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => this.toast('info', 'Đã sao chép URL!', url, 2000));
  },

  // ── Bulk Actions ──────────────────────────────────────────
  initBulkActions() {
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
      selectAll.addEventListener('change', () => {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = selectAll.checked);
        this.updateBulkBar();
      });
      document.addEventListener('change', e => {
        if (e.target.classList.contains('row-check')) this.updateBulkBar();
      });
    }
  },

  updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const bar = document.getElementById('bulkBar');
    const count = document.getElementById('bulkCount');
    if (bar) bar.classList.toggle('d-none', checked.length === 0);
    if (count) count.textContent = checked.length;
  },

  getSelectedIds() {
    return [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
  },

  async bulkDelete(table, url) {
    const ids = this.getSelectedIds();
    if (!ids.length) return;
    if (!confirm(`Xóa ${ids.length} mục đã chọn?`)) return;
    const res = await this.ajax({ url, data: { action: 'bulk_delete', table, ids: ids.join(',') } });
    if (res.success) { this.toast('success', `Đã xóa ${ids.length} mục!`, ''); setTimeout(() => location.reload(), 1000); }
    else this.toast('error', 'Lỗi!', res.message);
  },

  async bulkToggle(table, value, url) {
    const ids = this.getSelectedIds();
    if (!ids.length) return;
    const res = await this.ajax({ url, data: { action: 'bulk_status', table, value, ids: ids.join(',') } });
    if (res.success) { this.toast('success', 'Đã cập nhật!', ''); setTimeout(() => location.reload(), 800); }
    else this.toast('error', 'Lỗi!', res.message);
  },

  // ── Confirm Dialog ────────────────────────────────────────
  confirm(message, onConfirm) {
    if (window.confirm(message)) onConfirm();
  },

  // ── Form Helpers ──────────────────────────────────────────
  fillForm(formId, data) {
    const form = document.getElementById(formId);
    if (!form) return;
    Object.entries(data).forEach(([key, val]) => {
      const input = form.querySelector(`[name="${key}"]`);
      if (!input) return;
      if (input.type === 'checkbox') input.checked = val == 1;
      else if (input.type === 'radio') {
        const radio = form.querySelector(`[name="${key}"][value="${val}"]`);
        if (radio) radio.checked = true;
      } else if (input.tagName === 'SELECT') {
        input.value = val;
      } else {
        input.value = val ?? '';
      }
    });
    // Update range displays
    form.querySelectorAll('input[type="range"]').forEach(r => {
      const display = r.nextElementSibling;
      if (display && display.classList.contains('range-val')) display.textContent = r.value + '%';
    });
  },

  submitForm(formId, url, onSuccess) {
    const form = document.getElementById(formId);
    if (!form) return;
    const fd = new FormData(form);
    fd.set('_csrf_token', this.csrf);

    const btn = form.querySelector('[type="submit"]');
    const origHtml = btn?.innerHTML;
    if (btn) { btn.innerHTML = '<span class="spinner"></span> Đang lưu...'; btn.disabled = true; }

    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          this.toast('success', 'Đã lưu!', data.message || '');
          this.closeAllModals();
          if (onSuccess) onSuccess(data);
          else setTimeout(() => location.reload(), 800);
        } else {
          this.toast('error', 'Lỗi!', data.message || '');
        }
      })
      .catch(() => this.toast('error', 'Lỗi kết nối', ''))
      .finally(() => {
        if (btn) { btn.innerHTML = origHtml; btn.disabled = false; }
      });
  },
};

// Range input live update
document.addEventListener('input', e => {
  if (e.target.type === 'range') {
    const display = e.target.nextElementSibling;
    if (display && display.classList.contains('range-val')) {
      display.textContent = e.target.value + '%';
    }
  }
});

// Color picker sync to hex input
document.addEventListener('input', e => {
  if (e.target.type === 'color') {
    const wrap = e.target.closest('.color-picker-wrap');
    if (wrap) {
      const hex = wrap.querySelector('.color-hex');
      if (hex) hex.value = e.target.value.toUpperCase();
    }
  }
  if (e.target.classList.contains('color-hex')) {
    const wrap = e.target.closest('.color-picker-wrap');
    if (wrap && /^#[0-9A-Fa-f]{6}$/.test(e.target.value)) {
      const picker = wrap.querySelector('input[type="color"]');
      if (picker) picker.value = e.target.value;
    }
  }
});

// Init on DOM ready
document.addEventListener('DOMContentLoaded', () => AdminJS.init());

// Generic delete handler
window.deleteItem = function(table, id, url) {
  AdminJS.deleteItem(table, id, url);
};

// Generic toggle status handler
window.toggleStatus = async function(table, id, field, val, url) {
  const res = await AdminJS.ajax({ url, data: { action: 'toggle_status', table, id, field, value: val } });
  if (res.success) AdminJS.toast('info', 'Đã cập nhật!', '', 2000);
  else { AdminJS.toast('error', 'Lỗi!', res.message); location.reload(); }
};
