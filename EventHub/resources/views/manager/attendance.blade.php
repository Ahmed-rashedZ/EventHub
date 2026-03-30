<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Events</span>
      <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item active" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">M</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">Manager</div><div class="user-role" id="sidebar-role">Event Manager</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Attendance Tracking</h1><p class="page-subtitle">Live attendance for your events</p></div>
      <div class="topbar-actions">
        <select id="event-picker" class="form-control" style="width:260px" onchange="loadAttendance()">
          <option value="">Select an event…</option>
        </select>
        <button class="btn btn-ghost" onclick="loadAttendance()" title="Refresh">🔄</button>
      </div>
    </div>

    <!-- Stats -->
    <div class="stats-grid" id="attend-stats" style="display:none">
      <div class="stat-card"><div class="stat-label">Registered</div><div class="stat-value" id="a-registered">0</div><div class="stat-icon">🎟️</div></div>
      <div class="stat-card"><div class="stat-label">Checked In</div><div class="stat-value" id="a-attended">0</div><div class="stat-icon">✅</div></div>
      <div class="stat-card"><div class="stat-label">Not Yet Scanned</div><div class="stat-value" id="a-remaining">0</div><div class="stat-icon">⏳</div></div>
      <div class="stat-card">
        <div class="stat-label">Attendance Rate</div>
        <div class="stat-value" id="a-rate">0%</div>
        <div class="stat-icon">📊</div>
        <div class="progress-bar-bg" style="margin-top:10px"><div class="progress-bar-fill" id="a-progress" style="width:0%"></div></div>
      </div>
    </div>

    <!-- Participants table -->
    <div class="card" id="participants-card" style="display:none">
      <div class="card-header"><span class="card-title">Participants</span></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Attendee</th><th>Ticket Code</th><th>Status</th><th>Scanned At</th></tr></thead>
          <tbody id="participants-body">
            <tr class="loading-row"><td colspan="5"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div id="pick-prompt" class="empty-state" style="margin-top:60px">
      <div class="empty-icon">📍</div>
      <p>Select an event above to view attendance</p>
    </div>
  </main>
</div>
<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); }

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const sel = document.getElementById('event-picker');
    if (!res.ok || !res.data.length) { sel.innerHTML = '<option value="">No approved events</option>'; return; }
    const approved = res.data.filter(e => e.status === 'approved');
    sel.innerHTML = '<option value="">Select an event…</option>' + approved.map(e => `<option value="${e.id}">${e.title} (${fmtDateShort(e.start_time)})</option>`).join('');
  }

  async function loadAttendance() {
    const id = document.getElementById('event-picker').value;
    if (!id) return;
    document.getElementById('pick-prompt').style.display = 'none';
    document.getElementById('attend-stats').style.display = 'grid';
    document.getElementById('participants-card').style.display = 'block';

    // Stats
    const statsRes = await api.get(`/analytics/event/${id}`);
    if (statsRes.ok) {
      const d = statsRes.data;
      document.getElementById('a-registered').textContent = d.registered_count;
      document.getElementById('a-attended').textContent   = d.attended_count;
      document.getElementById('a-remaining').textContent  = d.registered_count - d.attended_count;
      document.getElementById('a-rate').textContent       = d.attendance_rate + '%';
      document.getElementById('a-progress').style.width   = d.attendance_rate + '%';
    }

    // Participants
    const partRes = await api.get(`/checkin/event/${id}`);
    const tbody = document.getElementById('participants-body');
    if (!partRes.ok || !partRes.data.length) { tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><div class="empty-icon">🎟️</div><p>No registrations yet</p></div></td></tr>'; return; }
    tbody.innerHTML = partRes.data.map((t, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td>
          <div style="display:flex;align-items:center;gap:8px">
            <div class="avatar" style="width:26px;height:26px;font-size:.7rem">${t.user?.name?.charAt(0) || '?'}</div>
            <div><div style="font-weight:500">${t.user?.name || '—'}</div><div style="font-size:.72rem;color:var(--text-muted)">${t.user?.email || ''}</div></div>
          </div>
        </td>
        <td style="font-family:monospace;color:var(--accent2)">${t.qr_code}</td>
        <td>${badge(t.status)}</td>
        <td style="color:var(--text-muted)">${t.attendance_log ? fmtDate(t.attendance_log.scanned_at) : '—'}</td>
      </tr>`).join('');
  }
</script>
</body>
</html>
