<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sponsor Dashboard – EventHub</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <style>
    /* Toggle Switch CSS */
    .switch {
      position: relative;
      display: inline-block;
      width: 50px;
      height: 26px;
    }
    .switch input { 
      opacity: 0; width: 0; height: 0;
    }
    .slider {
      position: absolute;
      cursor: pointer;
      top: 0; left: 0; right: 0; bottom: 0;
      background-color: rgba(255,255,255,0.1);
      transition: .4s;
      border-radius: 34px;
    }
    .slider:before {
      position: absolute;
      content: "";
      height: 18px; width: 18px;
      left: 4px; bottom: 4px;
      background-color: white;
      transition: .4s;
      border-radius: 50%;
    }
    input:checked + .slider {
      background-color: var(--success);
    }
    input:checked + .slider:before {
      transform: translateX(24px);
    }
    .availability-card {
        margin-bottom: 28px;
        padding: 24px;
        border-radius: var(--radius);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--bg-card);
        border: 1px solid var(--border);
        transition: all 0.3s ease;
    }
    .availability-card.active-status {
        border-color: rgba(34, 197, 94, 0.3);
        background: rgba(34, 197, 94, 0.03);
    }
  </style>
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
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Sponsor Dashboard</h1><p class="page-subtitle">Your sponsorship activities at a glance</p></div>
      <div class="topbar-actions">
        <a href="/sponsor/requests" class="btn btn-primary">Browse Requests</a>
      </div>
    </div>

    <!-- Shown only when profile.is_available is false (DB), after /profile load -->
    <div id="hidden-alert" style="display:none; background:rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); padding: 16px; border-radius: var(--radius); margin-bottom: 24px; color: #ff6b6b; font-weight: 500;">
      <div style="display:flex; align-items:flex-start; gap:12px;">
        <span style="font-size:1.5rem;">⚠️</span>
        <p id="hidden-alert-text" style="margin:0; font-size:0.95rem; line-height:1.5;">
          You are currently not visible to event managers.<br/>
          You will remain hidden until you turn this option ON again.
        </p>
      </div>
    </div>

    <!-- Source of Truth: This section only displays once fetched from DB -->
    <div class="availability-card" id="avail-card" style="display:none">
      <div>
        <h3 id="avail-title" style="margin:0; font-size:1.1rem; font-weight:700;">Open to Sponsorship</h3>
        <p id="avail-desc" style="margin:4px 0 0; font-size:0.85rem; color:var(--text-muted);">You are visible to event managers and can browse opportunities.</p>
      </div>
      <div style="display:flex; align-items:center; gap:12px;">
        <span id="avail-badge" class="badge">--</span>
        <label class="switch">
          <input type="checkbox" id="avail-toggle" disabled onchange="handleToggle(this.checked)">
          <span class="slider"></span>
        </label>
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
          <thead><tr><th>Event</th><th>Manager</th><th>Venue</th><th>Date</th><th>Status</th></tr></thead>
          <tbody id="my-body">
            <tr class="loading-row"><td colspan="5"><div class="spinner" style="margin:auto"></div></td></tr>
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
  if (user) { 
    populateSidebar(user); 
    setActiveNav(); 
    // AUTHORITATIVE: Fetch from DB before showing anything
    fetchAvailabilityStatus();
  }

  async function fetchAvailabilityStatus() {
    const res = await api.get('/profile');
    if (!res.ok || !res.data.user?.profile) {
      showToast('Could not load your visibility settings.', 'error');
      return;
    }
    localStorage.setItem('user', JSON.stringify(res.data.user));
    const fromDb = availabilityFromDatabase(res.data.user.profile.is_available);
    if (fromDb === null) {
      showToast('Could not read visibility from the server.', 'error');
      return;
    }
    syncToggleUI(fromDb);
    loadMySponsored();
  }

  function syncToggleUI(isON) {
    const card = document.getElementById('avail-card');
    const title = document.getElementById('avail-title');
    const desc = document.getElementById('avail-desc');
    const badge = document.getElementById('avail-badge');
    const alertBox = document.getElementById('hidden-alert');
    const toggle = document.getElementById('avail-toggle');

    toggle.checked = isON;
    toggle.disabled = false;

    if (isON) {
      card.classList.add('active-status');
      card.style.borderColor = 'rgba(34, 197, 94, 0.3)';
      card.style.background = 'rgba(34, 197, 94, 0.03)';
      title.innerText = 'Open to Sponsorship';
      title.style.color = 'var(--success)';
      desc.innerText = 'You are currently visible to event managers and can browse opportunities.';
      badge.innerText = 'Available';
      badge.className = 'badge badge-approved';
      alertBox.style.display = 'none';
    } else {
      card.classList.remove('active-status');
      card.style.borderColor = 'var(--border)';
      card.style.background = 'var(--bg-card)';
      title.innerText = 'Closed to Sponsorship';
      title.style.color = 'var(--text-muted)';
      desc.innerText = 'You are currently not visible to event managers.';
      badge.innerText = 'Not Available';
      badge.className = 'badge badge-rejected';
      alertBox.style.display = 'block';
    }
    
    card.style.display = 'flex';
  }

  async function handleToggle(isNowChecked) {
    const toggle = document.getElementById('avail-toggle');
    toggle.disabled = true;
    try {
      const res = await api.patch('/profile/availability', { is_available: !!isNowChecked });
      if (res.ok) {
          const raw = res.data.is_available !== undefined && res.data.is_available !== null
            ? res.data.is_available
            : res.data.user?.profile?.is_available;
          const dbStatus = availabilityFromDatabase(raw);
          if (dbStatus === null) {
            showToast('Invalid response from server.', 'error');
            await fetchAvailabilityStatus();
            return;
          }
          if (res.data.user) {
            localStorage.setItem('user', JSON.stringify(res.data.user));
          }
          syncToggleUI(dbStatus);
          showToast(dbStatus ? 'You are now visible.' : 'You are now hidden.', dbStatus ? 'success' : 'info');
      } else {
          showToast('Failed to sync with server', 'error');
          await fetchAvailabilityStatus();
      }
    } finally {
      toggle.disabled = false;
    }
  }

  async function loadMySponsored() {
    const res = await api.get('/sponsorship');
    const tbody = document.getElementById('my-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
    const all = res.data;
    const mine = all.filter(r => r.sponsor_id === JSON.parse(localStorage.getItem('user')).id);
    const open = all.filter(r => r.status === 'pending' && (!r.sponsor_id || r.sponsor_id === 0));

    document.getElementById('stat-accepted').textContent = mine.filter(r => r.status === 'accepted').length;
    document.getElementById('stat-pending').textContent  = mine.filter(r => r.status === 'pending').length;
    document.getElementById('stat-rejected').textContent = mine.filter(r => r.status === 'rejected').length;
    document.getElementById('stat-open').textContent     = open.length;

    if (!mine.length) { tbody.innerHTML = '<tr><td colspan="5"><div class="empty-state"><div class="empty-icon">💼</div><p>No sponsorships yet. <a href="/sponsor/requests">Browse open requests!</a></p></div></td></tr>'; return; }
    tbody.innerHTML = mine.map(r => `
      <tr>
        <td><div style="font-weight:600">${r.event?.title || '—'}</div></td>
        <td>
            <div style="display:flex; align-items:center; gap:8px; cursor:pointer;" onclick="navigateToProfile(${r.manager?.id})">
                <div class="avatar avatar-sm" style="width:24px; height:24px; font-size:0.7rem;">${r.manager?.name?.charAt(0) || 'M'}</div>
                <span style="font-size:0.85rem;">${r.manager?.name || '—'}</span>
            </div>
        </td>
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
