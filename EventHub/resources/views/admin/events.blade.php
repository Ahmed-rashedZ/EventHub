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
        <td>${badge(ev.status)} ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick="showEventDetails(${ev.id})" title="View Details">ℹ️ Details</button>
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
          <div class="event-status">
            ${ev.status ? badge(ev.status) : ''} 
            ${ev.status === 'approved' ? timeBadge(ev.time_status) : ''}
          </div>
          ${ev.status === 'rejected' && ev.rejection_reason ? `
            <div style="background:rgba(239, 68, 68, 0.1); border-left:4px solid var(--danger); padding:12px; margin:10px 0; border-radius:4px;">
              <div style="color:var(--danger); font-weight:700; font-size:0.8rem; margin-bottom:4px; text-transform:uppercase;">Rejection Reason</div>
              <div style="color:var(--text-primary); font-size:0.95rem;">${ev.rejection_reason}</div>
            </div>
          ` : ''}
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
            <div><b>Created by:</b> <span>${ev.creator?.name || '-'}</span></div>
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
  <div class="modal" style="max-width:500px">
    <div class="modal-header">
      <h3 class="modal-title">Event Details</h3>
      <button class="modal-close" onclick="closeEventDetailsModal()">✕</button>
    </div>
    <div class="modal-body" id="event-details-content" style="padding:16px;min-height:120px"></div>
  </div>
</div>

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
