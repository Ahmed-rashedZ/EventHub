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
      <div><h1 class="page-title">My Events</h1><p class="page-subtitle">Create and manage your events</p></div>
      <div class="topbar-actions">
        <button class="btn btn-primary" onclick="openModal()">+ Create Event</button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Title</th><th>Venue</th><th>Date</th><th>Capacity</th><th>Sponsorships</th><th>Status</th></tr></thead>
          <tbody id="events-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
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
      <div class="form-group">
        <label class="form-label">Capacity</label>
        <input id="e-capacity" type="number" class="form-control" placeholder="200" min="1" required/>
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
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); loadVenues(); }

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const tbody = document.getElementById('events-body');
    if (!res.ok || !res.data.length) {
      tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">📅</div><p>No events yet</p></div></td></tr>';
      return;
    }
    tbody.innerHTML = res.data.map((ev, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${ev.title}</div><div style="font-size:.72rem;color:var(--text-muted)">${ev.description.substring(0,60)}…</div></td>
        <td style="color:var(--text-muted)">${ev.venue?.name || '—'}</td>
        <td style="white-space:nowrap;color:var(--text-muted)">${fmtDateShort(ev.start_time)}</td>
        <td style="color:var(--text-muted)">${ev.capacity}</td>
        <td>
           <div style="display:flex; align-items:center;">
             <input type="checkbox" id="spon-tog-${ev.id}" ${ev.is_sponsorship_open ? 'checked' : ''} onchange="toggleSponsorship(${ev.id}, this.checked)" style="width:16px; height:16px; margin-right:5px; cursor:pointer;"/>
             <label for="spon-tog-${ev.id}" style="font-size:12px; cursor:pointer;">Open</label>
           </div>
        </td>
        <td>${badge(ev.status)} <button class="btn btn-sm btn-info" onclick="showEventDetails(${ev.id})">Details</button></td>
      </tr>`).join('');
}

// Modal for event details (يجب أن تكون في النطاق العام)
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
    content.innerHTML = `
      <div class="event-icon">🎫</div>
      <div class="modal-body event-details">
        <h3>${ev.title}</h3>
        <div class="event-status">${ev.status ? badge(ev.status) : ''}</div>
        <div><b>Description:</b> <span>${ev.description || '-'}</span></div>
        <div class="event-details-row">
          <div><b>Venue:</b> <span>${ev.venue?.name || '-'}</span></div>
          <div><b>Location:</b> <span>${ev.venue?.location || '-'}</span></div>
        </div>
        <div class="event-details-row">
          <div><b>Start:</b> <span>${fmtDate(ev.start_time)}</span></div>
          <div><b>End:</b> <span>${fmtDate(ev.end_time)}</span></div>
        </div>
        <div class="event-details-row">
          <div><b>Capacity:</b> <span>${ev.capacity}</span></div>
          <div><b>Tickets:</b> <span>${ev.tickets_count ?? '—'}</span></div>
        </div>
        <div class="event-details-row">
          <div><b>Created by:</b> <span>${ev.manager?.name || '-'}</span></div>
        </div>
      </div>
    `;
  });
}

function closeEventDetailsModal() {
  document.getElementById('event-details-modal').classList.remove('open');
  document.getElementById('event-details-content').innerHTML = '';
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
  const res = await api.post('/events', {
    title:       document.getElementById('e-title').value,
    description: document.getElementById('e-desc').value,
    venue_id:    +document.getElementById('e-venue').value,
    start_time:  document.getElementById('e-start').value,
    end_time:    document.getElementById('e-end').value,
    capacity:    +document.getElementById('e-capacity').value,
  });
  if (res.ok) { showToast('Event submitted for approval!', 'success'); closeModal(); loadEvents(); }
  else {
    const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error';
    showToast(msg, 'error');
  }
});
</script>

<!-- Event Details Modal -->
<div class="modal-overlay" id="event-details-modal">
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <h3 class="modal-title">Event Details</h3>
      <button class="modal-close" onclick="closeEventDetailsModal()">✕</button>
    </div>
    <div class="modal-body" id="event-details-content" style="padding:16px;min-height:120px"></div>
  </div>
</div>
</body>
</html>
