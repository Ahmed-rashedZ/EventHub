/**
 * EventHub – Central API Helper
 * All fetch calls go through apiFetch() so auth tokens are auto-injected.
 */

const API_BASE = '/api';

/**
 * Performs an authenticated API request.
 * @param {string} endpoint   e.g. '/events'
 * @param {object} options    fetch options (method, body, etc.)
 * @returns {Promise<{ok:boolean, status:number, data:any}>}
 */
async function apiFetch(endpoint, options = {}) {
    const token = localStorage.getItem('token');

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
        ...(options.headers || {}),
    };

    try {
        const res = await fetch(API_BASE + endpoint, {
            ...options,
            headers,
        });

        let data;
        const contentType = res.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            data = await res.json();
        } else {
            data = await res.text();
        }

        return { ok: res.ok, status: res.status, data };
    } catch (err) {
        showToast('Network error: ' + err.message, 'error');
        return { ok: false, status: 0, data: null };
    }
}

/**
 * Short-hand helpers
 */
const api = {
    get:    (url)           => apiFetch(url),
    post:   (url, body)     => apiFetch(url, { method: 'POST',   body: JSON.stringify(body) }),
    put:    (url, body)     => apiFetch(url, { method: 'PUT',    body: JSON.stringify(body) }),
    delete: (url)           => apiFetch(url, { method: 'DELETE' }),
};

/**
 * Toast notification helper
 */
function showToast(message, type = 'info') {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icon}</span><span>${message}</span>`;
    container.appendChild(toast);

    setTimeout(() => toast.remove(), 3500);
}

/**
 * Format a datetime string nicely
 */
function fmtDate(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleString('en-US', {
        month: 'short', day: 'numeric', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
}

function fmtDateShort(dt) {
    if (!dt) return '—';
    return new Date(dt).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

/**
 * Status badge HTML
 */
function badge(status) {
    const cls = {
        pending: 'badge-pending', approved: 'badge-approved', rejected: 'badge-rejected',
        accepted: 'badge-accepted', used: 'badge-used', unused: 'badge-unused',
        available: 'badge-approved', maintenance: 'badge-rejected'
    }[status] || 'badge-pending';
    return `<span class="badge ${cls}">${status}</span>`;
}

function roleBadge(role) {
    const map = { Admin: 'role-admin', 'Event Manager': 'role-manager', Sponsor: 'role-sponsor', User: 'role-user', Assistant: 'role-assistant' };
    return `<span class="role-badge ${map[role] || ''}">${role}</span>`;
}
