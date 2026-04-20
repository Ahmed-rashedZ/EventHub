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
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
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
          <div style="display:flex; gap:8px; align-items:center;">
            ${u.role !== 'Admin' ? (
              u.is_active 
                ? `<button class="btn btn-warning btn-sm" onclick="toggleUserStatus(${u.id}, '${u.name}', false)">⏸ Suspend</button>` 
                : `<button class="btn btn-success btn-sm" onclick="toggleUserStatus(${u.id}, '${u.name}', true)">▶ Activate</button>`
            ) : ''}
            ${u.role !== 'Admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id}, '${u.name}')">🗑 Delete</button>` : ''}
            ${(u.role === 'Event Manager' || u.role === 'Sponsor') && u.verification_status === 'verified' && u.verification_document ? 
              `<button class="btn btn-ghost btn-sm" style="background: rgba(139, 92, 246, 0.1); color: #a78bfa;" onclick="downloadDoc(${u.id}, '${u.name}')" title="View Verification File">📄 Docs</button>` : ''}
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
    else showToast(res.data?.message || 'Error deleting user', 'error');
  }

  async function downloadDoc(id, name) {
      showToast('Downloading document for ' + name + '...', 'info');
      try {
          const res = await fetch(`/api/verifications/${id}/document`, { headers: { 'Authorization': `Bearer ${localStorage.getItem('token')}` } });
          if (!res.ok) throw new Error('File not found');
          const blob = await res.blob();
          const urlObj = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.style.display = 'none';
          a.href = urlObj;
          a.download = `document_${id}`;
          document.body.appendChild(a);
          a.click();
          window.URL.revokeObjectURL(urlObj);
          showToast('Download complete', 'success');
      } catch(e) {
          showToast('Error downloading file, it may be unavailable.', 'error');
      }
  }


</script>
</body>
</html>
