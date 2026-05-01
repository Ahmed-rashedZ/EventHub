<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Venues – EventHub Admin</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/dark.css">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
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
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item active" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <a class="nav-item" href="/admin/verifications"><span class="nav-icon">🛡️</span> Verifications</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Venues</h1><p class="page-subtitle">Manage event locations</p></div>
      <div class="topbar-actions">
        <button class="btn btn-primary" onclick="openModal()">+ Add Venue</button>
      </div>
    </div>

    <div class="card">
      <div class="table-wrap">
        <table>
          <thead><tr><th>#</th><th>Name</th><th>Location</th><th>Capacity</th><th>Status</th><th>Maintenance</th><th>Actions</th></tr></thead>
          <tbody id="venues-body">
            <tr class="loading-row"><td colspan="7"><div class="spinner" style="margin:auto"></div></td></tr>
          </tbody>
        </table>
      </div>
    </div>
  </main>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="venue-modal">
  <div class="modal">
    <div class="modal-header">
      <h3 class="modal-title" id="modal-title">Add Venue</h3>
      <button class="modal-close" onclick="closeModal()">✕</button>
    </div>
    <form id="venue-form">
      <input type="hidden" id="venue-id"/>
      <div class="form-group">
        <label class="form-label">Venue Name</label>
        <input id="v-name" type="text" class="form-control" placeholder="City Hall" required/>
      </div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Google Maps Link</label>
          <input id="v-location" type="url" class="form-control" placeholder="https://maps.google.com/..." required/>
          <small style="color:var(--text-muted);font-size:12px;margin-top:4px;display:block">Open Google Maps → Select location → Copy share link and paste here</small>
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input id="v-capacity" type="number" class="form-control" placeholder="500" min="1" required/>
        </div>
      </div>

      <!-- Period Settings -->
      <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:16px;margin-bottom:16px">
        <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px">⏰ Period Settings</div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Morning Start</label>
            <input id="v-morning-start" type="time" class="form-control" value="09:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Morning End</label>
            <input id="v-morning-end" type="time" class="form-control" value="13:00" required/>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group">
            <label class="form-label">Evening Start</label>
            <input id="v-evening-start" type="time" class="form-control" value="15:00" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Evening End</label>
            <input id="v-evening-end" type="time" class="form-control" value="19:00" required/>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Status</label>
        <select id="v-status" class="form-control">
          <option value="available">Available</option>
          <option value="maintenance">Maintenance (Full Lockdown)</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="save-btn">Save Venue</button>
      </div>
    </form>
  </div>
</div>

<!-- Maintenance Schedule Modal -->
<div class="modal-overlay" id="maintenance-modal">
  <div class="modal" style="max-width:560px; max-height:85vh; overflow-y:auto;">
    <div class="modal-header">
      <h3 class="modal-title">🔧 Schedule Maintenance — <span id="maint-venue-name"></span></h3>
      <button class="modal-close" onclick="closeMaintenanceModal()">✕</button>
    </div>
    
    <!-- Add New Period -->
    <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:12px;padding:20px;margin-bottom:20px">
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:16px">📅 Add Maintenance Period</div>
      <div class="form-grid">
        <div class="form-group">
          <label class="form-label">Start Date</label>
          <input id="m-start" type="date" class="form-control" required/>
        </div>
        <div class="form-group">
          <label class="form-label">End Date</label>
          <input id="m-end" type="date" class="form-control" required/>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Reason (Optional)</label>
        <input id="m-reason" type="text" class="form-control" placeholder="e.g. Annual renovation, Equipment upgrade..."/>
      </div>
      <button type="button" class="btn btn-primary" onclick="addMaintenancePeriod()" style="width:100%;margin-top:8px">
        + Add Maintenance Period
      </button>
    </div>

    <!-- Existing Periods List -->
    <div>
      <div style="font-size:0.75rem;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:rgba(255,255,255,0.35);margin-bottom:12px">📋 Scheduled Maintenance Periods</div>
      <div id="maintenance-list">
        <div style="text-align:center;color:var(--text-muted);padding:20px">Loading...</div>
      </div>
    </div>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  let editingId = null;
  let currentMaintenanceVenueId = null;
  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadVenues(); }

  // Set minimum date for maintenance inputs
  const today = new Date().toISOString().split('T')[0];
  document.getElementById('m-start').min = today;
  document.getElementById('m-end').min = today;
  document.getElementById('m-start').addEventListener('change', function() {
    document.getElementById('m-end').min = this.value;
    if (document.getElementById('m-end').value < this.value) {
      document.getElementById('m-end').value = this.value;
    }
  });

  async function loadVenues() {
    const res = await api.get('/venues');
    const tbody = document.getElementById('venues-body');
    if (!res.ok || !res.data.length) { tbody.innerHTML = '<tr><td colspan="7"><div class="empty-state"><div class="empty-icon">🏛️</div><p>No venues yet. Add one!</p></div></td></tr>'; return; }
    tbody.innerHTML = res.data.map((v, i) => {
      // Count upcoming maintenance periods
      const now = new Date().toISOString().split('T')[0];
      const upcomingMaint = (v.maintenance_periods || []).filter(p => p.end_date >= now);
      const activeMaint = (v.maintenance_periods || []).filter(p => p.start_date <= now && p.end_date >= now);
      
      let statusHtml;
      if (v.status === 'maintenance') {
        statusHtml = '<span class="badge" style="background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)">🔒 Full Lockdown</span>';
      } else if (activeMaint.length > 0) {
        statusHtml = '<span class="badge" style="background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)">🔧 Under Maintenance</span>';
      } else {
        statusHtml = '<span class="badge" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)">✅ Available</span>';
      }

      const maintBadge = upcomingMaint.length > 0
        ? `<span class="badge" style="background:rgba(245,158,11,0.12);color:#f59e0b;border:1px solid rgba(245,158,11,0.25);font-size:11px">${upcomingMaint.length} scheduled</span>`
        : '<span style="color:var(--text-muted);font-size:12px">None</span>';

      return `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${v.name}</div></td>
        <td>${v.location && v.location.startsWith('http') ? `<a href="${v.location}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px">📍 Open in Maps <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>` : `<span style="color:var(--text-muted)">📍 ${v.location || '—'}</span>`}</td>
        <td style="color:var(--text-muted)">${v.capacity.toLocaleString()}</td>
        <td>${statusHtml}</td>
        <td>${maintBadge}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick='editVenue(${JSON.stringify(v)})'>✏️ Edit</button>
          <button class="btn btn-sm" style="background:rgba(245,158,11,0.1);color:#f59e0b;border:1px solid rgba(245,158,11,0.25)" onclick='openMaintenanceModal(${v.id}, ${JSON.stringify(v.name)})'>🔧 Maintenance</button>
        </td>
      </tr>`;
    }).join('');
  }

  function openModal(v = null) {
    editingId = v ? v.id : null;
    document.getElementById('modal-title').textContent = v ? 'Edit Venue' : 'Add Venue';
    document.getElementById('v-name').value     = v ? v.name : '';
    document.getElementById('v-location').value = v ? (v.location || '') : '';
    document.getElementById('v-capacity').value = v ? v.capacity : '';
    document.getElementById('v-status').value   = v ? v.status : 'available';
    document.getElementById('v-morning-start').value = v ? v.morning_start : '09:00';
    document.getElementById('v-morning-end').value   = v ? v.morning_end : '13:00';
    document.getElementById('v-evening-start').value = v ? v.evening_start : '15:00';
    document.getElementById('v-evening-end').value   = v ? v.evening_end : '19:00';
    document.getElementById('venue-modal').classList.add('open');
  }

  function editVenue(v) { openModal(v); }

  function closeModal() { document.getElementById('venue-modal').classList.remove('open'); editingId = null; }

  document.getElementById('venue-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = { 
      name: document.getElementById('v-name').value, 
      location: document.getElementById('v-location').value, 
      capacity: +document.getElementById('v-capacity').value, 
      status: document.getElementById('v-status').value,
      morning_start: document.getElementById('v-morning-start').value,
      morning_end: document.getElementById('v-morning-end').value,
      evening_start: document.getElementById('v-evening-start').value,
      evening_end: document.getElementById('v-evening-end').value
    };
    const res = editingId ? await api.put(`/venues/${editingId}`, body) : await api.post('/venues', body);
    if (res.ok) { showToast(editingId ? 'Venue updated!' : 'Venue added!', 'success'); closeModal(); loadVenues(); }
    else showToast(res.data?.message || 'Error', 'error');
  });

  // ── Maintenance Modal ─────────────────────────────────────────────────

  function openMaintenanceModal(venueId, venueName) {
    currentMaintenanceVenueId = venueId;
    document.getElementById('maint-venue-name').textContent = venueName;
    document.getElementById('m-start').value = '';
    document.getElementById('m-end').value = '';
    document.getElementById('m-reason').value = '';
    document.getElementById('maintenance-modal').classList.add('open');
    loadMaintenancePeriods();
  }

  function closeMaintenanceModal() {
    document.getElementById('maintenance-modal').classList.remove('open');
    currentMaintenanceVenueId = null;
  }

  async function loadMaintenancePeriods() {
    const container = document.getElementById('maintenance-list');
    container.innerHTML = '<div style="text-align:center;padding:16px"><div class="spinner" style="margin:auto"></div></div>';
    
    const res = await api.get(`/venues/${currentMaintenanceVenueId}/maintenance`);
    if (!res.ok || !res.data.length) {
      container.innerHTML = '<div style="text-align:center;color:var(--text-muted);padding:24px;background:rgba(255,255,255,0.02);border:1px dashed rgba(255,255,255,0.08);border-radius:10px"><div style="font-size:2rem;margin-bottom:8px">📋</div><p style="margin:0">No maintenance periods scheduled</p></div>';
      return;
    }

    const now = new Date().toISOString().split('T')[0];
    container.innerHTML = res.data.map(p => {
      const isActive = p.start_date <= now && p.end_date >= now;
      const isPast = p.end_date < now;
      const isFuture = p.start_date > now;
      
      let statusBadge;
      if (isActive) statusBadge = '<span style="background:rgba(245,158,11,0.15);color:#f59e0b;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">🔧 ACTIVE NOW</span>';
      else if (isPast) statusBadge = '<span style="background:rgba(107,114,128,0.15);color:#6b7280;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">✓ COMPLETED</span>';
      else statusBadge = '<span style="background:rgba(59,130,246,0.15);color:#3b82f6;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700">⏳ UPCOMING</span>';

      // Calculate duration
      const start = new Date(p.start_date);
      const end = new Date(p.end_date);
      const days = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

      return `
        <div style="display:flex;align-items:center;gap:12px;padding:14px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;margin-bottom:8px;${isActive ? 'border-left:3px solid #f59e0b;' : ''}${isPast ? 'opacity:0.5;' : ''}">
          <div style="flex:1">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px">
              ${statusBadge}
              <span style="color:var(--text-muted);font-size:11px">${days} day${days > 1 ? 's' : ''}</span>
            </div>
            <div style="font-weight:600;font-size:0.9rem;color:#fff;margin-bottom:4px">
              📅 ${p.start_date} → ${p.end_date}
            </div>
            ${p.reason ? `<div style="color:var(--text-muted);font-size:0.8rem">💬 ${p.reason}</div>` : ''}
          </div>
          ${!isPast ? `<button class="btn btn-sm" style="background:rgba(239,68,68,0.1);color:#ef4444;border:1px solid rgba(239,68,68,0.25)" onclick="deleteMaintenancePeriod(${p.id})">🗑️</button>` : ''}
        </div>`;
    }).join('');
  }

  async function addMaintenancePeriod() {
    const startDate = document.getElementById('m-start').value;
    const endDate = document.getElementById('m-end').value;
    const reason = document.getElementById('m-reason').value;

    if (!startDate || !endDate) {
      showToast('Please select both start and end dates', 'error');
      return;
    }

    const res = await api.post(`/venues/${currentMaintenanceVenueId}/maintenance`, {
      start_date: startDate,
      end_date: endDate,
      reason: reason || null,
    });

    if (res.ok) {
      showToast('Maintenance period scheduled! Affected managers have been notified.', 'success');
      document.getElementById('m-start').value = '';
      document.getElementById('m-end').value = '';
      document.getElementById('m-reason').value = '';
      loadMaintenancePeriods();
      loadVenues(); // Refresh table badges
    } else {
      showToast(res.data?.message || 'Error scheduling maintenance', 'error');
    }
  }

  async function deleteMaintenancePeriod(periodId) {
    if (!confirm('Are you sure you want to remove this maintenance period?')) return;
    
    const res = await api.delete(`/venues/${currentMaintenanceVenueId}/maintenance/${periodId}`);
    if (res.ok) {
      showToast('Maintenance period removed', 'success');
      loadMaintenancePeriods();
      loadVenues();
    } else {
      showToast(res.data?.message || 'Error', 'error');
    }
  }

  // Venue deletion is disabled to preserve archive history
</script>
</body>
</html>
