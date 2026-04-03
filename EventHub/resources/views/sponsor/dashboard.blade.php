<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sponsor Dashboard – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item active" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Opportunities</span>
      <a class="nav-item" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
      <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">S</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">Sponsor</div><div class="user-role" id="sidebar-role">Sponsor</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsor Dashboard</h1><p class="page-subtitle">Your sponsorship activities at a glance</p></div>
      <div class="topbar-actions">
        <a href="/sponsor/requests" class="btn btn-primary">Browse Requests</a>
      </div>
    </div>

    <div class="stats-grid">
      <div class="stat-card"><div class="stat-label">Accepted</div><div class="stat-value" id="stat-accepted">—</div><div class="stat-icon">✅</div></div>
      <div class="stat-card"><div class="stat-label">Pending</div><div class="stat-value" id="stat-pending">—</div><div class="stat-icon">⏳</div></div>
      <div class="stat-card"><div class="stat-label">Rejected</div><div class="stat-value" id="stat-rejected">—</div><div class="stat-icon">❌</div></div>
      <div class="stat-card"><div class="stat-label">Open Requests</div><div class="stat-value" id="stat-open">—</div><div class="stat-icon">📬</div></div>
    </div>

    <div class="card">
      <div class="card-header">
        <span class="card-title">My Sponsorships</span>
        <a href="/sponsor/requests" class="btn btn-ghost btn-sm">View All Open</a>
      </div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Event</th><th>Venue</th><th>Date</th><th>Status</th></tr></thead>
          <tbody id="my-body">
            <tr class="loading-row"><td colspan="4"><div class="spinner" style="margin:auto"></div></td></tr>
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
  const user = requireRole('Sponsor');
  if (user) { populateSidebar(user); setActiveNav(); loadMySponsored(); }

  async function loadMySponsored() {
    const res = await api.get('/sponsorship');
    const tbody = document.getElementById('my-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="4" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
    const all = res.data;
    const mine = all.filter(r => r.sponsor_id === JSON.parse(localStorage.getItem('user')).id);
    const open = all.filter(r => r.status === 'pending' && (!r.sponsor_id || r.sponsor_id === 0));

    document.getElementById('stat-accepted').textContent = mine.filter(r => r.status === 'accepted').length;
    document.getElementById('stat-pending').textContent  = mine.filter(r => r.status === 'pending').length;
    document.getElementById('stat-rejected').textContent = mine.filter(r => r.status === 'rejected').length;
    document.getElementById('stat-open').textContent     = open.length;

    if (!mine.length) { tbody.innerHTML = '<tr><td colspan="4"><div class="empty-state"><div class="empty-icon">💼</div><p>No sponsorships yet. <a href="/sponsor/requests">Browse open requests!</a></p></div></td></tr>'; return; }
    tbody.innerHTML = mine.map(r => `
      <tr>
        <td><div style="font-weight:600">${r.event?.title || '—'}</div></td>
        <td style="color:var(--text-muted)">${r.event?.venue?.name || '—'}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(r.event?.start_time)}</td>
        <td>
          ${badge(r.status)}
          ${r.status === 'accepted' ? `<a href="/storage/agreements/agreement_${r.id}.pdf" target="_blank" style="margin-left:8px;font-size:12px;text-decoration:none">📄 PDF</a>` : ''}
        </td>
      </tr>`).join('');
  }
</script>
</body>
</html>
