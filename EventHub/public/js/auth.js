/**
 * EventHub – Auth Guard Helper
 * Depends on i18n.js being loaded before this file for the t() function.
 */

/* Safety shim: if i18n.js hasn't loaded yet, t() is a no-op pass-through */
if (typeof t !== 'function') { window.t = k => k; }

function getUser() {
    try { return JSON.parse(localStorage.getItem('user')); } catch { return null; }
}

function getToken() {
    return localStorage.getItem('token');
}

/**
 * Maps profile.is_available from the API/database to a boolean.
 */
function availabilityFromDatabase(value) {
    if (value === true  || value === 1) return true;
    if (value === false || value === 0) return false;
    return null;
}

/**
 * Require auth + specific role(s). Redirects if not met.
 */
function requireRole(...roles) {
    const user  = getUser();
    const token = getToken();

    if (!user || !token) {
        window.location.href = '/login';
        return null;
    }

    if (roles.length > 0 && !roles.includes(user.role)) {
        const dashMap = {
            'Admin':         '/admin/dashboard',
            'Event Manager': '/manager/dashboard',
            'Sponsor':       '/sponsor/dashboard',
            'User':          '/profile',
            'Assistant':     '/profile'
        };
        const target = dashMap[user.role] || '/login';
        if (window.location.pathname !== target) window.location.href = target;
        return null;
    }

    return user;
}

/**
 * Log the user out and redirect to login
 */
function logout() {
    api.post('/logout').finally(() => {
        localStorage.removeItem('token');
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        localStorage.removeItem('user');
        window.location.href = '/login';
    });
}

/**
 * Build sidebar nav HTML using t() so links are translated automatically.
 */
function buildSidebarLinks(role, activePath) {
    const path = activePath || window.location.pathname;

    const nav = (href, icon, label) => {
        const active = path.startsWith(href) ? ' active' : '';
        return `<a class="nav-item${active}" href="${href}"><span class="nav-icon">${icon}</span> ${t(label)}</a>`;
    };

    let html = '';

    if (role === 'Admin') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/admin/dashboard', '📊', 'Dashboard');
        html += `<span class="nav-section-label">${t('Management')}</span>`;
        html += nav('/admin/users',   '👥', 'Users');
        html += nav('/admin/events',  '📅', 'Events');
        html += nav('/admin/venues',  '🏛️', 'Venues');
        html += nav('/admin/verifications', '🛡️', 'Verifications');

    } else if (role === 'Event Manager') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/manager/dashboard',   '📊', 'Dashboard');
        html += `<span class="nav-section-label">${t('Events')}</span>`;
        html += nav('/manager/events',      '📅', 'My Events');
        html += nav('/manager/assistants',  '👥', 'Assistants');
        html += nav('/manager/attendance',  '📍', 'Attendance');
        html += nav('/manager/sponsorship', '💼', 'Sponsorship');

    } else if (role === 'Sponsor') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/sponsor/dashboard', '📊', 'Dashboard');
        html += `<span class="nav-section-label">${t('Opportunities')}</span>`;
        html += nav('/sponsor/events',    '🌍', 'Browse Events');
        html += nav('/sponsor/requests',  '💼', 'Browse Requests');
        html += nav('/sponsor/history',   '📋', 'History');
    }

    html += `<span class="nav-section-label">${t('Settings')}</span>`;
    html += nav('/profile', '⚙️', 'My Profile');

    return html;
}

/**
 * Populate sidebar with current user info
 */
function populateSidebar(user) {
    const nameEl = document.getElementById('sidebar-username');
    const roleEl = document.getElementById('sidebar-role');
    const avEl   = document.getElementById('sidebar-avatar');

    if (nameEl && nameEl.textContent.trim() === '') nameEl.textContent = user.name;
    if (roleEl && roleEl.textContent.trim() === '') roleEl.textContent = user.role;

    if (avEl && !avEl.querySelector('img')) {
        let imageUrl = '/images/default-avatar.png';
        if (user.image && user.image.trim() !== '') {
            imageUrl = (user.image.startsWith('http') || user.image.startsWith('/')) ? user.image : '/storage/' + user.image;
        } else if (user.avatar && user.avatar.trim() !== '') {
            imageUrl = (user.avatar.startsWith('http') || user.avatar.startsWith('/')) ? user.avatar : '/storage/' + user.avatar;
        } else if (user.profile && user.profile.logo) {
            imageUrl = user.profile.logo.startsWith('http') ? user.profile.logo : (user.profile.logo.startsWith('/') ? user.profile.logo : '/' + user.profile.logo);
        }
        avEl.innerHTML = `<img src="${imageUrl}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Avatar"/>`;
        avEl.style.background = 'transparent';
    }

    /* Auto-populate sidebar-links if an element with that ID exists */
    const linksDiv = document.getElementById('sidebar-links');
    if (linksDiv && linksDiv.innerHTML.trim() === '') {
        linksDiv.innerHTML = buildSidebarLinks(user.role);
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) logoutBtn.addEventListener('click', logout);

    // Initialize notification bell if the script is loaded
    if (typeof initNotificationBell === 'function') {
        initNotificationBell();
    }
}

/**
 * Highlight the active nav item based on current path
 */
function setActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(el => {
        const href = el.getAttribute('href') || '';
        if (path.startsWith(href) && href !== '') el.classList.add('active');
    });
}

function navigateToProfile(id) {
    if (!id) return;
    window.location.href = `/user-profile?id=${id}`;
}

function requireAuth() {
    const user = getUser();
    if (!user) {
        window.location.href = '/login';
        return null;
    }
    return user;
}
