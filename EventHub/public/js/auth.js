/**
 * EventHub – Auth Guard Helper
 */

function getUser() {
    try { return JSON.parse(localStorage.getItem('user')); } catch { return null; }
}

function getToken() {
    return localStorage.getItem('token');
}

/**
 * Require auth + specific role(s). Redirects if not met.
 * @param {...string} roles   e.g. requireRole('Admin') or requireRole('Admin','Event Manager')
 */
function requireRole(...roles) {
    const user = getUser();
    const token = getToken();

    if (!user || !token) {
        window.location.href = '/login.html';
        return null;
    }

    if (roles.length > 0 && !roles.includes(user.role)) {
        // Redirect to the correct dashboard
        const dashMap = {
            'Admin': '/admin/dashboard.html',
            'Event Manager': '/manager/dashboard.html',
            'Sponsor': '/sponsor/dashboard.html',
            'User': '/profile.html',
            'Assistant': '/profile.html'
        };
        const target = dashMap[user.role] || '/login.html';
        if (window.location.pathname !== target) {
            window.location.href = target;
        }
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
        localStorage.removeItem('user');
        window.location.href = '/login.html';
    });
}

/**
 * Populate sidebar with current user info
 */
function populateSidebar(user) {
    const nameEl  = document.getElementById('sidebar-username');
    const roleEl  = document.getElementById('sidebar-role');
    const avEl    = document.getElementById('sidebar-avatar');
    if (nameEl) nameEl.textContent = user.name;
    if (roleEl) roleEl.textContent = user.role;
    if (avEl)   avEl.textContent   = user.name.charAt(0).toUpperCase();

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) logoutBtn.addEventListener('click', logout);
}

/**
 * Highlight the active nav item based on current path
 */
function setActiveNav() {
    const path = window.location.pathname;
    document.querySelectorAll('.nav-item').forEach(el => {
        const href = el.getAttribute('href') || '';
        if (path.endsWith(href) || (href !== '' && path.includes(href))) {
            el.classList.add('active');
        }
    });
}
