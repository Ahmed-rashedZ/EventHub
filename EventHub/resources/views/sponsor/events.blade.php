<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Browse Events – EventHub Sponsor</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <style>
      .req-btn {
          padding: 6px 12px;
          font-size: 13px;
      }
      .availability-warning {
          background: #fff3cd;
          color: #856404;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
          border: 1px solid #ffeeba;
          display: none;
      }
      .availability-load-error {
          background: #fee2e2;
          color: #991b1b;
          padding: 15px;
          border-radius: 8px;
          margin-bottom: 20px;
          border: 1px solid #fecaca;
          display: none;
      }
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/sponsor/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Opportunities</span>
      <a class="nav-item active" href="/sponsor/events"><span class="nav-icon">🌍</span> Browse Events</a>
      <a class="nav-item" href="/sponsor/requests"><span class="nav-icon">💼</span> Sponsorships</a>
      <a class="nav-item" href="/sponsor/history"><span class="nav-icon">📜</span> History</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Browse Events</h1><p class="page-subtitle">Discover opportunities to sponsor upcoming verified events</p></div>
    </div>
    
    <div class="availability-load-error" id="availability-load-error">
      Could not load your visibility status from the server. Refresh the page or try again later.
    </div>

    <div class="availability-warning" id="availability-warning">
      You are currently not visible to event managers.<br/>
      You will remain hidden until you turn this option ON again.
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead>
              <tr>
                  <th>Event Name</th>
                  <th>Manager</th>
                  <th>Venue</th>
                  <th>Date</th>
                  <th>Capacity</th>
                  <th>Action</th>
              </tr>
          </thead>
          <tbody id="events-body">
            <tr class="loading-row"><td colspan="5"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Send Request Modal -->
<div class="modal-overlay" id="req-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title">Sponsor Event</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="req-form">
      <input type="hidden" id="r-event-id" value=""/>
      <div style="background:#f4f6f8; padding:15px; border-radius:6px; margin-bottom:15px;">
        <div style="font-size:12px; color:var(--text-muted)">Selected Event</div>
        <div id="r-event-title" style="font-weight:bold; font-size:16px;">--</div>
      </div>
      <div class="form-group">
        <label class="form-label">Message / Intro</label>
        <textarea id="r-message" class="form-control" placeholder="Introduce yourself and state what sponsorship options you are interested in..." rows="4"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="btn-submit">Send Request</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Sponsor');
  /** @type {boolean|null} null until loaded from GET /profile */
  let sponsorAvailability = null;

  if (user) {
      populateSidebar(user);
      setActiveNav();
      initSponsorEventsPage();
  }

  async function initSponsorEventsPage() {
      const res = await api.get('/profile');
      const warnEl = document.getElementById('availability-warning');
      const errEl = document.getElementById('availability-load-error');
      warnEl.style.display = 'none';
      errEl.style.display = 'none';

      let fresh = user;
      if (res.ok && res.data?.user) {
          fresh = res.data.user;
          localStorage.setItem('user', JSON.stringify(fresh));
          populateSidebar(fresh);
      }

      if (!res.ok || !fresh.profile) {
          sponsorAvailability = null;
          errEl.style.display = 'block';
          showToast('Could not load your visibility status.', 'error');
          loadEvents();
          return;
      }

      sponsorAvailability = availabilityFromDatabase(fresh.profile.is_available);
      if (sponsorAvailability === null) {
          errEl.style.display = 'block';
          showToast('Could not read visibility from the server.', 'error');
          loadEvents();
          return;
      }

      if (sponsorAvailability === false) {
          warnEl.style.display = 'block';
      }
      loadEvents();
  }

  async function loadEvents() {
    const [eventsRes, reqsRes] = await Promise.all([
      api.get('/events'),
      api.get('/sponsorship')
    ]);
    
    const myRequestEventIds = (reqsRes.ok && reqsRes.data) ? reqsRes.data.map(r => r.event?.id || r.event_id) : [];

    const tbody = document.getElementById('events-body');
    const upcomingEvents = eventsRes.data.filter(e => e.time_status === 'upcoming');
    if (!eventsRes.ok || !upcomingEvents.length) { 
        tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><p>No upcoming public events available right now.</p></div></td></tr>'; 
        return; 
    }
    
    tbody.innerHTML = upcomingEvents.map(e => {
      const hasRequested = myRequestEventIds.includes(e.id);
      
      let reqBtnHtml = '';
      if (hasRequested) {
          reqBtnHtml = `<span style="font-size:11px; color:var(--text-muted); margin-right:8px; font-style:italic;">Already Requested</span>
                        <button class="btn btn-ghost btn-sm" style="opacity:0.4; cursor:not-allowed;" disabled>Request</button>`;
      } else {
          reqBtnHtml = `<button class="btn btn-primary btn-sm req-btn" onclick="openModal(${e.id}, '${e.title.replace(/'/g, "\\'")}')" ${sponsorAvailability !== true ? 'disabled' : ''} title="${sponsorAvailability === true ? 'Request to sponsor' : (sponsorAvailability === false ? 'Turn on Open to Sponsorship on your dashboard or profile' : 'Loading visibility…')}">Request</button>`;
      }

      return `
      <tr>
        <td>
            <div style="font-weight:600">${e.title}</div>
        </td>
        <td>
            <div style="font-size:13px; color:var(--accent2); cursor:pointer; display:inline-block;" onclick="navigateToProfile(${e.creator?.id})">
                👤 ${e.creator?.name || 'Unknown'}
            </div>
        </td>
        <td style="color:var(--text-muted)">${e.venue?.name || 'TBA'}</td>
        <td style="color:var(--text-muted)">${fmtDateShort(e.start_time)} <div style="margin-top:4px;">${timeBadge(e.time_status)}</div></td>
        <td style="color:var(--text-muted)">${e.capacity ? e.capacity.toLocaleString() : 'N/A'}</td>
        <td style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
           <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${e.id})" title="View Details">ℹ️ Details</button>
           <div style="display:flex; align-items:center;">
             ${reqBtnHtml}
           </div>
        </td>
      </tr>`;
    }).join('');
  }

  function openModal(eventId, eventTitle) { 
      if (sponsorAvailability !== true) {
          showToast(sponsorAvailability === false
            ? 'Turn on "Open to Sponsorship" on your dashboard or profile to send requests.'
            : 'Visibility status is still loading or unavailable.', 'warning');
          return;
      }
      document.getElementById('r-event-id').value = eventId;
      document.getElementById('r-event-title').innerText = eventTitle;
      document.getElementById('req-modal').classList.add('open'); 
  }
  
  function closeModal() { 
      document.getElementById('req-modal').classList.remove('open'); 
      document.getElementById('req-form').reset(); 
  }

  document.getElementById('req-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('btn-submit');
    btn.textContent = 'Sending...';
    btn.disabled = true;

    const payload = { 
        event_id: +document.getElementById('r-event-id').value, 
        message: document.getElementById('r-message').value 
    };

    const res = await api.post('/sponsorship', payload);
    
    if (res.ok) { 
        showToast('Request sent directly to Event Manager!', 'success'); 
        closeModal(); 
    } else { 
        showToast(res.data?.message || 'Error sending request', 'error'); 
    }
    
    btn.textContent = 'Send Request';
    btn.disabled = false;
  });

  const typeIcons = { Conference: '🎤', Workshop: '🔧', Exhibition: '🖼️', Entertainment: '🎭', Seminar: '📚', Festival: '🎉', Other: '📌' };
  const typeColors = { Conference: '#3b82f6', Workshop: '#10b981', Exhibition: '#f59e0b', Entertainment: '#ec4899', Seminar: '#8b5cf6', Festival: '#f97316', Other: '#6b7280' };

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
            <div class="ed-section mt-4">
              <div class="ed-section-label">Current Sponsors</div>
              <div style="display:flex; flex-direction:column; gap:8px;">
                ${ev.sponsors.map(sp => `
                   <div style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.04); padding:10px; border-radius:10px; border:1px solid rgba(255,255,255,0.05); cursor:pointer;" onclick="navigateToProfile(${sp.id})">
                      <div class="avatar" style="width:32px; height:32px; font-size:12px; display:inline-flex; align-items:center; justify-content:center; background:#333; border-radius:50%; overflow:hidden;">
                          ${sp.profile?.logo ? `<img src="${sp.profile.logo.startsWith('http') ? sp.profile.logo : (sp.profile.logo.includes('storage') ? '/' + sp.profile.logo.replace(/^\//,'') : '/storage/' + sp.profile.logo)}" style="width:100%;height:100%;object-fit:cover;">` : (sp.name ? sp.name.charAt(0).toUpperCase() : '?')}
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
      } else {
          sponsorsHtml = `
            <div class="ed-section mt-4">
              <div class="ed-section-label">Current Sponsors</div>
              <p class="ed-description" style="font-size:0.85rem; font-style:italic;">Be the first to sponsor this event!</p>
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
              ${timeBadge(ev.time_status)}
            </div>
          </div>
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
            <div class="ed-info-card ed-info-warning">
              <div class="ed-info-icon">👥</div>
              <div><div class="ed-info-label">Capacity</div><div class="ed-info-value">${ev.capacity}</div></div>
            </div>
          </div>
          
          ${sponsorsHtml}
          
          <div class="ed-footer mt-2">
            <span class="ed-footer-label">Event Manager</span>
            <span class="ed-footer-name cursor-pointer" onclick="navigateToProfile(${ev.creator?.id})" style="color:var(--accent2);">${ev.creator?.name || '—'}</span>
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
.mt-4 { margin-top: 16px; }
.mt-2 { margin-top: 8px; }
.cursor-pointer { cursor: pointer; }
</style>
