<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login – EventHub</title>
  <meta name="description" content="Sign in to EventHub Smart Event Management Platform"/>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
<link rel="icon" href="/images/logo.jpg" type="image/jpeg">
</head>
<body>
<div class="auth-page">
  <div class="auth-bg-glow"></div>
  <div class="auth-bg-glow auth-bg-glow-2"></div>

  <div class="auth-card">
    <div class="auth-logo" style="display:flex; justify-content:center; align-items:center; margin-bottom: 20px;"><img src="/images/logo.jpg" alt="EventHub Logo" style="width: 95px; height: 95px; object-fit: contain; border-radius: 50%; box-shadow: 0 8px 24px rgba(0,0,0,0.1);"></div>

    <h2 class="auth-heading">Welcome back</h2>
    <p class="auth-subheading">Sign in to your account to continue</p>

    <form id="login-form" autocomplete="off">
      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <input id="email" type="email" class="form-control" placeholder="you@example.com" required autocomplete="off"/>
      </div>
      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <input id="password" type="password" class="form-control" placeholder="••••••••" required autocomplete="new-password"/>
      </div>
      <div style="text-align: right; margin: -8px 0 16px;">
        <a href="/forgot-password" style="font-size: 0.82rem; color: var(--accent); font-weight: 500; text-decoration: none; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.7'" onmouseout="this.style.opacity='1'">
          <script>document.write(t('Forgot Password?'))</script>
        </a>
      </div>
      <button type="submit" class="btn btn-primary btn-block" id="login-btn">Sign In</button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="/register">Register</a>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script>
  (function(){
    // Clear any old localStorage auth data (migration from old system)
    localStorage.removeItem('user');
    localStorage.removeItem('token');

    const user = sessionStorage.getItem('user');
    const token = sessionStorage.getItem('token');
    const hasCookie = document.cookie.split('; ').some(row => row.startsWith('auth_token='));
    
    if (user && token && hasCookie) {
      const u = JSON.parse(user);
      if (['Event Manager', 'Sponsor'].includes(u.role) && u.verification_status !== 'verified') {
        window.location.href = '/pending-verification';
      } else {
        redirectByRole(u.role);
      }
    } else {
      sessionStorage.removeItem('user');
      sessionStorage.removeItem('token');
      document.cookie = 'auth_token=; path=/; expires=Thu, 01 Jan 1970 00:00:00 UTC;';
    }
  })();

  function redirectByRole(role) {
    const map = { 'Admin': '/admin/dashboard', 'Event Manager': '/manager/dashboard', 'Sponsor': '/sponsor/dashboard', 'User': '/profile', 'Assistant': '/profile' };
    window.location.href = map[role] || '/login';
  }

  document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('login-btn');
    btn.textContent = 'Signing in…'; btn.disabled = true;

    const res = await api.post('/login', {
      email: document.getElementById('email').value,
      password: document.getElementById('password').value,
      platform: 'web',
    });

    if (res.ok) {
      sessionStorage.setItem('token', res.data.token);
      sessionStorage.setItem('user', JSON.stringify(res.data.user));
      document.cookie = "auth_token=" + res.data.token + "; path=/; SameSite=Lax;";
      showToast(translateText('Welcome back, ' + res.data.user.name + '!'), 'success');
      
      setTimeout(() => {
        if (['Event Manager', 'Sponsor'].includes(res.data.user.role) && res.data.user.verification_status !== 'verified') {
          window.location.href = '/pending-verification';
        } else {
          redirectByRole(res.data.user.role);
        }
      }, 600);
    } else {
      showToast(res.data?.message || 'Login failed', 'error');
      btn.textContent = 'Sign In'; btn.disabled = false;
    }
  });
</script>
</body>
</html>





