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
 * Maps profile.is_available from the API/database to a boolean.
 * Returns null if the value is missing or not a definite true/false — do not guess ON/OFF in the UI.
 */
function availabilityFromDatabase(value) {
    if (value === true || value === 1) return true;
    if (value === false || value === 0) return false;
    return null;
}

/**
 * Require auth + specific role(s). Redirects if not met.
 * @param {...string} roles   e.g. requireRole('Admin') or requireRole('Admin','Event Manager')
 */
function requireRole(...roles) {
    const user = getUser();
    const token = getToken();

    if (!user || !token) {
        window.location.href = '/login';
        return null;
    }

    if (roles.length > 0 && !roles.includes(user.role)) {
        // Redirect to the correct dashboard
        const dashMap = {
            'Admin': '/admin/dashboard',
            'Event Manager': '/manager/dashboard',
            'Sponsor': '/sponsor/dashboard',
            'User': '/profile',
            'Assistant': '/profile'
        };
        const target = dashMap[user.role] || '/login';
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
        document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
        localStorage.removeItem('user');
        window.location.href = '/login';
    });
}

/**
 * Populate sidebar with current user info
 */
function populateSidebar(user) {
    const nameEl  = document.getElementById('sidebar-username');
    const roleEl  = document.getElementById('sidebar-role');
    const avEl    = document.getElementById('sidebar-avatar');
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

        avEl.innerHTML = `<img src="${imageUrl}" style="width:100%; height:100%; border-radius:50%; object-fit:cover;" alt="Avatar"/>`;
        avEl.style.background = 'transparent';
    }

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
