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
  </style>
</head>
<body>
<div class="app-layout">
  <aside class="sidebar">
    <div class="sidebar-logo"><div class="logo-icon">🎯</div><span>EventHub</span></div>
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
      <div><h1 class="page-title">Assistants</h1><p class="page-subtitle">Create accounts for assistants to scan QR codes at your events</p></div>
    </div>

    <div style="display: flex; gap: 20px; align-items: flex-start;">
      <div class="assistant-card" style="flex: 1;">
        <h3 style="margin-top:0; margin-bottom: 20px;">Generate Assistant Account</h3>
        <p style="color:var(--text-muted); font-size: 0.9rem; margin-bottom: 24px;">
          Assistants use their accounts to log in on their mobile devices and scan QR codes for attendance tracking at the venue gates.
        </p>
        
        <form id="assistant-form">
          <div class="form-group">
            <label class="form-label">Assistant Name</label>
            <input id="a-name" type="text" class="form-control" placeholder="e.g. Gate 1 Scanner" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Assistant Email (Login ID)</label>
            <input id="a-email" type="email" class="form-control" placeholder="gate1@myevent.com" required/>
          </div>
          <div class="form-group">
            <label class="form-label">Password</label>
            <input id="a-pass" type="password" class="form-control" required minlength="8"/>
          </div>
          <div class="form-group">
            <label class="form-label">Assign to Event</label>
            <select id="a-event" class="form-control" required>
              <option value="">Select an Event...</option>
            </select>
          </div>
          <div style="text-align: right; margin-top:20px;">
            <button type="submit" class="btn btn-primary" id="save-btn">Create Assistant</button>
          </div>
        </form>

        <div id="success-box" style="display:none; margin-top:30px; padding: 20px; background: rgba(16,185,129,0.1); border: 1px solid var(--success); border-radius: 8px;">
          <h4 style="color: var(--success); margin-top:0;">✅ Assistant Created Successfully!</h4>
          <p style="margin-bottom:0; font-size: 0.9rem; color: var(--text-secondary)">Please share the email and password with your assistant. They can now log in and access the QR scanner.</p>
        </div>
      </div>

      <div class="assistant-card" style="flex: 1;">
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
<script src="/js/auth.js"></script>
<script>
  const user = requireRole('Event Manager');
  if (user) { populateSidebar(user); setActiveNav(); loadEvents(); loadAssistants(); }

  async function loadEvents() {
    const res = await api.get('/events/list/my');
    const select = document.getElementById('a-event');
    if (res.ok) {
      res.data.forEach(ev => {
        const opt = document.createElement('option');
        opt.value = ev.id;
        opt.textContent = ev.title;
        select.appendChild(opt);
      });
    } else {
      showToast('Failed to load events', 'error');
    }
  }

  async function loadAssistants() {
    const res = await api.get('/assistants');
    const listDiv = document.getElementById('assistants-list');
    if (res.ok && res.data.length > 0) {
      listDiv.innerHTML = '<table style="width:100%; border-collapse: collapse;"><thead><tr><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Name</th><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Email</th><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Event</th><th style="text-align:left; padding:8px; border-bottom:1px solid var(--border);">Actions</th></tr></thead><tbody>' +
        res.data.map(a => `<tr><td style="padding:8px; border-bottom:1px solid var(--border);" class="i18n-skip">${a.name}</td><td style="padding:8px; border-bottom:1px solid var(--border);">${a.email}</td><td style="padding:8px; border-bottom:1px solid var(--border);">${a.event ? a.event.title : 'N/A'}</td><td style="padding:8px; border-bottom:1px solid var(--border);"><button class="btn btn-danger btn-sm" onclick="deleteAssistant(${a.id})">Delete</button></td></tr>`).join('') +
        '</tbody></table>';
    } else {
      listDiv.innerHTML = '<p style="color:var(--text-muted); font-size: 0.9rem;">No assistants created yet.</p>';
    }
  }

  async function deleteAssistant(id) {
    if (!confirm('Are you sure you want to delete this assistant?')) return;
    const res = await api.delete(`/assistants/${id}`);
    if (res.ok) {
      showToast('Assistant deleted!', 'success');
      loadAssistants();
    } else {
      showToast('Failed to delete assistant', 'error');
    }
  }

  document.getElementById('assistant-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('save-btn');
    btn.textContent = 'Creating...'; btn.disabled = true;

    const body = {
      name: document.getElementById('a-name').value,
      email: document.getElementById('a-email').value,
      password: document.getElementById('a-pass').value,
      event_id: document.getElementById('a-event').value,
      role: 'Assistant' 
    };

    const res = await api.post('/users', body);
    
    if (res.ok) {
      showToast('Assistant created!', 'success');
      document.getElementById('assistant-form').reset();
      document.getElementById('success-box').style.display = 'block';
      setTimeout(() => { document.getElementById('success-box').style.display = 'none'; }, 8000);
      loadAssistants(); // Reload the list
    } else {
      const msg = res.data?.errors ? Object.values(res.data.errors).flat().join('. ') : res.data?.message || 'Error creating assistant';
      showToast(msg, 'error');
    }
    btn.textContent = 'Create Assistant'; btn.disabled = false;
  });
</script>
</body>
</html>
