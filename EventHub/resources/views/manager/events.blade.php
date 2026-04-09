<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>My Events – EventHub Manager</title>
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
      <a class="nav-item active" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">My Events</h1><p class="page-subtitle">Create and manage your events</p></div>
      <div class="topbar-actions" style="display:flex;gap:10px;align-items:center">
        <div style="position:relative">
          <input id="search-input" type="text" class="form-control" placeholder="Search by event name..." style="width:260px;padding-left:36px" oninput="applyFilter()">
          <svg style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-muted)" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
        </div>
        <select id="filter-status" class="form-control" style="width:160px" onchange="applyFilter()">
          <option value="">All Events</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
        <button class="btn btn-primary" onclick="openModal()">+ Create Event</button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Title</th><th>Venue</th><th>Start</th><th>Capacity</th><th>Sponsorships</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="events-body">
            <tr class="loading-row"><td colspan="8"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Create Event Modal -->
<div class="modal-overlay" id="event-modal">
  <div class="modal" style="max-width:600px">
    <div class="modal-header">
      <h3 class="modal-title">Create New Event</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="event-form">
      <div class="form-group">
        <label class="form-label">Event Title</label>
        <input id="e-title" type="text" class="form-control" placeholder="Tech Summit 2026" required/>
      </div>
      <div class="form-group">
        <label class="form-label">Description</label>
        <textarea id="e-desc" class="form-control" placeholder="Describe your event…" required></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">Event Type</label>
        <select id="e-type" class="form-control" required>
          <option value="Conference">Conference</option>
          <option value="Workshop">Workshop</option>
          <option value="Exhibition">Exhibition</option>
          <option value="Entertainment">Entertainment</option>
          <option value="Seminar">Seminar</option>
          <option value="Festival">Festival</option>
          <option value="Other">Other</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Venue</label>
        <select id="e-venue" class="form-control" required>
          <option value="">Loading venues…</option>
        </select>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Start Date & Time</label>
          <input id="e-start" type="datetime-local" class="form-control" required/>
        </div>
        <div class="form-group">
          <label class="form-label">End Date & Time</label>
          <input id="e-end" type="datetime-local" class="form-control" required/>
        </div>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input id="e-capacity" type="number" class="form-control" placeholder="200" min="1" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Event Banner Image</label>
          <input id="e-image" type="file" accept="image/*" class="form-control"/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary">Submit for Approval</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/auth.js"></script>
<script>
  let allEvents = [];
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); loadVenues(); }

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const tbody = document.getElementById('events-body');
    if (!res.ok) { tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;color:var(--danger)">Failed to load</td></tr>'; return; }
    allEvents = res.data;
    applyFilter();
  }

  function applyFilter() {
    const f = document.getElementById('filter-status').value;
    const q = (document.getElementById('search-input').value || '').toLowerCase().trim();
    let filtered = f ? allEvents.filter(e => e.status === f) : [...allEvents];
    if (q) {
      filtered = filtered.filter(e => {
        const title = (e.title || '').toLowerCase();
        return title.includes(q);
      });
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
        <td style="color:var(--text-muted);white-space:nowrap">${fmtDateShort(ev.start_time)}</td>
        <td style="color:var(--text-muted)">${ev.capacity}</td>
        <td>
           <div style="display:flex; align-items:center;">
             <input type="checkbox" id="spon-tog-${ev.id}" ${ev.is_sponsorship_open ? 'checked' : ''} onchange="toggleSponsorship(${ev.id}, this.checked)" style="width:16px; height:16px; margin-right:5px; cursor:pointer;"/>
             <label for="spon-tog-${ev.id}" style="font-size:12px; cursor:pointer;">Open</label>
           </div>
        </td>
        <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</td>
        <td style="display:flex;gap:6px;padding:14px 16px;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${ev.id})" title="View Details">ℹ️ Details</button>
          <button class="btn btn-sm" style="background:rgba(34,211,238,.12);color:#22d3ee;border:1px solid rgba(34,211,238,.25)" onclick="showEventStats(${ev.id})" title="View Statistics">📊 Stats</button>
        </td>
      </tr>`).join('');
  }

// Modal for event details
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

    const rejectionSection = (ev.status === 'rejected' && ev.rejection_reason)
      ? `<div class="ed-rejection"><span class="ed-rej-label">⚠ Rejection Reason</span><p>${ev.rejection_reason}</p></div>`
      : '';

    content.innerHTML = `
      ${bannerSection}
      <div class="ed-body">

        <!-- Header: Title + Type + Badges -->
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

        <!-- Description -->
        <div class="ed-section">
          <div class="ed-section-label">About this Event</div>
          <p class="ed-description">${ev.description || 'No description provided.'}</p>
        </div>

        <!-- Info Grid -->
        <div class="ed-info-grid">
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">🏛️</div>
            <div>
              <div class="ed-info-label">Venue</div>
              <div class="ed-info-value">${ev.venue?.name || '—'}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-accent2">
            <div class="ed-info-icon">📍</div>
            <div>
              <div class="ed-info-label">Location</div>
              <div class="ed-info-value">${ev.venue?.location || '—'}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-accent">
            <div class="ed-info-icon">🕐</div>
            <div>
              <div class="ed-info-label">Start</div>
              <div class="ed-info-value">${fmtDate(ev.start_time)}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-accent">
            <div class="ed-info-icon">🕔</div>
            <div>
              <div class="ed-info-label">End</div>
              <div class="ed-info-value">${fmtDate(ev.end_time)}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">👥</div>
            <div>
              <div class="ed-info-label">Capacity</div>
              <div class="ed-info-value">${ev.capacity}</div>
            </div>
          </div>
          <div class="ed-info-card ed-info-warning">
            <div class="ed-info-icon">🎟️</div>
            <div>
              <div class="ed-info-label">Tickets Booked</div>
              <div class="ed-info-value">${ev.tickets_count ?? '—'}</div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="ed-footer">
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

// ── Event Statistics Modal ──
function showEventStats(eventId) {
  const modal = document.getElementById('event-stats-modal');
  const content = document.getElementById('event-stats-content');
  modal.classList.add('open');
  content.innerHTML = '<div class="spinner" style="margin:auto"></div>';
  Promise.all([
    api.get(`/analytics/event/${eventId}`),
    api.get(`/events/${eventId}`)
  ]).then(([statsRes, eventRes]) => {
    if (!statsRes.ok) {
      content.innerHTML = '<div class="empty-state"><div class="empty-icon">❌</div><p>Could not fetch statistics</p></div>';
      return;
    }
    const s = statsRes.data;
    const ev = eventRes.ok ? eventRes.data : s.event;
    const fillRate = ev.capacity > 0 ? Math.round((s.registered_count / ev.capacity) * 100 * 10) / 10 : 0;
    const frColor = fillRate > 80 ? '#22c55e' : fillRate > 50 ? '#f59e0b' : '#ef4444';
    const arColor = s.attendance_rate > 80 ? '#22c55e' : s.attendance_rate > 50 ? '#f59e0b' : '#3b82f6';

    content.innerHTML = `
      <div class="es-header">
        <h3 class="es-title">📊 ${ev.title || 'Event'} — Statistics</h3>
      </div>
      <div class="es-body">
        <div class="es-metrics">
          <div class="es-metric">
            <div class="es-metric-icon" style="background:rgba(110,64,242,.15)">🎟️</div>
            <div class="es-metric-label">Registered</div>
            <div class="es-metric-value">${s.registered_count}</div>
            <div class="es-metric-sub">of ${ev.capacity} capacity</div>
          </div>
          <div class="es-metric">
            <div class="es-metric-icon" style="background:rgba(34,197,94,.15)">✅</div>
            <div class="es-metric-label">Attended</div>
            <div class="es-metric-value">${s.attended_count}</div>
            <div class="es-metric-sub">checked in</div>
          </div>
        </div>

        <div class="es-bars">
          <div class="es-bar-group">
            <div class="es-bar-header">
              <span>Fill Rate</span>
              <span style="font-weight:700;color:#fff">${s.registered_count}/${ev.capacity} (${fillRate}%)</span>
            </div>
            <div class="es-bar-track"><div class="es-bar-fill" style="width:${fillRate}%;background:${frColor}"></div></div>
          </div>
          <div class="es-bar-group">
            <div class="es-bar-header">
              <span>Attendance Rate</span>
              <span style="font-weight:700;color:#fff">${s.attended_count}/${s.registered_count} (${s.attendance_rate}%)</span>
            </div>
            <div class="es-bar-track"><div class="es-bar-fill" style="width:${s.attendance_rate}%;background:${arColor}"></div></div>
          </div>
        </div>

        <div class="es-info-row">
          <div class="es-info-item"><span class="es-info-label">Event Status</span><span>${badge(ev.status)}</span></div>
          <div class="es-info-item"><span class="es-info-label">Venue</span><span style="color:#fff;font-weight:600">${ev.venue?.name || '—'}</span></div>
        </div>
      </div>
    `;
  });
}

function closeEventStatsModal() {
  document.getElementById('event-stats-modal').classList.remove('open');
  document.getElementById('event-stats-content').innerHTML = '';
}

async function toggleSponsorship(eventId, checked) {
    const res = await api.patch(`/events/${eventId}/toggle-sponsorship`);
    if (res.ok) {
        showToast(res.data.is_sponsorship_open ? 'Sponsorship is now OPEN for this event.' : 'Sponsorship is now CLOSED for this event.', 'success');
    } else {
        showToast(res.data?.message || 'Error updating status', 'error');
        document.getElementById(`spon-tog-${eventId}`).checked = !checked; // revert UI visually
    }
}

async function loadVenues() {
  const res = await api.get('/venues');
  const sel = document.getElementById('e-venue');
  if (!res.ok) { sel.innerHTML = '<option value="">No venues available</option>'; return; }
  sel.innerHTML = res.data.map(v => `<option value="${v.id}">${v.name} (${v.location})</option>`).join('');
}

function openModal() { document.getElementById('event-modal').classList.add('open'); }
function closeModal() { document.getElementById('event-modal').classList.remove('open'); document.getElementById('event-form').reset(); }

document.getElementById('event-form').addEventListener('submit', async (e) => {
  e.preventDefault();
  const formData = new FormData();
  formData.append('title', document.getElementById('e-title').value);
  formData.append('description', document.getElementById('e-desc').value);
  formData.append('event_type', document.getElementById('e-type').value);
  formData.append('venue_id', document.getElementById('e-venue').value);
  formData.append('start_time', document.getElementById('e-start').value);
  formData.append('end_time', document.getElementById('e-end').value);
  formData.append('capacity', document.getElementById('e-capacity').value);
  
  const imageFile = document.getElementById('e-image').files[0];
  if (imageFile) {
      formData.append('image', imageFile);
  }

  const res = await api.postForm('/events', formData);
  
  if (res.ok) { showToast('Event submitted for approval!', 'success'); closeModal(); loadEvents(); }
  else {
    const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
    showToast(msg, 'error');
  }
});
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
  padding: 0;
  overflow: hidden;
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 32px 80px rgba(0,0,0,0.6);
  background: #13131f;
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
.ed-content { position: relative; }
.ed-banner {
  width: 100%; height: 200px;
  background-size: cover; background-position: center;
  position: relative;
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
.ed-body { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 20px; }
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

<!-- Event Stats Modal -->
<div class="modal-overlay" id="event-stats-modal">
  <div class="modal es-modal">
    <button class="ed-close-btn" onclick="closeEventStatsModal()">✕</button>
    <div id="event-stats-content" class="es-content"></div>
  </div>
</div>

<style>
.es-modal {
  max-width: 520px;
  padding: 0;
  overflow: hidden;
  border-radius: 20px;
  border: 1px solid rgba(255,255,255,0.08);
  box-shadow: 0 32px 80px rgba(0,0,0,0.6);
  background: #13131f;
}
.es-content { position: relative; }
.es-header {
  padding: 24px 28px 0;
}
.es-title {
  margin: 0;
  font-size: 1.2rem;
  font-weight: 800;
  color: #fff;
}
.es-body {
  padding: 20px 28px 28px;
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.es-metrics {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}
.es-metric {
  background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.07);
  border-radius: 14px;
  padding: 18px;
  text-align: center;
  transition: background 0.2s;
}
.es-metric:hover { background: rgba(255,255,255,0.07); }
.es-metric-icon {
  width: 44px; height: 44px;
  border-radius: 12px;
  display: grid; place-items: center;
  font-size: 1.3rem;
  margin: 0 auto 10px;
}
.es-metric-label {
  font-size: 0.7rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--text-muted);
  margin-bottom: 4px;
}
.es-metric-value {
  font-size: 2rem;
  font-weight: 800;
  color: #fff;
  line-height: 1;
  margin-bottom: 4px;
}
.es-metric-sub {
  font-size: 0.72rem;
  color: var(--text-muted);
}
.es-bars {
  display: flex;
  flex-direction: column;
  gap: 14px;
}
.es-bar-group {}
.es-bar-header {
  display: flex;
  justify-content: space-between;
  font-size: 0.78rem;
  color: var(--text-muted);
  margin-bottom: 6px;
}
.es-bar-track {
  background: rgba(255,255,255,0.06);
  border-radius: 6px;
  height: 10px;
  overflow: hidden;
}
.es-bar-fill {
  height: 100%;
  border-radius: 6px;
  transition: width 0.8s ease;
}
.es-info-row {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding-top: 10px;
  border-top: 1px solid rgba(255,255,255,0.06);
}
.es-info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
}
.es-info-label {
  font-size: 0.78rem;
  color: var(--text-muted);
}
</style>
</body>
</html>
