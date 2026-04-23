<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Events – EventHub Admin</title>
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
      <a class="nav-item" href="/admin/users"><span class="nav-icon">👥</span> Users</a>
      <a class="nav-item active" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Events</h1><p class="page-subtitle">Approve, reject and monitor all events</p></div>
      <div class="topbar-actions" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
        <div style="position:relative">
          <input id="search-input" type="text" class="form-control" placeholder="Search by event or manager name..." style="width:240px;padding-left:36px" oninput="applyFilter()">
          <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
        <select id="filter-status" class="form-control" style="width:145px" onchange="applyFilter()">
          <option value="">All Status</option>
          <option value="pending" selected>Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <div style="position:relative;display:flex;align-items:center">
          <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M7 12h10M11 18h2"/></svg>
          <select id="sort-events" class="form-control" style="width:190px;padding-left:32px" onchange="applyFilter()">
            <option value="soonest">Soonest First</option>
            <option value="farthest">Farthest First</option>
            <option value="alpha">Alphabetical</option>
            <option value="live">Live Now</option>
            <option value="ended">Ended</option>
          </select>
        </div>
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
    const f   = document.getElementById('filter-status').value;
    const q   = (document.getElementById('search-input').value || '').toLowerCase().trim();
    const s   = document.getElementById('sort-events').value;
    const now = new Date();

    // 1. Filter by status
    let filtered = f ? allEvents.filter(e => e.status === f) : [...allEvents];

    // 2. Filter by search
    if (q) {
      filtered = filtered.filter(e => {
        const title   = (e.title || '').toLowerCase();
        const manager = (e.creator?.name || '').toLowerCase();
        return title.includes(q) || manager.includes(q);
      });
    }

    // 3. Filter by time when sort is live/ended
    if (s === 'live') {
      filtered = filtered.filter(e => {
        const start = new Date(e.start_time);
        const end   = new Date(e.end_time);
        return start <= now && end >= now;
      });
    } else if (s === 'ended') {
      filtered = filtered.filter(e => new Date(e.end_time) < now);
    }

    // 4. Sort
    if (s === 'soonest') {
      filtered.sort((a, b) => new Date(b.start_time) - new Date(a.start_time));
    } else if (s === 'farthest') {
      filtered.sort((a, b) => new Date(a.start_time) - new Date(b.start_time));
    } else if (s === 'alpha') {
      filtered.sort((a, b) => (a.title || '').localeCompare(b.title || '', 'ar'));
    } else if (s === 'live') {
      filtered.sort((a, b) => new Date(a.end_time) - new Date(b.end_time));
    } else if (s === 'ended') {
      filtered.sort((a, b) => new Date(b.end_time) - new Date(a.end_time));
    }

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
        <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</td>
        <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${ev.id})" title="View Details">ℹ️ Details</button>
          <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="window.location.href='/admin/event-stats/${ev.id}'" title="View Statistics">📊 Stats</button>
          ${ev.status === 'pending' ? `<button class="btn btn-success btn-sm" onclick="approve(${ev.id})">✓ Approve</button><button class="btn btn-danger btn-sm" onclick="reject(${ev.id})">✕ Reject</button>` : ''}
        </td>
      </tr>`).join('');
  }

  async function approve(id) {
    const res = await api.put(`/events/${id}/approve`);
    if (res.ok) { showToast('Event approved!', 'success'); loadEvents(); }
    else showToast(res.data?.message || 'Error', 'error');
  }

  function reject(id) {
    document.getElementById('reject-event-id').value = id;
    document.getElementById('rejection-modal').classList.add('open');
  }

  async function submitRejection(e) {
    e.preventDefault();
    const id = document.getElementById('reject-event-id').value;
    const reason = document.getElementById('r-reason').value;
    
    const res = await api.put(`/events/${id}/reject`, { rejection_reason: reason });
    if (res.ok) { 
      showToast('Event rejected.', 'info'); 
      closeRejectionModal();
      loadEvents(); 
    }
    else showToast(res.data?.message || 'Error', 'error');
  }

  function closeRejectionModal() {
    document.getElementById('rejection-modal').classList.remove('open');
    document.getElementById('rejection-form').reset();
  }

  const typeIcons  = { 'مؤتمر':'🎙️', 'ندوة':'📖', 'ورشة عمل':'🔧', 'دورة تدريبية':'🎓', 'ترفيه':'🎭', 'ملتقى علمي':'🔬', 'رياضة':'⚽', 'تقنية':'💻', 'اجتماعية':'🤝' };
  const typeColors = { 'مؤتمر':'#3b82f6', 'ندوة':'#8b5cf6', 'ورشة عمل':'#10b981', 'دورة تدريبية':'#06b6d4', 'ترفيه':'#ec4899', 'ملتقى علمي':'#f59e0b', 'رياضة':'#22c55e', 'تقنية':'#6366f1', 'اجتماعية':'#f97316' };

  function showEventDetails(eventId) {
    const modal = document.getElementById('event-details-modal');
    const content = document.getElementById('event-details-content');
    modal.classList.add('open');
    content.innerHTML = '<div class="spinner" style="margin:auto"></div>';
    api.get(`/events/${eventId}`).then(res => {
      if (!res.ok) {
        content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch event details</p></div>';
        return;
      }
      const ev = res.data;
      const eType = ev.event_type || 'Other';
      const tColor = typeColors[eType] || typeColors.Other;
      const tIcon  = typeIcons[eType]  || '📌';

      const bannerSection = ev.image
        ? `<div class="ed-banner" style="background-image:url('/storage/${ev.image}')"><div class="ed-banner-fade"></div></div>`
        : `<div class="ed-banner ed-banner-placeholder"><span class="ed-banner-emoji">${tIcon}</span><div class="ed-banner-fade"></div></div>`;

      const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
        ? `<div class="ed-rejection"><span class="ed-rej-label">⚠ Rejection Reason</span><p>${ev.rejection_reason}</p></div>`
        : '';

      let sponsorsHtml = '';
      if (ev.sponsors && ev.sponsors.length > 0) {
          const getTierBadge = (tier) => {
              switch (tier) {
                  case 'diamond': return '<span style="background:rgba(6,182,212,0.15); color:#06b6d4; padding:3px 8px; border-radius:12px; border:1px solid rgba(6,182,212,0.3); font-size:10px;">💎 Diamond</span>';
                  case 'gold': return '<span style="background:rgba(234,179,8,0.15); color:#eab308; padding:3px 8px; border-radius:12px; border:1px solid rgba(234,179,8,0.3); font-size:10px;">🥇 Gold</span>';
                  case 'silver': return '<span style="background:rgba(156,163,175,0.15); color:#9ca3af; padding:3px 8px; border-radius:12px; border:1px solid rgba(156,163,175,0.3); font-size:10px;">🥈 Silver</span>';
                  case 'bronze': return '<span style="background:rgba(217,119,6,0.15); color:#d97706; padding:3px 8px; border-radius:12px; border:1px solid rgba(217,119,6,0.3); font-size:10px;">🥉 Bronze</span>';
                  default: return `<span style="background:rgba(255,255,255,0.1); color:#fff; padding:3px 8px; border-radius:12px; border:1px solid rgba(255,255,255,0.2); font-size:10px;">${tier || 'Sponsor'}</span>`;
              }
          };

          sponsorsHtml = `
            <div class="ed-section mt-4" style="margin-top: 16px;">
              <div class="ed-section-label">Current Sponsors</div>
              <div style="display:flex; flex-direction:column; gap:8px;">
                ${ev.sponsors.map(sp => `
                   <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="navigateToProfile(${sp.id})">
                      <div class="avatar" style="width:36px; height:36px; font-size:14px; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); border-radius:50%; overflow:hidden;">
                          ${(() => {
                             const src = sp.image || sp.avatar || sp.profile?.logo;
                             if (src) {
                               const fullSrc = (src.startsWith('http') || src.startsWith('/')) ? src : '/storage/' + src;
                               return `<img src="${fullSrc}" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none'; this.parentElement.innerText='${sp.name?.charAt(0).toUpperCase() || '?'}'">`;
                             }
                             return sp.name ? sp.name.charAt(0).toUpperCase() : '?';
                          })()}
                      </div>
                      <div style="flex:1">
                          <div style="font-size:0.85rem; font-weight:600; color:#fff;">${sp.profile?.company_name || sp.name}</div>
                          <div style="margin-top: 2px;">${getTierBadge(sp.pivot?.tier)}</div>
                      </div>
                   </div>
                `).join('')}
              </div>
            </div>
          `;
      }

      content.innerHTML = `
        ${bannerSection}
        <div class="ed-body">

          <div class="ed-header">
            <div class="ed-title-row">
              <h2 class="ed-title">${ev.title}</h2>
              <span class="ed-type-pill" style="--tcolor:${tColor}">${tIcon} ${eType}</span>
            </div>
            <div class="ed-badges">
              ${ev.status ? badge(ev.status) : ''}
              ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
            </div>
          </div>

          ${rejectionSection}

          <div class="ed-section">
            <div class="ed-section-label">About this Event</div>
            <p class="ed-description">${ev.description || 'No description provided.'}</p>
          </div>

          <div class="ed-info-grid">
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">🏛️</div>
              <div><div class="ed-info-label">Venue</div><div class="ed-info-value">${ev.venue?.name || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent2">
              <div class="ed-info-icon">📍</div>
              <div><div class="ed-info-label">Location</div><div class="ed-info-value">${ev.venue?.location || '—'}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent">
              <div class="ed-info-icon">🕐</div>
              <div><div class="ed-info-label">Start</div><div class="ed-info-value">${fmtDate(ev.start_time)}</div></div>
            </div>
            <div class="ed-info-card ed-info-accent">
              <div class="ed-info-icon">🕔</div>
              <div><div class="ed-info-label">End</div><div class="ed-info-value">${fmtDate(ev.end_time)}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">👥</div>
              <div><div class="ed-info-label">Capacity</div><div class="ed-info-value">${ev.capacity}</div></div>
            </div>
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">🎟️</div>
              <div><div class="ed-info-label">Tickets Booked</div><div class="ed-info-value">${ev.tickets_count ?? '—'}</div></div>
            </div>
          </div>
          
          ${sponsorsHtml}

          <div class="ed-footer" style="margin-top: 8px;">
            <span class="ed-footer-label">Created by</span>
            <span class="ed-footer-name">${ev.creator?.name || ev.manager?.name || '—'}</span>
          </div>

        </div>
      `;
    });
  }

  function closeEventDetailsModal() {
    document.getElementById('event-details-modal').classList.remove('open');
    document.getElementById('event-details-content').innerHTML = '';
  }


</script>

<!-- Event Details Modal -->
<div class="modal-overlay" id="event-details-modal">
  <div class="modal ed-modal">
    <button class="ed-close-btn" onclick="closeEventDetailsModal()">✕</button>
    <div id="event-details-content" class="ed-content"></div>
  </div>
</div>

<style>
/* ── Event Details Modal ───────────────────────────── */
.ed-modal {
  max-width: 560px;
  width: 95%;
  padding: 0;
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 32px 80px rgba(0,0,0,0.6);
  background: #13131f;
  max-height: 90vh;
  display: flex;
  flex-direction: column;
}
.ed-close-btn {
  position: absolute;
  top: 14px; right: 14px;
  z-index: 20;
  background: rgba(0,0,0,0.4);
  border: 1px solid rgba(255,255,255,0.15);
  color: #fff;
  width: 32px; height: 32px;
  border-radius: 50%;
  font-size: 1rem;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center;
  transition: background 0.2s;
}
.ed-close-btn:hover { background: rgba(255,255,255,0.15); }
.ed-content { position: relative; display: flex; flex-direction: column; max-height: 90vh; }
.ed-banner {
  width: 100%; height: 200px;
  background-size: cover; background-position: center;
  position: relative;
  flex-shrink: 0;
}
.ed-banner-placeholder {
  background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
  display: flex; align-items: center; justify-content: center;
}
.ed-banner-emoji { font-size: 4.5rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.5)); }
.ed-banner-fade {
  position: absolute;
  bottom: 0; left: 0; right: 0;
  height: 80px;
  background: linear-gradient(to bottom, transparent, #13131f);
}
.ed-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 20px; overflow-y: auto; }
.ed-header { display: flex; flex-direction: column; gap: 10px; }
.ed-title-row { display: flex; align-items: flex-start; gap: 12px; flex-wrap: wrap; }
.ed-title { margin: 0; font-size: 1.55rem; font-weight: 800; color: #fff; line-height: 1.2; flex: 1; min-width: 0; }
.ed-type-pill {
  display: inline-flex; align-items: center; gap: 5px;
  background: color-mix(in srgb, var(--tcolor) 18%, transparent);
  color: var(--tcolor);
  border: 1px solid color-mix(in srgb, var(--tcolor) 40%, transparent);
  padding: 4px 12px; border-radius: 20px;
  font-size: 0.78rem; font-weight: 700;
  text-transform: uppercase; letter-spacing: 0.06em;
  white-space: nowrap; flex-shrink: 0;
}
.ed-badges { display: flex; gap: 8px; flex-wrap: wrap; }
.ed-rejection { background: rgba(239,68,68,0.09); border-left: 3px solid #ef4444; border-radius: 8px; padding: 12px 14px; }
.ed-rej-label { display: block; font-size: 0.72rem; font-weight: 700; color: #ef4444; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
.ed-rejection p { margin: 0; color: #e2e8f0; font-size: 0.9rem; line-height: 1.5; }
.ed-section-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; color: rgba(255,255,255,0.35); margin-bottom: 6px; }
.ed-description { margin: 0; color: rgba(255,255,255,0.75); font-size: 0.95rem; line-height: 1.7; }
.ed-info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
.ed-info-card { background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.07); border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px; transition: background 0.2s; }
.ed-info-card:hover { background: rgba(255,255,255,0.07); }
.ed-info-icon { font-size: 1.3rem; flex-shrink: 0; }
.ed-info-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 2px; }
.ed-info-value { font-weight: 600; font-size: 0.88rem; color: #fff; }
.ed-info-accent  .ed-info-label { color: var(--accent); }
.ed-info-accent2 .ed-info-label { color: var(--accent2); }
.ed-info-warning .ed-info-label { color: var(--warning); }
.ed-footer { display: flex; align-items: center; gap: 8px; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.06); }
.ed-footer-label { font-size: 0.8rem; color: rgba(255,255,255,0.35); }
.ed-footer-name  { font-size: 0.85rem; font-weight: 600; color: #fff; }
</style>



<!-- Rejection Modal -->
<div class="modal-overlay" id="rejection-modal">
  <div class="modal" style="max-width:400px">
    <div class="modal-header">
      <h3 class="modal-title">Reason for Rejection</h3>
      <button class="modal-close" onclick="closeRejectionModal()">✕</button>
    </div>
    <form id="rejection-form" onsubmit="submitRejection(event)">
      <input type="hidden" id="reject-event-id">
      <div class="form-group">
        <label class="form-label" style="font-size:0.75rem;">Explain why this event is being rejected</label>
        <textarea id="r-reason" class="form-control" rows="4" placeholder="e.g. Venue capacity mismatch, missing details..." required></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeRejectionModal()">Cancel</button>
        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
      </div>
    </form>
  </div>
</div>
</body>
</html>
