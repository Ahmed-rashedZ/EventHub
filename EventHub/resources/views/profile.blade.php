<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Profile – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <style>
    .profile-card { max-width: 600px; margin: 40px auto; padding: 30px; }
  </style>
</head>
<body>
<div class="app-layout">
  <!-- Dynamic Sidebar placeholder -->
  <aside class="sidebar" id="dynamic-sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav" id="sidebar-links"></nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">?</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">User</div><div class="user-role" id="sidebar-role">Role</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">My Profile</h1><p class="page-subtitle">Update your personal information</p></div>
    </div>

    <div class="card profile-card">
      <form id="profile-form">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input id="p-name" type="text" class="form-control" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input id="p-email" type="email" class="form-control" required/>
        </div>
        <div class="form-group">
          <label class="form-label">New Password</label>
          <input id="p-pass" type="password" class="form-control" placeholder="Leave blank to keep current password" minlength="8"/>
        </div>
        <div style="margin-top:24px; text-align:right">
          <button type="submit" class="btn btn-primary" id="save-btn">Save Changes</button>
        </div>
      </form>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  // Dynamic Sidebar Logic
  const u = JSON.parse(localStorage.getItem('user'));
  if (!u) { window.location.href = '/login'; }
  else {
    populateSidebar(u);
    
    // Inject links based on role
    const linksDiv = document.getElementById('sidebar-links');
    if (u.role === 'Admin') {
      linksDiv.innerHTML = `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Management</span>
        <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
        <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
        <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>`;
    } else if (u.role === 'Event Manager') {
      linksDiv.innerHTML = `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Events</span>
        <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
        <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
        <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
        <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>`;
    } else if (u.role === 'Sponsor') {
      linksDiv.innerHTML = `
        <span class="nav-section-label">Overview</span>
        <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
        <span class="nav-section-label">Sponsorships</span>
        <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Browse Requests</a>`;
    }
    // Add Profile link at the bottom
    linksDiv.innerHTML += `
      <span class="nav-section-label">Settings</span>
      <a class="nav-item active" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>`;
      
    // Populate form
    document.getElementById('p-name').value = u.name;
    document.getElementById('p-email').value = u.email;
  }

  document.getElementById('profile-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.textContent = 'Saving...'; btn.disabled = true;

    const body = { name: document.getElementById('p-name').value, email: document.getElementById('p-email').value };
    const pass = document.getElementById('p-pass').value;
    if (pass) body.password = pass;

    const res = await api.put('/profile', body);
    
    if (res.ok) {
      showToast('Profile updated!', 'success');
      localStorage.setItem('user', JSON.stringify(res.data.user)); // update cache
      populateSidebar(res.data.user);
      document.getElementById('p-pass').value = '';
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error updating profile';
      showToast(msg, 'error');
    }
    btn.textContent = 'Save Changes'; btn.disabled = false;
  });
</script>
</body>
</html>
