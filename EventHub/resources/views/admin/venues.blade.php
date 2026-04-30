<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Venues – EventHub Admin</title>
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
          <thead><tr><th>#</th><th>Name</th><th>Location</th><th>Capacity</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody id="venues-body">
            <tr class="loading-row"><td colspan="6"><div class="spinner" style="margin:auto"></div></td></tr>
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
          <option value="maintenance">Maintenance</option>
        </select>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-ghost" onclick="closeModal()">Cancel</button>
        <button type="submit" class="btn btn-primary" id="save-btn">Save Venue</button>
      </div>
    </form>
  </div>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  let editingId = null;
  const user = requireRole('Admin');
  if (user) { populateSidebar(user); setActiveNav(); loadVenues(); }

  async function loadVenues() {
    const res = await api.get('/venues');
    const tbody = document.getElementById('venues-body');
    if (!res.ok || !res.data.length) { tbody.innerHTML = '<tr><td colspan="6"><div class="empty-state"><div class="empty-icon">🏛️</div><p>No venues yet. Add one!</p></div></td></tr>'; return; }
    tbody.innerHTML = res.data.map((v, i) => `
      <tr>
        <td style="color:var(--text-muted)">${i+1}</td>
        <td><div style="font-weight:600">${v.name}</div></td>
        <td>${v.location && v.location.startsWith('http') ? `<a href="${v.location}" target="_blank" rel="noopener" style="color:var(--primary);text-decoration:none;display:inline-flex;align-items:center;gap:4px">📍 Open in Maps <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg></a>` : `<span style="color:var(--text-muted)">📍 ${v.location || '—'}</span>`}</td>
        <td style="color:var(--text-muted)">${v.capacity.toLocaleString()}</td>
        <td>${badge(v.status)}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick='editVenue(${JSON.stringify(v)})'>✏️ Edit</button>
        </td>
      </tr>`).join('');
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

  // Venue deletion is disabled to preserve archive history
</script>
</body>
</html>
