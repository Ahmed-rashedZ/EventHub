<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Venues – EventHub Admin</title>
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
      <a class="nav-item" href="/admin/events"><span class="nav-icon">📅</span> Events</a>
      <a class="nav-item active" href="/admin/venues"><span class="nav-icon">🏛️</span> Venues</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav>
    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="avatar" id="sidebar-avatar">A</div>
        <div class="user-info"><div class="user-name" id="sidebar-username">Admin</div><div class="user-role" id="sidebar-role">Administrator</div></div>
      </div>
      <button class="btn btn-logout" id="logout-btn">🚪 Sign Out</button>
    </div>
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
          <label class="form-label">Location</label>
          <input id="v-location" type="text" class="form-control" placeholder="Riyadh, KSA" required/>
        </div>
        <div class="form-group">
          <label class="form-label">Capacity</label>
          <input id="v-capacity" type="number" class="form-control" placeholder="500" min="1" required/>
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
        <td style="color:var(--text-muted)">📍 ${v.location}</td>
        <td style="color:var(--text-muted)">${v.capacity.toLocaleString()}</td>
        <td>${badge(v.status)}</td>
        <td style="display:flex;gap:6px;padding:14px 16px">
          <button class="btn btn-ghost btn-sm" onclick='editVenue(${JSON.stringify(v)})'>✏️ Edit</button>
          <button class="btn btn-danger btn-sm" onclick="deleteVenue(${v.id})">🗑</button>
        </td>
      </tr>`).join('');
  }

  function openModal(v = null) {
    editingId = v ? v.id : null;
    document.getElementById('modal-title').textContent = v ? 'Edit Venue' : 'Add Venue';
    document.getElementById('v-name').value     = v ? v.name : '';
    document.getElementById('v-location').value = v ? v.location : '';
    document.getElementById('v-capacity').value = v ? v.capacity : '';
    document.getElementById('v-status').value   = v ? v.status : 'available';
    document.getElementById('venue-modal').classList.add('open');
  }

  function editVenue(v) { openModal(v); }

  function closeModal() { document.getElementById('venue-modal').classList.remove('open'); editingId = null; }

  document.getElementById('venue-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const body = { name: document.getElementById('v-name').value, location: document.getElementById('v-location').value, capacity: +document.getElementById('v-capacity').value, status: document.getElementById('v-status').value };
    const res = editingId ? await api.put(`/venues/${editingId}`, body) : await api.post('/venues', body);
    if (res.ok) { showToast(editingId ? 'Venue updated!' : 'Venue added!', 'success'); closeModal(); loadVenues(); }
    else showToast(res.data?.message || 'Error', 'error');
  });

  async function deleteVenue(id) {
    if (!confirm('Delete this venue?')) return;
    const res = await api.delete(`/venues/${id}`);
    if (res.ok) { showToast('Venue deleted', 'success'); loadVenues(); }
    else showToast(res.data?.message || 'Error', 'error');
  }
</script>
</body>
</html>
