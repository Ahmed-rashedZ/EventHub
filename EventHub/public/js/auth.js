/**
 * EventHub – Auth Guard Helper
 * Depends on i18n.js being loaded before this file for the t() function.
 */

/* Safety shim: if i18n.js hasn't loaded yet, t() is a no-op pass-through */
if (typeof t !== 'function') { window.t = k => k; }

function getUser() {
    try { return JSON.parse(sessionStorage.getItem('user')); } catch { return null; }
}

function getToken() {
    return sessionStorage.getItem('token');
}

/**
 * Extract auth_token value from cookie string.
 */
function _getTokenFromCookie() {
    const entry = document.cookie.split('; ').find(r => r.startsWith('auth_token='));
    return entry ? entry.split('=')[1] : null;
}

/**
 * If sessionStorage is empty but a valid cookie exists,
 * restore the session via API and reload the page (runs ONCE).
 * Returns true if a restore was triggered (caller should stop init).
 */
function _tryRestoreSession() {
    if (getUser() && getToken()) return false; // already have session

    const cookieToken = _getTokenFromCookie();
    if (!cookieToken) return false; // no cookie either → genuine logout

    // Cookie present but sessionStorage empty → restore silently
    fetch('/api/profile', {
        headers: {
            'Authorization': `Bearer ${cookieToken}`,
            'Accept': 'application/json'
        }
    }).then(r => {
        if (r.ok) return r.json();
        throw new Error('invalid');
    }).then(data => {
        if (data && data.user) {
            sessionStorage.setItem('token', cookieToken);
            sessionStorage.setItem('user', JSON.stringify(data.user));
            window.location.reload(); // one-time reload with session restored
        } else {
            throw new Error('no user');
        }
    }).catch(() => {
        // Cookie invalid — clear everything and go to login
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        window.location.href = '/login';
    });

    return true; // restore in progress, caller must stop
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
 * If sessionStorage is empty but cookie is valid, restores session silently.
 */
function requireRole(...roles) {
    const user  = getUser();
    const token = getToken();

    if (!user || !token) {
        // Try restoring from cookie before giving up
        if (_tryRestoreSession()) return null; // restore in progress
        window.location.href = '/login';
        return null;
    }

    if (roles.length > 0 && !roles.includes(user.role)) {
        const dashMap = {
            'Admin':         '/admin/dashboard',
            'Event Manager': '/manager/dashboard',
            'Sponsor':       '/sponsor/dashboard',
            'Company':       '/company/dashboard',
            'Attendee':      '/profile',
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
        sessionStorage.removeItem('token');
        sessionStorage.removeItem('user');
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        window.location.href = '/login';
    });
}

/**
 * Build sidebar nav HTML using t() so links are translated automatically.
 */
function buildSidebarLinks(role, activePath) {
    const path = activePath || window.location.pathname;

    const icons = {
        dashboard: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>`,
        users: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>`,
        events: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>`,
        venues: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>`,
        verifications: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>`,
        assistants: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 005.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>`,
        attendance: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`,
        sponsorship: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>`,
        browse: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>`,
        history: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.282.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>`,
        profile: `<svg xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;vertical-align:middle;display:inline-block;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>`
    };

    const nav = (href, iconKey, label) => {
        const active = path.startsWith(href) ? ' active' : '';
        const iconSvg = icons[iconKey] || '';
        return `<a class="nav-item${active}" href="${href}"><span class="nav-icon">${iconSvg}</span> ${t(label)}</a>`;
    };

    let html = '';

    if (role === 'Admin') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/admin/dashboard', 'dashboard', 'Dashboard');
        html += `<span class="nav-section-label">${t('Management')}</span>`;
        html += nav('/admin/users',   'users', 'Users');
        html += nav('/admin/events',  'events', 'Events');
        html += nav('/admin/venues',  'venues', 'Venues');
        html += nav('/admin/verifications', 'verifications', 'Verifications');

    } else if (role === 'Event Manager') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/manager/dashboard',   'dashboard', 'Dashboard');
        html += `<span class="nav-section-label">${t('Events')}</span>`;
        html += nav('/manager/events',      'events', 'My Events');
        html += nav('/manager/assistants',  'users', 'Assistants');
        html += nav('/manager/attendance',  'attendance', 'Attendance');
        html += nav('/manager/sponsorship', 'sponsorship', 'Sponsorship');
        html += nav('/manager/exhibition',  'venues', 'Exhibitions');

    } else if (role === 'Sponsor') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/sponsor/dashboard', 'dashboard', 'Dashboard');
        html += `<span class="nav-section-label">${t('Opportunities')}</span>`;
        html += nav('/sponsor/events',    'browse', 'Browse Events');
        html += nav('/sponsor/requests',  'sponsorship', 'Browse Requests');
        html += nav('/sponsor/history',   'history', 'History');
    } else if (role === 'Company') {
        html += `<span class="nav-section-label">${t('Overview')}</span>`;
        html += nav('/company/dashboard',   'dashboard', 'Dashboard');
        html += `<span class="nav-section-label">${t('Exhibitions')}</span>`;
        html += nav('/company/exhibitions', 'venues', 'My Applications');
        html += nav('/company/events',      'browse', 'Browse Exhibitions');
    }

    html += `<span class="nav-section-label">${t('Settings')}</span>`;
    html += nav('/profile', 'profile', 'My Profile');

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
        if (typeof user.image === 'string' && user.image.trim() !== '') {
            imageUrl = (user.image.startsWith('http') || user.image.startsWith('/')) ? user.image : '/storage/' + user.image;
        } else if (typeof user.avatar === 'string' && user.avatar.trim() !== '') {
            imageUrl = (user.avatar.startsWith('http') || user.avatar.startsWith('/')) ? user.avatar : '/storage/' + user.avatar;
        } else if (user.profile && typeof user.profile.logo === 'string' && user.profile.logo.trim() !== '') {
            imageUrl = user.profile.logo.startsWith('http') ? user.profile.logo : (user.profile.logo.startsWith('/') ? user.profile.logo : '/' + user.profile.logo);
        }
        avEl.innerHTML = `<img src="${imageUrl}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" alt="Avatar"/>`;
        avEl.style.background = 'transparent';
    }

    /* Auto-populate sidebar-links if an element with that ID exists */
    const linksDiv = document.getElementById('sidebar-links');
    if (linksDiv && !linksDiv.querySelector('.nav-item')) {
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
        if (_tryRestoreSession()) return null; // restore in progress
        window.location.href = '/login';
        return null;
    }
    return user;
}
