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
        <button class="notif-btn" id="notif-bell-btn" onclick="_toggleDropdown(event)" title="Notifications" style="display:inline-flex; align-items:center; justify-content:center;">
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
            <span class="notif-dropdown-title" style="display:inline-flex; align-items:center; gap:6px;">🔔 ${t('Notifications')}</span>
            <button class="notif-mark-all" id="notif-mark-all-btn" onclick="_markAllRead()">${t('Mark all read')}</button>
        </div>
        <div class="notif-dropdown-body" id="notif-dropdown-body">
            <div class="notif-empty" style="padding: 24px 0; text-align: center; color: var(--text-muted);">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: rgba(255,255,255,0.25);">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155-3.097a4.486 4.486 0 00-3.097 3.097m0 0a24.3 24.3 0 01-.3 3.844m-4.5-4.5H3m18 0h-2.25m-15 0a9 9 0 0118 0m-9 5.25h.008v.008H12v-.008z"></path></svg>
                </div>
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
            <div class="notif-empty" style="padding: 24px 0; text-align: center; color: var(--text-muted);">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.03); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px; color: rgba(255,255,255,0.25);">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.143 17.082a24.248 24.248 0 003.844.148m-3.844-.148a23.856 23.856 0 01-5.455-1.31 8.964 8.964 0 002.3-5.542m3.155-3.097a4.486 4.486 0 00-3.097 3.097m0 0a24.3 24.3 0 01-.3 3.844m-4.5-4.5H3m18 0h-2.25m-15 0a9 9 0 0118 0m-9 5.25h.008v.008H12v-.008z"></path></svg>
                </div>
                <span>${t('No notifications yet')}</span>
            </div>`;
        return;
    }

    body.innerHTML = _notifData.map(n => {
        const unreadClass = n.is_read ? '' : ' notif-item-unread';
        const timeAgo = _timeAgo(n.created_at);
        const actionAttr = n.action_url ? `onclick="_goToNotif('${n.id}', '${n.action_url}')"` : `onclick="_markOneRead('${n.id}')"`;
        const cleanTitle = _cleanNotifText(translateText(n.title));
        const cleanMessage = _cleanNotifText(translateText(n.message));
        return `
            <div class="notif-item${unreadClass}" ${actionAttr} style="display:flex;align-items:center;gap:12px;padding:12px 16px;">
                <div class="notif-item-icon" style="flex-shrink:0;background:none;padding:0;display:flex;align-items:center;justify-content:center;">${_getNotifIconHTML(n.icon, n.type)}</div>
                <div class="notif-item-content" style="flex:1;min-width:0;">
                    <div class="notif-item-title" style="font-weight:600;font-size:0.85rem;color:#fff;margin-bottom:2px;">${_escHtml(cleanTitle)}</div>
                    <div class="notif-item-message" style="font-size:0.75rem;color:var(--text-muted);line-height:1.4;margin-bottom:4px;">${_escHtml(cleanMessage)}</div>
                    <div class="notif-item-time" style="font-size:0.65rem;color:rgba(255,255,255,0.3);">${timeAgo}</div>
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

function _cleanNotifText(str) {
    if (!str) return '';
    try {
        // Strip emojis, checkmarks, stars, tickets, documents
        return str.replace(/[\u{1F300}-\u{1F9FF}\u{1F600}-\u{1F64F}\u{2700}-\u{27BF}\u{1F200}-\u{1F2FF}\u{1F900}-\u{1F9FF}\u{1F000}-\u{1F0FF}\u{1F1E0}-\u{1F1FF}]/gu, '')
                  .replace(/[\u2705\u274C\u2B50\u26A1]/g, '')
                  .replace(/\s+/g, ' ')
                  .trim();
    } catch (e) {
        return str;
    }
}

function _getNotifIconHTML(iconStr, typeStr) {
    const icon = iconStr || '';
    const type = typeStr || '';
    
    if (icon.includes('✅') || type === 'accepted' || type === 'approved') {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(16,185,129,0.15);color:#10b981;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"></path></svg></span>`;
    }
    if (icon.includes('❌') || type === 'rejected' || type === 'cancelled') {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(239,68,68,0.15);color:#ef4444;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path></svg></span>`;
    }
    if (icon.includes('📝') || icon.includes('📋') || type === 'review' || type === 'contract' || type === 'event') {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(139,92,246,0.15);color:#a78bfa;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path></svg></span>`;
    }
    if (icon.includes('🎟️') || icon.includes('🎫') || type === 'ticket') {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(6,182,212,0.15);color:#06b6d4;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 00-2.25-2.25H9.75a.75.75 0 00-.75.75v1.5a.75.75 0 01-1.5 0v-1.5a.75.75 0 00-.75-.75H4.5A2.25 2.25 0 002.25 6v2.25a.75.75 0 010 1.5V12a2.25 2.25 0 002.25 2.25h3.75a.75.75 0 00.75-.75v-1.5a.75.75 0 011.5 0v1.5a.75.75 0 00.75.75h3.75a2.25 2.25 0 002.25-2.25V9.75a.75.75 0 010-1.5z"></path></svg></span>`;
    }
    if (icon.includes('⭐') || icon.includes('🌟') || type === 'rating') {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(234,179,8,0.15);color:#eab308;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499c-.198-.39-.76-.39-.958 0L8.286 7.859l-4.81.698a.562.562 0 00-.312.958l3.481 3.393-.82 4.793a.562.562 0 00.814.592L12 15.847l4.364 2.29a.562.562 0 00.814-.592l-.82-4.793 3.48-3.393a.562.562 0 00-.312-.958l-4.81-.698L11.48 3.5z"></path></svg></span>`;
    }
    if (icon.includes('🔄') || icon.includes('⏳') || icon.includes('⌛')) {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(234,179,8,0.15);color:#eab308;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"></path></svg></span>`;
    }
    if (icon.includes('⚠️') || icon.includes('🚫')) {
        return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(239,68,68,0.15);color:#ef4444;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></span>`;
    }
    
    return `<span style="display:flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.06);color:var(--text-muted);"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a3 3 0 11-5.714 0M3.124 7.5A8.969 8.969 0 015.292 3m13.417 0a8.969 8.969 0 012.168 4.5"></path></svg></span>`;
}
