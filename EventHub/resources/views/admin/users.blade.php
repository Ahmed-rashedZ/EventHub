<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item active" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Users</h1><p class="page-subtitle">Manage all platform users</p></div>
      <div class="topbar-actions">
        <div class="search-bar"><span>🔍</span><input id="search-input" type="text" placeholder="Search users…" oninput="filterTable()"/></div>
        <button class="btn btn-primary" onclick="openModal()">+ Add User</button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table id="users-table">
          <thead>
            <tr>
              <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th>
            </tr>
          </thead>
          <tbody id="users-body">
            <tr class="loading-row"><td colspan="6"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="user-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Create User</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="user-form">
      <div class="form-group">
        <label class="form-label">Full Name</label>
        <input id="u-name" type="text" class="form-control" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input id="u-email" type="email" class="form-control" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Password</label>
        <input id="u-pass" type="password" class="form-control" required minlength="8"/>
      </div>
      <div class="form-group">
        <label class="form-label">Role</label>
        <select id="u-role" class="form-control">
          <option value="Event Manager">Event Manager</option>
          <option value="User">Attendee (User)</option>
          <option value="Sponsor">Sponsor</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="save-btn">Create User</button>
      </div>
    </form>
  </div>
</div>
<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  let allUsers = [];
  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadUsers(); }

  async function loadUsers() {
    const res = await api.get('/analytics/users');
    const tbody = document.getElementById('users-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;color:var(--danger)">Failed to load users</td></tr>'; return; }
    allUsers = res.data;
    renderUsers(allUsers);
  }

  function renderUsers(users) {
    const tbody = document.getElementById('users-body');
    if (!users.length) { tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">👥</div><p>No users found</p></div></td></tr>'; return; }
    tbody.innerHTML = users.map((u, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td>
          ${(u.role === 'Event Manager' || u.role === 'Sponsor') 
            ? `<a href="/user-profile?id=${u.id}" style="display:flex;align-items:center;gap:8px; text-decoration:none; color:inherit; transition:opacity 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'" title="View Profile">` 
            : `<div style="display:flex;align-items:center;gap:8px">`
          }
            <span style="font-weight:500; ${(u.role === 'Event Manager' || u.role === 'Sponsor') ? 'color:var(--accent);' : ''}">${u.name}</span>
          ${(u.role === 'Event Manager' || u.role === 'Sponsor') ? `</a>` : `</div>`}
        </td>
        <td style="color:var(--text-muted)">${u.email}</td>
        <td>${roleBadge(u.role)}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(u.created_at)}</td>
        <td>
          <div style="display:flex; gap:8px;">
            ${u.role !== 'Admin' ? (
              u.is_active 
                ? `<button class="btn btn-warning btn-sm" onclick="toggleUserStatus(${u.id}, '${u.name}', false)">⏸ Suspend</button>` 
                : `<button class="btn btn-success btn-sm" onclick="toggleUserStatus(${u.id}, '${u.name}', true)">▶ Activate</button>`
            ) : ''}
            ${u.role !== 'Admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id}, '${u.name}')">🗑 Delete</button>` : ''}
          </div>
        </td>
      </tr>`).join('');
  }

  function filterTable() {
    const q = document.getElementById('search-input').value.toLowerCase();
    renderUsers(allUsers.filter(u => u.name.toLowerCase().includes(q) || u.email.toLowerCase().includes(q) || u.role.toLowerCase().includes(q)));
  }

  async function toggleUserStatus(id, name, isActivate) {
    const action = isActivate ? 'activate' : 'suspend';
    if (!confirm(`Are you sure you want to ${action} user "${name}"?
Suspended users will be logged out and unable to log back in.`)) return;
    const res = await api.patch(`/analytics/users/${id}/status`);
    if (res.ok) { showToast(`User ${action}d successfully.`, 'success'); loadUsers(); }
    else showToast(res.data?.message || 'Error updating status', 'error');
  }

  async function deleteUser(id, name) {
    if (!confirm(`Delete user "${name}"? This cannot be undone.`)) return;
    const res = await api.delete(`/analytics/users/${id}`);
    if (res.ok) { showToast('User deleted', 'success'); loadUsers(); }
    else showToast(res.data?.message || 'Error', 'error');
  }

  function openModal() { document.getElementById('user-modal').classList.add('open'); }
  function closeModal() { document.getElementById('user-modal').classList.remove('open'); document.getElementById('user-form').reset(); }

  document.getElementById('user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.textContent = 'Creating...'; btn.disabled = true;

    const body = {
      name: document.getElementById('u-name').value,
      email: document.getElementById('u-email').value,
      password: document.getElementById('u-pass').value,
      role: document.getElementById('u-role').value
    };

    const res = await api.post('/users', body);
    if (res.ok) {
      showToast('User created successfully!', 'success');
      closeModal();
      loadUsers();
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error creating user';
      showToast(msg, 'error');
    }
    btn.textContent = 'Create User'; btn.disabled = false;
  });
</script>
</body>
</html>
