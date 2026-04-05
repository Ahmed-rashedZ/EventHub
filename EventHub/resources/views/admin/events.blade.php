<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/admin/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Management</span>
      <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item active" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Events</h1><p class="page-subtitle">Approve, reject and monitor all events</p></div>
      <div class="topbar-actions">
        <select id="filter-status" class="form-control" style="width:160px" onchange="applyFilter()">
          <option value="">All Events</option>
          <option value="pending" selected>Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Title</th><th>Venue</th><th>Manager</th><th>Start</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="events-body">
            <tr class="loading-row"><td colspan="8"><div class="spinner" style="margin:auto"></div></td></tr>
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
  let allEvents = [];
  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); }

  async function loadEvents() {
    const res = await api.get('/events/list/all');
    const tbody = document.getElementById('events-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
    allEvents = res.data;
    applyFilter();
  }

  function applyFilter() {
    const f = document.getElementById('filter-status').value;
    const filtered = f ? allEvents.filter(e => e.status === f) : allEvents;
    renderEvents(filtered);
  }

  function renderEvents(events) {
    const tbody = document.getElementById('events-body');
    if (!events.length) { tbody.innerHTML = '<tr><td colspan="8"><div class="empty-state"><div class="empty-icon">📅</div><p>No events found</p></div></td></tr>'; return; }
    tbody.innerHTML = events.map((ev, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${ev.title}</div></td>
        <td style="color:var(--text-muted)">${ev.venue?.name || '—'}</td>
        <td style="color:var(--text-muted)">${ev.creator?.name || '—'}</td>
        <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
        <td style="color:var(--text-muted)">${ev.capacity}</td>
        <td>${badge(ev.status)}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          ${ev.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="approve(${ev.id})">✓ Approve</button><button class="btn btn-danger btn-sm" onclick="reject(${ev.id})">✕ Reject</button>` : '—'}
        </td>
      </tr>`).join('');
  }

  async function approve(id) {
    const res = await api.put(`/events/${id}/approve`);
    if (res.ok) { showToast('Event approved!', 'success'); loadEvents(); }
    else showToast(res.data?.message || 'Error', 'error');
  }

  async function reject(id) {
    const res = await api.put(`/events/${id}/reject`);
    if (res.ok) { showToast('Event rejected.', 'info'); loadEvents(); }
    else showToast(res.data?.message || 'Error', 'error');
  }
</script>
</body>
</html>
