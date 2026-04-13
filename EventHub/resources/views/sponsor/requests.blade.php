<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Requests – EventHub Sponsor</title>
  <link rel="stylesheet" href="/css/style.css"/>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Opportunities</span>
      <a class="nav-item" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
      <a class="nav-item active" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      <a class="nav-item" href="/sponsor/history"><span class="nav-icon">📜</span> History</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsorship Requests</h1><p class="page-subtitle">Browse and respond to open sponsorship opportunities</p></div>
      <div class="topbar-actions">
        <select id="filter-status" class="form-control" style="width:160px" onchange="applyFilter()">
          <option value="pending">Open Requests</option>
          <option value="">All Requests</option>
          <option value="accepted">Accepted</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <!-- Request Cards -->
    <div id="cards-container" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px">
      <div style="grid-column:1/-1"><div class="loading-row card"><div class="spinner" style="margin:auto"></div></div></div>
    </div>
  </main>
</div>
<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  let allRequests = [];
  const user = requireRole('Sponsor');
  if (user) { populateSidebar(user); setActiveNav(); loadRequests(); }

  async function loadRequests() {
    const res = await api.get('/sponsorship');
    if (!res.ok) { document.getElementById('cards-container').innerHTML = '<div style="grid-column:1/-1;text-align:center;color:var(--danger)">Failed to load</div>'; return; }
    allRequests = res.data;
    applyFilter();
  }

  function applyFilter() {
    const f = document.getElementById('filter-status').value;
    const filtered = f ? allRequests.filter(r => r.status === f) : allRequests;
    renderCards(filtered);
  }

  function renderCards(requests) {
    const container = document.getElementById('cards-container');
    if (!requests.length) {
      container.innerHTML = '<div style="grid-column:1/-1"><div class="empty-state"><div class="empty-icon">💼</div><p>No requests found</p></div></div>';
      return;
    }
    container.innerHTML = requests.map(r => `
      <div class="card" style="position:relative">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px">
          <div>
            <div style="font-weight:700;font-size:1rem">${r.event?.title || 'Untitled Event'}</div>
            <div style="font-size:.78rem;color:var(--text-muted);margin-top:2px">📍 ${r.event?.venue?.name || '—'} &nbsp;·&nbsp; 🗓 ${fmtDateShort(r.event?.start_time)}</div>
          </div>
          <div style="display:flex; align-items:center; gap:8px">
            ${r.initiator === 'event_manager' 
                ? '<span style="font-size:11px; padding:3px 8px; border-radius:12px; background:rgba(59,130,246,0.1); color:#3b82f6; border:1px solid rgba(59,130,246,0.2);">📥 Received</span>' 
                : '<span style="font-size:11px; padding:3px 8px; border-radius:12px; background:rgba(139,92,246,0.1); color:#8b5cf6; border:1px solid rgba(139,92,246,0.2);">📤 Sent</span>'}
            ${r.status === 'accepted' ? `<a href="/storage/agreements/agreement_${r.id}.pdf" target="_blank" style="font-size:12px;text-decoration:none">📄 PDF</a>` : ''}
            ${badge(r.status)}
          </div>
        </div>
        ${r.message ? `<p style="font-size:.85rem;color:var(--text-muted);margin-bottom:14px;line-height:1.5">${r.message}</p>` : ''}
        
        <div style="margin-bottom:14px; background:rgba(255,255,255,0.02); padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.05); display:flex; justify-content:space-between; align-items:center;">
            <div>
                <div style="font-size:0.7rem; text-transform:uppercase; color:var(--text-muted); letter-spacing:0.05em; margin-bottom:4px;">Event Manager</div>
                <div style="font-size:0.9rem; font-weight:600; color:#fff; display:flex; align-items:center; gap:6px;">
                    👤 ${r.event?.creator?.name || r.manager?.name || '—'}
                </div>
            </div>
            <button class="btn btn-ghost btn-sm" onclick="navigateToProfile(${r.event?.creator?.id || r.manager?.id})" style="font-size:12px; padding:4px 10px;">Message</button>
        </div>
        ${r.status === 'pending' ? `
          <div style="display:flex;gap:8px">
            ${r.initiator === 'event_manager' ? `
                <button class="btn btn-success" style="flex:1" onclick="respond(${r.id}, 'accepted')">✅ Accept</button>
                <button class="btn btn-danger" style="flex:1" onclick="respond(${r.id}, 'rejected')">❌ Decline</button>
            ` : `
                <div style="flex:1; text-align: center; font-size: 12px; color: var(--text-muted); background: #eef2f5; padding: 6px; border-radius: 4px;">⏳ Awaiting Event Manager to respond</div>
            `}
          </div>` : ''}
      </div>`).join('');
  }

  async function respond(id, status) {
    const res = await api.put(`/sponsorship/${id}`, { status });
    if (res.ok) { showToast(status === 'accepted' ? 'Sponsorship accepted! 🎉' : 'Request declined.', status === 'accepted' ? 'success' : 'info'); loadRequests(); }
    else showToast(res.data?.message || 'Error', 'error');
  }
</script>
</body>
</html>
