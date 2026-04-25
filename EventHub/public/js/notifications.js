/**
 * EventHub – Notification Bell Component
 * Injects a notification bell into the topbar and manages the dropdown UI.
 * Depends on: api.js (apiFetch, fmtDate), i18n.js (t), auth.js (getUser)
 *
 * Usage: call `initNotificationBell()` after the page loads.
 */

let _notifData = [];
let _notifDropdownOpen = false;
let _notifInitialized = false;

/**
 * Initialize the notification bell. Call this once on page load.
 */
function initNotificationBell() {
    if (_notifInitialized) return;
    _notifInitialized = true;

    const topbarActions = document.querySelector('.topbar-actions');
    if (!topbarActions) {
        // If there's no topbar-actions div, create one inside the topbar
        const topbar = document.querySelector('.topbar');
        if (!topbar) return;
        let actions = document.createElement('div');
        actions.className = 'topbar-actions';
        topbar.appendChild(actions);
    }
    _injectBellHTML();
    _loadNotifications();

    // Poll every 30 seconds
    setInterval(_loadNotifications, 30000);

    // Close dropdown on outside click
    document.addEventListener('click', (e) => {
        const panel = document.getElementById('notif-dropdown');
        const btn   = document.getElementById('notif-bell-btn');
        if (panel && btn && !panel.contains(e.target) && !btn.contains(e.target)) {
            _closeDropdown();
        }
    });
}

function _injectBellHTML() {
    const topbarActions = document.querySelector('.topbar-actions');
    if (!topbarActions) return;

    // Insert bell button BEFORE existing buttons
    const bellWrapper = document.createElement('div');
    bellWrapper.style.position = 'relative';
    bellWrapper.innerHTML = `
        <button class="notif-btn" id="notif-bell-btn" onclick="_toggleDropdown(event)" title="Notifications">
            🔔
            <span class="notif-dot" id="notif-dot" style="display:none"></span>
        </button>
    `;
    topbarActions.prepend(bellWrapper);

    // Create dropdown as direct child of body to avoid overflow clipping
    const dropdown = document.createElement('div');
    dropdown.className = 'notif-dropdown';
    dropdown.id = 'notif-dropdown';
    dropdown.innerHTML = `
        <div class="notif-dropdown-header">
            <span class="notif-dropdown-title">🔔 ${t('Notifications')}</span>
            <button class="notif-mark-all" id="notif-mark-all-btn" onclick="_markAllRead()">${t('Mark all read')}</button>
        </div>
        <div class="notif-dropdown-body" id="notif-dropdown-body">
            <div class="notif-empty">
                <div style="font-size:2rem; opacity:.4; margin-bottom:8px;">🔕</div>
                <span>${t('No notifications yet')}</span>
            </div>
        </div>
    `;
    document.body.appendChild(dropdown);
}

async function _loadNotifications() {
    const res = await api.get('/notifications');
    if (!res.ok) return;

    _notifData = res.data.notifications || [];
    const unread = res.data.unread_count || 0;

    // Update dot
    const dot = document.getElementById('notif-dot');
    if (dot) dot.style.display = unread > 0 ? 'block' : 'none';

    // Update count on bell button
    const btn = document.getElementById('notif-bell-btn');
    if (btn) {
        // Remove old count badge
        const old = btn.querySelector('.notif-count');
        if (old) old.remove();

        if (unread > 0) {
            const countEl = document.createElement('span');
            countEl.className = 'notif-count';
            countEl.textContent = unread > 9 ? '9+' : unread;
            btn.appendChild(countEl);
        }
    }

    // Re-render dropdown content if open
    if (_notifDropdownOpen) _renderDropdown();
}

function _toggleDropdown(e) {
    if (e) e.stopPropagation();
    _notifDropdownOpen = !_notifDropdownOpen;
    const panel = document.getElementById('notif-dropdown');
    const btn = document.getElementById('notif-bell-btn');
    if (!panel) return;
    if (_notifDropdownOpen) {
        // Position dropdown below the bell
        if (btn) {
            const rect = btn.getBoundingClientRect();
            panel.style.top = (rect.bottom + 10) + 'px';
            const isRTL = document.documentElement.getAttribute('dir') === 'rtl';
            if (isRTL) {
                panel.style.right = 'auto';
                panel.style.left = Math.max(16, rect.left) + 'px';
            } else {
                panel.style.left = 'auto';
                panel.style.right = Math.max(16, window.innerWidth - rect.right) + 'px';
            }
        }
        _renderDropdown();
        panel.classList.add('open');
    } else {
        _closeDropdown();
    }
}

function _closeDropdown() {
    _notifDropdownOpen = false;
    const panel = document.getElementById('notif-dropdown');
    if (panel) panel.classList.remove('open');
}

function _renderDropdown() {
    const body = document.getElementById('notif-dropdown-body');
    if (!body) return;

    if (!_notifData.length) {
        body.innerHTML = `
            <div class="notif-empty">
                <div style="font-size:2rem; opacity:.4; margin-bottom:8px;">🔕</div>
                <span>${t('No notifications yet')}</span>
            </div>`;
        return;
    }

    body.innerHTML = _notifData.map(n => {
        const unreadClass = n.is_read ? '' : ' notif-item-unread';
        const timeAgo = _timeAgo(n.created_at);
        const actionAttr = n.action_url ? `onclick="_goToNotif('${n.id}', '${n.action_url}')"` : `onclick="_markOneRead('${n.id}')"`;
        return `
            <div class="notif-item${unreadClass}" ${actionAttr}>
                <div class="notif-item-icon">${n.icon || '🔔'}</div>
                <div class="notif-item-content">
                    <div class="notif-item-title">${_escHtml(translateText(n.title))}</div>
                    <div class="notif-item-message">${_escHtml(translateText(n.message))}</div>
                    <div class="notif-item-time">${timeAgo}</div>
                </div>
                ${!n.is_read ? '<div class="notif-item-unread-dot"></div>' : ''}
            </div>`;
    }).join('');
}

async function _goToNotif(id, url) {
    // Mark as read, then navigate
    await api.put(`/notifications/${id}/read`);
    _closeDropdown();
    window.location.href = url;
}

async function _markOneRead(id) {
    await api.put(`/notifications/${id}/read`);
    await _loadNotifications();
    _renderDropdown();
}

async function _markAllRead() {
    await api.put('/notifications/read-all');
    await _loadNotifications();
    _renderDropdown();
    showToast('All notifications marked as read', 'success');
}

function _timeAgo(dateStr) {
    const now = new Date();
    const then = new Date(dateStr);
    const diffMs = now - then;
    const diffMin = Math.floor(diffMs / 60000);
    if (diffMin < 1) return t('Just now');
    if (diffMin < 60) return diffMin + ' ' + t('m ago');
    const diffHr = Math.floor(diffMin / 60);
    if (diffHr < 24) return diffHr + ' ' + t('h ago');
    const diffDay = Math.floor(diffHr / 24);
    if (diffDay < 7) return diffDay + ' ' + t('d ago');
    return fmtDateShort(dateStr);
}

function _escHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}
