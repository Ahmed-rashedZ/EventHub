<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Assistants – EventHub Manager</title>
  <link rel="stylesheet" href="/css/style.css"/>
  <script src="/js/i18n.js"></script>
  <style>
    .assistant-card { background: var(--surface2); padding: 20px; border-radius: 12px; border: 1px solid var(--border); max-width: 600px; }
    .status-badge { padding: 4px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
    .status-active { background: rgba(16, 185, 129, 0.1); color: var(--success); }
    .status-inactive { background: rgba(239, 68, 68, 0.1); color: var(--error); }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); backdrop-filter: blur(4px); }
    .modal-content { background: var(--surface2); margin: 5% auto; padding: 24px; border-radius: 16px; border: 1px solid var(--border); width: 80%; max-width: 900px; max-height: 85vh; overflow-y: auto; }
    .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .stat-box { background: var(--surface1); padding: 16px; border-radius: 12px; border: 1px solid var(--border); }
    .history-item { display: flex; align-items: center; gap: 12px; padding: 12px; border-bottom: 1px solid var(--border); }
    .history-item img { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; }
  </style>
<link rel="icon" href="/images/logo.jpg" type="image/jpeg">
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo" style="display:flex; justify-content:center; align-items:center; padding: 8px 0;"><img src="/images/logo.jpg" alt="EventHub Logo" style="width: 85px; height: 85px; object-fit: contain; border-radius: 50%; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"></div>
    <nav class="sidebar-nav">
      <span class="nav-section-label">Overview</span>
      <a class="nav-item" href="/manager/dashboard"><span class="nav-icon">📊</span> Dashboard</a>
      <span class="nav-section-label">Events</span>
      <a class="nav-item" href="/manager/events"><span class="nav-icon">📅</span> My Events</a>
      <a class="nav-item active" href="/manager/assistants"><span class="nav-icon">👥</span> Assistants</a>
      <a class="nav-item" href="/manager/attendance"><span class="nav-icon">📍</span> Attendance</a>
      <a class="nav-item" href="/manager/sponsorship"><span class="nav-icon">💼</span> Sponsorship</a>
      <span class="nav-section-label">Settings</span>
      <a class="nav-item" href="/profile"><span class="nav-icon">⚙️</span> My Profile</a>
    </nav> 
    @include('partials._sidebar-footer')
  </aside>

  <main class="main-content">
    <div class="topbar">
      <div><h1 class="page-title">Assistants Management</h1><p class="page-subtitle">Manage your event assistants and track their check-in performance</p></div>
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
      <div class="assistant-card" style="flex: 1;">
        <h3 id="form-title" style="margin-top:0; margin-bottom: 20px;">Generate Assistant Account</h3>
        <p id="form-desc" style="color:var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">
          Assistants use their accounts to log in on their mobile devices and scan QR codes for attendance tracking.
        </p>
        
        <form id="assistant-form">
          <input type="hidden" id="edit-id"/>
          <div class="form-group">
            <label class="form-label">Assistant Name</label>
            <input id="a-name" type="text" class="form-control" placeholder="e.g. Gate 1 Scanner" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Assistant Email (Login ID)</label>
            <input id="a-email" type="email" class="form-control" placeholder="gate1@myevent.com" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Password <span id="pass-hint" style="font-size:0.75rem; color:var(--text-muted)">(Leave blank to keep current)</span></label>
            <input id="a-pass" type="password" class="form-control" minlength="8"/>
          </div>
          <div class="form-group">
            <label class="form-label">Assign to Event</label>
            <select id="a-event" class="form-control" required>
              <option value="">Select an Event...</option>
            </select>
          </div>
          <div style="display:flex; justify-content: space-between; margin-top:20px;">
            <button type="button" class="btn btn-ghost" id="cancel-edit" style="display:none;" onclick="resetForm()">Cancel</button>
            <button type="submit" class="btn btn-primary" id="save-btn">Create Assistant</button>
          </div>
        </form>

        <div id="success-box" style="display:none; margin-top:30px; padding: 20px; background: rgba(16,185,129,0.1); border: 1px solid var(--success); border-radius: 8px;">
          <h4 style="color: var(--success); margin-top:0;">✅ Success!</h4>
          <p style="margin-bottom:0; font-size: 0.9rem; color: var(--text-secondary)">The assistant data has been updated. They can now log in and access the QR scanner.</p>
        </div>
      </div>

      <div class="assistant-card" style="flex: 1.5; max-width: none;">
        <h3 style="margin-top:0; margin-bottom: 20px;">Existing Assistants</h3>
        <div id="assistants-list">
          <p style="color:var(--text-muted); font-size: 0.9rem;">Loading assistants...</p>
        </div>
      </div>
    </div>
  </main>
</div>

<div id="toast-container"></div>
<script src="/js/api.js"></script>
<script src="/js/notifications.js"></script>
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); loadAssistants(); }

  let eventsMap = {};

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const select = document.getElementById('a-event');
    if (res.ok) {
      res.data.forEach(ev => {
        eventsMap[ev.id] = ev.title;
        const opt = document.createElement('option');
        opt.value = ev.id;
        opt.textContent = ev.title;
        select.appendChild(opt);
      });
    }
  }

  async function loadAssistants() {
    const res = await api.get('/assistants');
    const listDiv = document.getElementById('assistants-list');
    if (res.ok && res.data.length > 0) {
      listDiv.innerHTML = '<table style="width:100%; border-collapse: collapse;"><thead><tr><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Name</th><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Event</th><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Status</th><th style="text-align:right; padding:8px; border-bottom:1px solid var(--border);">Actions</th></tr></thead><tbody>' +
        res.data.map(a => `
          <tr>
            <td style="padding:8px; border-bottom:1px solid var(--border);">
              <div style="font-weight:600;">${a.name}</div>
              <div style="font-size:0.75rem; color:var(--text-muted)">${a.email}</div>
            </td>
            <td style="padding:8px; border-bottom:1px solid var(--border);">${a.event ? a.event.title : 'N/A'}</td>
            <td style="padding:8px; border-bottom:1px solid var(--border);">
              <span class="status-badge ${a.is_active ? 'status-active' : 'status-inactive'}">${a.is_active ? 'Active' : 'Suspended'}</span>
            </td>
            <td style="padding:8px; border-bottom:1px solid var(--border); text-align:right; display:flex; gap:8px; justify-content:flex-end;">
              <a href="/manager/assistants/${a.id}/stats" class="btn btn-ghost btn-sm">Stats</a>
              <button class="btn btn-ghost btn-sm" onclick="editAssistant(${JSON.stringify(a).replace(/"/g, '&quot;')})">Edit</button>
              <button class="btn btn-sm ${a.is_active ? 'btn-danger' : 'btn-success'}" onclick="toggleStatus(${a.id})">
                ${a.is_active ? 'Suspend' : 'Activate'}
              </button>
            </td>
          </tr>`).join('') +
        '</tbody></table>';
    } else {
      listDiv.innerHTML = '<p style="color:var(--text-muted); font-size: 0.9rem;">No assistants created yet.</p>';
    }
  }

  async function toggleStatus(id) {
    const res = await api.patch(`/assistants/${id}/status`);
    if (res.ok) {
      showToast('Status updated!', 'success');
      loadAssistants();
    }
  }

  function editAssistant(a) {
    document.getElementById('form-title').textContent = 'Edit Assistant';
    document.getElementById('form-desc').textContent = 'Modify assistant details or reassign them to another event.';
    document.getElementById('edit-id').value = a.id;
    document.getElementById('a-name').value = a.name;
    document.getElementById('a-email').value = a.email;
    document.getElementById('a-event').value = a.event_id;
    document.getElementById('a-pass').required = false;
    document.getElementById('pass-hint').style.display = 'inline';
    document.getElementById('save-btn').textContent = 'Update Assistant';
    document.getElementById('cancel-edit').style.display = 'inline';
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  function resetForm() {
    document.getElementById('form-title').textContent = 'Generate Assistant Account';
    document.getElementById('form-desc').textContent = 'Assistants use their accounts to log in on their mobile devices and scan QR codes.';
    document.getElementById('edit-id').value = '';
    document.getElementById('assistant-form').reset();
    document.getElementById('a-pass').required = true;
    document.getElementById('pass-hint').style.display = 'none';
    document.getElementById('save-btn').textContent = 'Create Assistant';
    document.getElementById('cancel-edit').style.display = 'none';
  }

  document.getElementById('assistant-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    const editId = document.getElementById('edit-id').value;
    btn.textContent = editId ? 'Updating...' : 'Creating...'; btn.disabled = true;

    const body = {
      name: document.getElementById('a-name').value,
      email: document.getElementById('a-email').value,
      event_id: document.getElementById('a-event').value,
    };
    if (document.getElementById('a-pass').value) {
      body.password = document.getElementById('a-pass').value;
    }

    let res;
    if (editId) {
      res = await api.put(`/assistants/${editId}`, body);
    } else {
      body.role = 'Assistant';
      res = await api.post('/users', body);
    }
    
    if (res.ok) {
      showToast(editId ? 'Assistant updated!' : 'Assistant created!', 'success');
      resetForm();
      document.getElementById('success-box').style.display = 'block';
      setTimeout(() => { document.getElementById('success-box').style.display = 'none'; }, 5000);
      loadAssistants();
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error saving assistant';
      showToast(msg, 'error');
    }
    btn.textContent = editId ? 'Update Assistant' : 'Create Assistant'; btn.disabled = false;
  });
</script>
</body>





